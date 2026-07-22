import { Router } from 'express';
import { query, execute, queryOne, generateUniqueHash, withTransaction } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime, asArray, generateMonths } from '../utils/helpers';

const router = Router();

/** Round to 2 decimals (matches legacy PHP round($x, 2)). */
function round2(n: number): number {
  return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
}

/**
 * Billing engine for cloud projects.
 * Bill status: 0 draft, 1 portal prices, 2 sales prices, 3 purchase prices.
 */

/**
 * Resolve the RI/PAYG discount that applies to a given month, mirroring the
 * legacy lookup used across every *billitemsection.php:
 *   from_date < 'Y-M-15' AND to_date > 'Y-M-15'  (interval match)
 *   else the open-ended row (to_date IS NULL)     (current/perpetual)
 */
async function resolveDiscount(projectId: number, month: number, year: number) {
  const dd = `${year}-${String(month).padStart(2, '0')}-15`;
  let row = await queryOne(
    `SELECT ri_discount, payg_discount, credit_days FROM project_discount
     WHERE from_date < ? AND to_date > ? AND project_id = ? LIMIT 1`,
    [dd, dd, projectId]
  );
  if (!row) {
    row = await queryOne(
      `SELECT ri_discount, payg_discount, credit_days FROM project_discount
       WHERE project_id = ? AND to_date IS NULL LIMIT 1`,
      [projectId]
    );
  }
  return {
    ri: Number(row?.ri_discount ?? 0),
    payg: Number(row?.payg_discount ?? 0),
    creditDays: Number(row?.credit_days ?? 0),
  };
}

/** Items whose deployment overlaps the given month (for a bill's pricing grid). */
async function applicableItems(projectId: number, month: number, year: number) {
  const monthStart = `${year}-${String(month).padStart(2, '0')}-01`;
  const lastDay = new Date(year, month, 0).getDate();
  const monthEnd = `${year}-${String(month).padStart(2, '0')}-${lastDay}`;
  return query(
    `SELECT pi.id, pi.hash, pi.resource_id, pi.model, pi.deployment_start, pi.deployment_end,
            pi.unit_price, pi.quantity, pi.status, pi.description, pi.header_id, pi.purchase_header_id,
            (SELECT name FROM product WHERE id = pi.product) AS product_name,
            (SELECT name FROM cloud_category WHERE value = (SELECT sub_category FROM product WHERE id = pi.product)) AS category_name,
            (SELECT name FROM project_header WHERE id = pi.header_id) AS header_name,
            (SELECT name FROM purchase_header WHERE id = pi.purchase_header_id) AS purchase_header_name,
            (SELECT name FROM distributor WHERE id = pi.distributor) AS distributor_name,
            (SELECT name FROM unit_measure WHERE value = pi.unit_measure) AS unit_measure_name,
            (SELECT name FROM product WHERE id = pi.deployed_product) AS deployed_product_name,
            (SELECT name FROM project_item_discovery WHERE value = pi.discovery_status) AS discovery_status_name
     FROM project_item pi
     WHERE pi.project_id = ? AND pi.is_deleted = 0
       AND pi.deployment_start <= ?
       AND (pi.deployment_end IS NULL OR pi.deployment_end >= ? OR pi.status = 1)
     ORDER BY pi.header_id, pi.id`,
    [projectId, monthEnd, monthStart]
  );
}

/** Ensure bill_purchase_header rows exist for all non-zero purchase headers used by bill items. */
async function ensureBillPurchaseHeaders(projectId: number, billId: number) {
  const existing = await query(
    'SELECT purchase_header_id, amount FROM bill_purchase_header WHERE bill_id = ? AND project_id = ?',
    [billId, projectId]
  );
  const existingSet = new Set(existing.map((r: any) => Number(r.purchase_header_id)));

  const distinct = await query(
    `SELECT DISTINCT purchase_header_id FROM bill_item
     WHERE bill_id = ? AND project_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
     ORDER BY purchase_header_id`,
    [billId, projectId]
  );

  for (const row of distinct as any[]) {
    const phId = Number(row.purchase_header_id);
    if (!Number.isFinite(phId) || phId <= 0 || existingSet.has(phId)) continue;
    await execute(
      'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
      [billId, projectId, phId]
    );
  }

  return query(
    'SELECT purchase_header_id, amount FROM bill_purchase_header WHERE bill_id = ? AND project_id = ?',
    [billId, projectId]
  );
}

/**
 * Purchase-reconciliation note states, mirroring the legacy status-grid border
 * colours (viewmonthlybillstatussection.php) and the video legend:
 *   pending (grey) -> deviation (red) -> dn generated (gold) -> cn received (blue),
 *   or ok (green) when the distributor gave at least the agreed discount.
 */
const NOTE_STATES = {
  pending: { label: 'Pending', color: '#94a3b8' },
  ok: { label: 'OK', color: '#22c55e' },
  deviation: { label: 'Deviation', color: '#ef6b5a' },
  dn: { label: 'DN Generated', color: '#f0b429' },
  cn: { label: 'CN Received', color: '#1e5bf0' },
} as const;
type NoteState = keyof typeof NOTE_STATES;

/** Per-model portal sums for a purchase header on a bill. */
async function headerPortalSums(billId: number, purchaseHeaderId: number) {
  const sum = async (cond: string) => {
    const r = await queryOne(
      `SELECT COALESCE(SUM(portal_price),0) AS s FROM bill_item bi
       INNER JOIN project_item pi ON pi.id = bi.project_item_id
       WHERE ${cond} AND bi.purchase_header_id = ? AND bi.bill_id = ?`,
      [purchaseHeaderId, billId]
    );
    return Number(r?.s ?? 0);
  };
  return {
    ri: await sum("pi.model = 'RI'"),
    payg: await sum("pi.model = 'PAYG'"),
    other: await sum("(pi.model IS NULL OR pi.model NOT IN ('RI','PAYG'))"),
  };
}

/** Expected purchase for a header = portal minus the model's agreed discount. */
function headerExpected(sums: { ri: number; payg: number; other: number }, discount: { ri: number; payg: number }) {
  return round2(sums.ri - (sums.ri / 100) * discount.ri)
    + round2(sums.payg - (sums.payg / 100) * discount.payg)
    + round2(sums.other);
}

/**
 * Full purchase reconciliation for a bill, faithful to the legacy portal:
 *  - per header expected = (RI portal - RI%) + (PAYG portal - PAYG%) + other;
 *  - only headers where expected < actual (distributor under-discounted) count;
 *  - debit notes raised on the bill's invoices are ADDED BACK to expected, which
 *    closes the gap; a matching credit note then settles it.
 * Mirrors monthlybillitemsummarysection.php + viewmonthlybillstatussection.php.
 */
async function computeReconciliation(projectId: number, bill: any, discount: { ri: number; payg: number }) {
  const priced = Number(bill.status) >= 3 && Number(bill.purchase_header_status) === 1;

  let calcGrand = 0;
  let actualGrand = 0;
  if (priced) {
    const phs = await query(
      'SELECT purchase_header_id, amount FROM bill_purchase_header WHERE project_id = ? AND bill_id = ?',
      [projectId, bill.id]
    );
    for (const ph of phs as any[]) {
      const sums = await headerPortalSums(bill.id, ph.purchase_header_id);
      const calc = round2(headerExpected(sums, discount));
      const actual = round2(Number(ph.amount || 0));
      if (calc < actual) {
        calcGrand += calc;
        actualGrand += actual;
      }
    }
  }
  calcGrand = round2(calcGrand);
  actualGrand = round2(actualGrand);

  // Debit notes raised for this bill's mapped invoices (added back to expected).
  const dnMapped = await queryOne(
    `SELECT COALESCE(SUM(amount),0) AS s FROM debit_note
     WHERE project_id = ? AND invoice_id IN
       (SELECT invoice_id FROM bill_invoice_mapping WHERE project_id = ? AND bill_id = ?)`,
    [projectId, projectId, bill.id]
  );
  const debitNoteAmount = round2(Number(dnMapped?.s ?? 0));

  // DN/CN totals tied to this bill (for the DN->CN settlement check).
  const dnBill = await queryOne('SELECT COALESCE(SUM(amount),0) AS s FROM debit_note WHERE bill_id = ?', [bill.id]);
  const cnBill = await queryOne(
    'SELECT COALESCE(SUM(amount),0) AS s FROM credit_note WHERE debit_note_id IN (SELECT id FROM debit_note WHERE bill_id = ?)',
    [bill.id]
  );
  const dnTotal = round2(Number(dnBill?.s ?? 0));
  const cnTotal = round2(Number(cnBill?.s ?? 0));

  let noteState: NoteState = 'pending';
  let deviation = 0;
  if (priced) {
    if (calcGrand >= actualGrand) {
      noteState = 'ok';
      deviation = calcGrand > actualGrand && calcGrand > 0 ? round2(((calcGrand - actualGrand) / calcGrand) * 100) : 0;
    } else {
      const calcWithDn = round2(calcGrand + debitNoteAmount);
      if (calcWithDn >= actualGrand) {
        deviation = calcWithDn > 0 ? round2(((calcWithDn - actualGrand) / calcWithDn) * 100) : 0;
        noteState = dnTotal > 0 && cnTotal >= dnTotal ? 'cn' : 'dn';
      } else {
        noteState = 'deviation';
        deviation = actualGrand > 0 ? round2(((actualGrand - calcGrand) / actualGrand) * 100) : 0;
      }
    }
  }

  // Remaining amount still to be recovered by a debit note (the DN "Remaining Amount").
  const shortfall = Math.max(0, round2(actualGrand - (calcGrand + debitNoteAmount)));
  const meta = NOTE_STATES[noteState];

  return {
    calculated_purchase: calcGrand,
    actual_purchase: actualGrand,
    debit_note_amount: debitNoteAmount,
    dn_total: dnTotal,
    cn_total: cnTotal,
    shortfall,
    deviation,
    deviation_color: noteState === 'deviation' ? 'red' : 'green',
    note_state: noteState,
    note_label: meta.label,
    note_color: meta.color,
  };
}

/**
 * Get the bill (if any) for a project/month/year together with the active
 * project items and headers needed by the pricing screens. Mirrors the various
 * *billitemsection.php / monthlybillitemsummarysection.php loaders.
 */
router.get(
  '/project/:projectId/:month/:year',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const month = toNum(req.params.month);
    const year = toNum(req.params.year);

    const bill = await queryOne(
      'SELECT * FROM bill WHERE project_id = ? AND month = ? AND year = ? AND is_deleted = 0',
      [projectId, month, year]
    );

    // Active items for the project (used to render the pricing grid).
    const items = await query(
      `SELECT pi.*,
              (SELECT name FROM product WHERE id = pi.product) AS product_name,
              (SELECT name FROM project_header WHERE id = pi.header_id) AS header_name,
              (SELECT name FROM purchase_header WHERE id = pi.purchase_header_id) AS purchase_header_name
       FROM project_item pi
       WHERE pi.project_id = ? AND pi.is_deleted = 0
       ORDER BY pi.header_id, pi.id`,
      [projectId]
    );

    const headers = await query(
      'SELECT * FROM project_header WHERE project_id = ? AND is_deleted = 0 ORDER BY id',
      [projectId]
    );

    let billItems: any[] = [];
    let billHeaders: any[] = [];
    let billPurchaseHeaders: any[] = [];
    if (bill) {
      billItems = await query('SELECT * FROM bill_item WHERE bill_id = ? AND project_id = ?', [bill.id, projectId]);
      billHeaders = await query('SELECT * FROM bill_header WHERE bill_id = ? AND project_id = ? AND is_deleted = 0', [bill.id, projectId]);
      billPurchaseHeaders = await query(
        `SELECT bph.*, (SELECT name FROM purchase_header WHERE id = bph.purchase_header_id) AS purchase_header_name
         FROM bill_purchase_header bph WHERE bph.bill_id = ? AND bph.project_id = ?`,
        [bill.id, projectId]
      );
    }

    res.json(ok('Bill', { bill, items, headers, billItems, billHeaders, billPurchaseHeaders }));
  })
);

/** All bills for a project. */
router.get(
  '/project/:projectId',
  asyncHandler(async (req, res) => {
    const rows = await query(
      `SELECT b.*, (SELECT name FROM month WHERE value = b.month) AS month_name
       FROM bill b WHERE b.project_id = ? AND b.is_deleted = 0 ORDER BY b.year, b.month`,
      [toNum(req.params.projectId)]
    );
    res.json(ok('Bills', rows));
  })
);

/** Monthly bill summary with item + header + purchase-header detail. */
router.get(
  '/:billId/summary',
  asyncHandler(async (req, res) => {
    const billId = toNum(req.params.billId);
    const bill = await queryOne(
      `SELECT b.*, (SELECT name FROM month WHERE value = b.month) AS month_name
       FROM bill b WHERE b.id = ?`,
      [billId]
    );
    if (!bill) return res.json(fail('Bill not found'));
    const items = await query(
      `SELECT bi.*,
              (SELECT name FROM product WHERE id = (SELECT product FROM project_item WHERE id = bi.project_item_id)) AS product_name,
              (SELECT name FROM purchase_header WHERE id = bi.purchase_header_id) AS purchase_header_name
       FROM bill_item bi WHERE bi.bill_id = ?`,
      [billId]
    );
    const headers = await query(
      `SELECT bh.*, (SELECT name FROM project_header WHERE id = bh.header_id) AS header_name
       FROM bill_header bh WHERE bh.bill_id = ? AND bh.is_deleted = 0`,
      [billId]
    );
    const purchaseHeaders = await query(
      `SELECT bph.*, (SELECT name FROM purchase_header WHERE id = bph.purchase_header_id) AS purchase_header_name
       FROM bill_purchase_header bph WHERE bph.bill_id = ?`,
      [billId]
    );
    res.json(ok('Bill Summary', { bill, items, headers, purchaseHeaders }));
  })
);

/**
 * Month status table for a cloud project (mirrors viewmonthlybillstatussection.php).
 * One row per existing bill with portal/sales/purchase/invoice totals, the RI/PAYG
 * discount that applies to that month, CI/CO flags, progress and purchase deviation.
 * Also returns the months (from project start to now) that have no bill yet.
 */
router.get(
  '/project/:projectId/status',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const project = await queryOne('SELECT start_date FROM project WHERE id = ?', [projectId]);

    const bills = await query(
      `SELECT b.id, b.hash, b.year, b.month, b.status, b.purchase_header_status, b.progress,
              b.ri_discount, b.payg_discount,
              (SELECT name FROM month WHERE value = b.month) AS month_name,
              (SELECT COALESCE(SUM(portal_price),0) FROM bill_item WHERE bill_id = b.id) AS portal_total,
              (SELECT COALESCE(SUM(amount),0) FROM bill_header WHERE bill_id = b.id AND is_deleted = 0) AS sales_total,
              (SELECT COALESCE(SUM(amount),0) FROM bill_purchase_header WHERE bill_id = b.id AND project_id = ?) AS purchase_total,
              (SELECT COALESCE(SUM(amount),0) FROM purchase_invoice WHERE id IN
                (SELECT invoice_id FROM bill_invoice_mapping WHERE bill_id = b.id AND project_id = ?)) AS invoice_total,
              (SELECT COUNT(id) FROM purchase_invoice WHERE id IN
                (SELECT invoice_id FROM bill_invoice_mapping WHERE bill_id = b.id AND project_id = ?)) AS invoice_count
       FROM bill b WHERE b.project_id = ? AND b.is_deleted = 0
       ORDER BY b.year, b.month`,
      [projectId, projectId, projectId, projectId]
    );

    const rows: any[] = [];
    for (const bill of bills) {
      // Discount is snapshotted onto the bill at portal-pricing (legacy parity), so a
      // finalized bill's reconciliation is locked to the rate agreed when it was priced.
      const discount = { ri: Number(bill.ri_discount ?? 0), payg: Number(bill.payg_discount ?? 0) };
      const recon = await computeReconciliation(projectId, bill, discount);

      rows.push({
        ...bill,
        ...recon,
      });
    }

    // Months (start → now) that do not yet have a bill.
    const allMonths = generateMonths(project?.start_date ?? null);
    const taken = new Set(bills.map((b: any) => `${b.year}-${b.month}`));
    const availableMonths = allMonths.filter((m) => !taken.has(`${m.year}-${m.month}`));

    res.json(ok('Bill Status', { rows, availableMonths }));
  })
);

/**
 * Applicable items + resolved discount for a month that has no bill yet.
 * Used by the Portal pricing modal when creating a new monthly bill.
 */
router.get(
  '/project/:projectId/applicable/:month/:year',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const month = toNum(req.params.month);
    const year = toNum(req.params.year);
    const discount = await resolveDiscount(projectId, month, year);
    const items = await applicableItems(projectId, month, year);
    const rows = items.map((it: any) => ({ ...it, portal_price: it.unit_price ?? 0 }));
    res.json(ok('Applicable Items', { discount, rows }));
  })
);

/**
 * Detailed pricing data for one bill: the applicable items joined with their
 * bill_item prices, project headers with bill_header amounts, and purchase
 * headers with the calculated (portal minus discount) vs actual amounts.
 * Feeds the Portal / Sales / Purchase / Summary modals.
 */
router.get(
  '/:billId/pricing',
  asyncHandler(async (req, res) => {
    const billId = toNum(req.params.billId);
    const bill = await queryOne(
      `SELECT b.*, (SELECT name FROM month WHERE value = b.month) AS month_name FROM bill b WHERE b.id = ?`,
      [billId]
    );
    if (!bill) return res.json(fail('Bill not found'));
    const projectId = bill.project_id;
    // Use the discount snapshotted onto the bill at portal-pricing (legacy parity).
    const discount = { ri: Number(bill.ri_discount ?? 0), payg: Number(bill.payg_discount ?? 0) };

    // Legacy parity: for an existing bill, pricing views are driven by bill_item
    // snapshot rows, not a re-evaluated "applicable items" query.
    const rawRows = await query(
      `SELECT bi.id AS bill_item_id,
              bi.project_item_id AS id,
              bi.purchase_header_id,
              bi.portal_price,
              bi.sales_price,
              bi.purchase_price,
              pi.resource_id,
              pi.model,
              pi.deployment_start,
              pi.deployment_end,
              pi.status,
              pi.quantity,
              pi.description,
              pi.header_id,
              pi.unit_price,
              (SELECT name FROM product WHERE id = pi.product) AS product_name,
              (SELECT name FROM cloud_category WHERE value = (SELECT sub_category FROM product WHERE id = pi.product)) AS category_name,
              (SELECT name FROM project_header WHERE id = pi.header_id) AS header_name,
              (SELECT name FROM purchase_header WHERE id = bi.purchase_header_id) AS purchase_header_name
       FROM bill_item bi
       INNER JOIN project_item pi ON pi.id = bi.project_item_id
       WHERE bi.bill_id = ? AND bi.project_id = ?
       ORDER BY bi.purchase_header_id, bi.id`,
      [billId, projectId]
    );

    const monthEndDate = new Date(bill.year, bill.month, 0);
    const rows = rawRows.map((r: any) => {
      const discPct = r.model === 'RI' ? discount.ri : r.model === 'PAYG' ? discount.payg : 0;
      const portal = Number(r.portal_price ?? 0);
      const estimatedPurchase = round2(portal - (portal / 100) * discPct);

      let statusLabel = 'Active';
      if (r.status === 0 && r.deployment_end && new Date(r.deployment_end) <= monthEndDate) {
        statusLabel = r.deployment_end;
      }

      return {
        ...r,
        estimated_purchase: estimatedPurchase,
        status_label: statusLabel,
      };
    });

    const headers = await query(
      `SELECT ph.id, ph.name, ph.quantity,
              (SELECT amount FROM bill_header WHERE bill_id = ? AND project_id = ? AND header_id = ph.id) AS amount
       FROM project_header ph WHERE ph.project_id = ? AND ph.is_deleted = 0 ORDER BY ph.id`,
      [billId, projectId, projectId]
    );

    const phRows = await ensureBillPurchaseHeaders(projectId, billId);
    const purchaseHeaders: any[] = [];
    for (const ph of phRows) {
      const sums = await headerPortalSums(billId, ph.purchase_header_id);
      const name = await queryOne('SELECT name FROM purchase_header WHERE id = ?', [ph.purchase_header_id]);
      const calc = round2(headerExpected(sums, discount));
      const actual = ph.amount == null ? null : round2(Number(ph.amount));

      // Per-header deviation, shown in the Summary reconciliation view.
      let hdrDeviation = 0;
      let hdrColor = 'green';
      if (Number(bill.status) >= 3 && actual != null) {
        if (calc < actual) { hdrDeviation = actual > 0 ? round2(((actual - calc) / actual) * 100) : 0; hdrColor = 'red'; }
        else if (calc > actual) { hdrDeviation = calc > 0 ? round2(((calc - actual) / calc) * 100) : 0; hdrColor = 'green'; }
      }

      purchaseHeaders.push({
        purchase_header_id: ph.purchase_header_id,
        purchase_header_name: name?.name ?? `Header ${ph.purchase_header_id}`,
        ri_portal: round2(sums.ri),
        payg_portal: round2(sums.payg),
        other_portal: round2(sums.other),
        ri_calculated: round2(sums.ri - (sums.ri / 100) * discount.ri),
        payg_calculated: round2(sums.payg - (sums.payg / 100) * discount.payg),
        portal_total: round2(sums.ri + sums.payg + sums.other),
        calculated_amount: calc,
        amount: ph.amount,
        deviation: hdrDeviation,
        deviation_color: hdrColor,
      });
    }

    const reconciliation = await computeReconciliation(projectId, bill, discount);

    res.json(ok('Bill Pricing', { bill, discount, rows, headers, purchaseHeaders, reconciliation }));
  })
);

/**
 * Save portal prices. Creates the bill (status 1) with bill_header, bill_item and
 * bill_purchase_header rows on first save; updates prices afterwards.
 * Mirrors add_portal_price.php.
 */
router.post(
  '/portal-price',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body.projectidpost);
    const month = toNum(req.body.month ?? req.body.pricemonthpost);
    const year = toNum(req.body.year ?? req.body.priceyearpost);
    const riDiscount = toStr(req.body.riDiscount ?? req.body.portalridiscountpost);
    const paygDiscount = toStr(req.body.paygDiscount ?? req.body.portalpaygdiscountpost);
    const itemIds = asArray(req.body.projectItemIds ?? req.body.projectitemidarrvalpost);
    const portalPrices = asArray(req.body.portalPrices ?? req.body.portalpricearrvalpost);

    if (itemIds.length === 0) return res.json(fail('Error : No data recieved from client !'));
    if (riDiscount === '') return res.json(fail('Please enter RI discount'));
    if (paygDiscount === '') return res.json(fail('Please enter PAYG discount'));

    await withTransaction(async (conn) => {
      const [existingRows] = await conn.query(
        'SELECT id FROM bill WHERE project_id = ? AND month = ? AND year = ? AND is_deleted = 0',
        [projectId, month, year]
      );
      const existing = (existingRows as any[])[0];

      if (existing) {
        const billId = existing.id;
        await conn.query('UPDATE bill SET ri_discount = ?, payg_discount = ? WHERE id = ?', [
          toNum(riDiscount), toNum(paygDiscount), billId,
        ]);
        for (let i = 0; i < itemIds.length; i++) {
          await conn.query(
            'UPDATE bill_item SET portal_price = ? WHERE bill_id = ? AND project_id = ? AND project_item_id = ?',
            [toNum(portalPrices[i], 0), billId, projectId, toNum(itemIds[i])]
          );
        }

        const [existingPhRows] = await conn.query(
          'SELECT purchase_header_id FROM bill_purchase_header WHERE bill_id = ? AND project_id = ?',
          [billId, projectId]
        );
        const existingSet = new Set((existingPhRows as any[]).map((r) => Number(r.purchase_header_id)));
        const [distinctPh] = await conn.query(
          `SELECT DISTINCT purchase_header_id FROM bill_item
           WHERE project_id = ? AND bill_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
           ORDER BY purchase_header_id`,
          [projectId, billId]
        );
        for (const ph of distinctPh as any[]) {
          const phId = Number(ph.purchase_header_id);
          if (!Number.isFinite(phId) || phId <= 0 || existingSet.has(phId)) continue;
          await conn.query(
            'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
            [billId, projectId, phId]
          );
        }

        return;
      }

      // Create new bill
      const hash = await generateUniqueHash(['bill']);
      const [billResult]: any = await conn.query(
        'INSERT INTO bill (hash, project_id, month, year, ri_discount, payg_discount, status, created_on) VALUES (?,?,?,?,?,?,1,?)',
        [hash, projectId, month, year, toNum(riDiscount), toNum(paygDiscount), nowDateTime()]
      );
      const billId = billResult.insertId;

      // bill_header rows (one per active project header)
      const [headers] = await conn.query(
        'SELECT id FROM project_header WHERE project_id = ? AND is_deleted = 0',
        [projectId]
      );
      for (const h of headers as any[]) {
        await conn.query(
          'INSERT INTO bill_header (bill_id, project_id, header_id, amount, is_deleted) VALUES (?,?,?,0,0)',
          [billId, projectId, h.id]
        );
      }

      // bill_item rows (one per project item priced)
      for (let i = 0; i < itemIds.length; i++) {
        const itemId = toNum(itemIds[i]);
        const [phRows]: any = await conn.query('SELECT purchase_header_id FROM project_item WHERE id = ?', [itemId]);
        const purchaseHeaderId = phRows[0]?.purchase_header_id ?? 0;
        await conn.query(
          'INSERT INTO bill_item (bill_id, project_id, project_item_id, purchase_header_id, portal_price) VALUES (?,?,?,?,?)',
          [billId, projectId, itemId, purchaseHeaderId, toNum(portalPrices[i], 0)]
        );
      }

      // bill_purchase_header rows (one per distinct purchase header)
      const [distinctPh] = await conn.query(
        `SELECT DISTINCT purchase_header_id FROM bill_item
         WHERE project_id = ? AND bill_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
         ORDER BY purchase_header_id`,
        [projectId, billId]
      );
      for (const ph of distinctPh as any[]) {
        await conn.query(
          'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
          [billId, projectId, ph.purchase_header_id]
        );
      }
    });

    res.json(ok('Portal Price Saved'));
  })
);

/** Save sales prices - mirrors add_sales_price.php (bill status -> 2). */
router.post(
  '/sales-price',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body.projectidpost);
    const billId = toNum(req.body.billId ?? req.body.billidpost);
    const itemIds = asArray(req.body.projectItemIds ?? req.body.projectitemidarrvalpost);
    const salesPrices = asArray(req.body.salesPrices ?? req.body.salespricearrvalpost);
    const headerIds = asArray(req.body.projectHeaderIds ?? req.body.projectheaderidarrvalpost);
    const headerPrices = asArray(req.body.headerPrices ?? req.body.headerpricearrvalpost);

    let touched = false;
    await withTransaction(async (conn) => {
      for (let i = 0; i < headerIds.length; i++) {
        await conn.query(
          'UPDATE bill_header SET amount = ? WHERE header_id = ? AND project_id = ? AND bill_id = ?',
          [toNum(headerPrices[i], 0), toNum(headerIds[i]), projectId, billId]
        );
        touched = true;
      }
      for (let i = 0; i < itemIds.length; i++) {
        await conn.query(
          'UPDATE bill_item SET sales_price = ? WHERE bill_id = ? AND project_id = ? AND project_item_id = ?',
          [toNum(salesPrices[i], 0), billId, projectId, toNum(itemIds[i])]
        );
        touched = true;
      }
      if (touched) {
        await conn.query('UPDATE bill SET status = 2 WHERE id = ? AND project_id = ? AND status <= 2', [billId, projectId]);
      }
    });

    if (!touched) return res.json(fail('Error : Failed to update items !'));
    res.json(ok('Sales Price Saved'));
  })
);

/** Save purchase prices - mirrors add_purchase_price.php (bill status -> 3). */
router.post(
  '/purchase-price',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body.projectidpost);
    const billId = toNum(req.body.billId ?? req.body.billidpost);
    const itemIds = asArray(req.body.projectItemIds ?? req.body.projectitemidarrvalpost);
    const purchasePrices = asArray(req.body.purchasePrices ?? req.body.purchasepricearrvalpost);
    let purchaseHeaderIds = asArray(req.body.purchaseHeaderIds ?? req.body.purchaseheaderidarrvalpost);
    let headerPrices = asArray(req.body.headerPrices ?? req.body.headerpricearrvalpost);

    if (purchaseHeaderIds.length === 0) {
      const distinct = await query(
        `SELECT DISTINCT purchase_header_id FROM bill_item
         WHERE bill_id = ? AND project_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
         ORDER BY purchase_header_id`,
        [billId, projectId]
      );
      purchaseHeaderIds = (distinct as any[]).map((r) => String(r.purchase_header_id));
    }

    if (purchaseHeaderIds.length === 0) {
      return res.json(fail('No purchase headers found. Assign purchase headers to products first.'));
    }

    if (headerPrices.length < purchaseHeaderIds.length) {
      headerPrices = [
        ...headerPrices,
        ...new Array(purchaseHeaderIds.length - headerPrices.length).fill('0'),
      ];
    }

    await withTransaction(async (conn) => {
      const purchRows = Math.min(itemIds.length, purchasePrices.length);
      for (let i = 0; i < purchRows; i++) {
        await conn.query(
          'UPDATE bill_item SET purchase_price = ? WHERE bill_id = ? AND project_id = ? AND project_item_id = ?',
          [toNum(purchasePrices[i], 0), billId, projectId, toNum(itemIds[i])]
        );
      }
      for (let j = 0; j < purchaseHeaderIds.length; j++) {
        const phId = toNum(purchaseHeaderIds[j]);
        const [existing] = await conn.query(
          'SELECT id FROM bill_purchase_header WHERE bill_id = ? AND project_id = ? AND purchase_header_id = ? LIMIT 1',
          [billId, projectId, phId]
        );
        if ((existing as any[]).length === 0) {
          await conn.query(
            'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,?)',
            [billId, projectId, phId, toNum(headerPrices[j], 0)]
          );
        } else {
          await conn.query(
            'UPDATE bill_purchase_header SET amount = ? WHERE bill_id = ? AND project_id = ? AND purchase_header_id = ?',
            [toNum(headerPrices[j], 0), billId, projectId, phId]
          );
        }
      }
      await conn.query(
        'UPDATE bill SET status = 3, purchase_header_status = 1 WHERE id = ? AND project_id = ? AND status <= 3',
        [billId, projectId]
      );
    });

    res.json(ok('Purchase Price Saved'));
  })
);

/**
 * Pull newly-added project items/headers into an existing monthly bill.
 * Mirrors update_bill_item.php: adds missing bill_item/bill_header rows for
 * items active in the selected month, then resets purchase pricing + invoice
 * and note mappings because the purchase totals must be recalculated.
 */
router.post(
  '/update-items',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectid ?? req.body.projectId);
    const billId = toNum(req.body.billid ?? req.body.billId);
    const month = toNum(req.body.billmonth ?? req.body.month);
    const year = toNum(req.body.billyear ?? req.body.year);
    if (!projectId || !billId || !month || !year) return res.json(fail('Error : Missing Parameters !'));

    const monthStart = new Date(year, month - 1, 1);
    const monthEnd = new Date(year, month, 0);

    await withTransaction(async (conn) => {
      const [items] = await conn.query(
        `SELECT pi.id, pi.header_id, pi.deployment_start, pi.deployment_end, pi.status, pi.purchase_header_id
         FROM project_item pi
         INNER JOIN product pr ON pr.id = pi.product
         WHERE pi.project_id = ? AND pi.is_deleted = 0
         ORDER BY pr.sub_category, pi.id ASC`,
        [projectId]
      );

      for (const item of items as any[]) {
        const start = item.deployment_start ? new Date(item.deployment_start) : null;
        const end = item.deployment_end ? new Date(item.deployment_end) : null;
        let showRow = false;
        if (item.status === 0 && end && start) {
          showRow = (end >= monthStart && end <= monthEnd) || (end >= monthStart && start <= monthEnd);
        } else if (start) {
          showRow = start <= monthEnd;
        }
        if (!showRow) continue;

        const [existingItem] = await conn.query(
          'SELECT id FROM bill_item WHERE bill_id = ? AND project_id = ? AND project_item_id = ? LIMIT 1',
          [billId, projectId, item.id]
        );
        if ((existingItem as any[]).length === 0) {
          await conn.query(
            `INSERT INTO bill_item (bill_id, project_id, project_item_id, purchase_header_id, portal_price, sales_price, purchase_price)
             VALUES (?,?,?,?,0,NULL,NULL)`,
            [billId, projectId, item.id, item.purchase_header_id || 0]
          );
        }

        const [existingHeader] = await conn.query(
          'SELECT id FROM bill_header WHERE bill_id = ? AND project_id = ? AND header_id = ? LIMIT 1',
          [billId, projectId, item.header_id]
        );
        if ((existingHeader as any[]).length === 0) {
          await conn.query(
            'INSERT INTO bill_header (bill_id, project_id, header_id, amount, is_deleted) VALUES (?,?,?,0,0)',
            [billId, projectId, item.header_id]
          );
        }
      }

      const [missingHeaders] = await conn.query(
        `SELECT id FROM project_header
         WHERE project_id = ? AND is_deleted = 0
           AND id NOT IN (SELECT header_id FROM bill_header WHERE bill_id = ? AND project_id = ?)`,
        [projectId, billId, projectId]
      );
      for (const header of missingHeaders as any[]) {
        await conn.query(
          'INSERT INTO bill_header (bill_id, project_id, header_id, amount, is_deleted) VALUES (?,?,?,0,0)',
          [billId, projectId, header.id]
        );
      }

      // Rebuild purchase-header snapshot from current bill items.
      await conn.query('DELETE FROM bill_purchase_header WHERE bill_id = ? AND project_id = ?', [billId, projectId]);
      const [distinctPh] = await conn.query(
        `SELECT DISTINCT purchase_header_id FROM bill_item
         WHERE project_id = ? AND bill_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
         ORDER BY purchase_header_id`,
        [projectId, billId]
      );
      for (const ph of distinctPh as any[]) {
        await conn.query(
          'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
          [billId, projectId, ph.purchase_header_id]
        );
      }

      const [billRows] = await conn.query('SELECT status FROM bill WHERE id = ? AND project_id = ?', [billId, projectId]);
      const billStatus = Number((billRows as any[])[0]?.status ?? 0);
      const statusValue = billStatus > 2 ? 2 : billStatus;
      await conn.query('UPDATE bill SET purchase_header_status = 0, status = ? WHERE id = ?', [statusValue, billId]);
      await conn.query('UPDATE bill_item SET purchase_price = NULL WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM bill_invoice_mapping WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM debit_note WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM credit_note WHERE bill_id = ?', [billId]);
    });

    res.json(ok('Portal Price Saved'));
  })
);

/** Update bill progress - mirrors update_monthly_bill_progress.php. */
router.post(
  '/:billId/progress',
  asyncHandler(async (req, res) => {
    const billId = toNum(req.params.billId);
    const projectId = toNum(req.body['progress-project-id'] ?? req.body.projectId);
    const progress = toNum(req.body['bill-progress'] ?? req.body.progress);
    if (!progress) return res.json(fail('Please select progress'));
    await execute('UPDATE bill SET progress = ? WHERE id = ? AND project_id = ?', [progress, billId, projectId]);
    res.json(ok('Progress Updated'));
  })
);

/**
 * Reset purchase prices - mirrors reset_purchase_price.php.
 * Reverts status to 2 and clears purchase prices + linked invoices/notes/inward.
 */
router.post(
  '/:billId/reset-purchase',
  asyncHandler(async (req, res) => {
    const billId = toNum(req.params.billId);
    await withTransaction(async (conn) => {
      await conn.query('UPDATE bill SET purchase_header_status = 0, status = 2 WHERE id = ?', [billId]);
      await conn.query('UPDATE bill_item SET purchase_price = NULL WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM bill_invoice_mapping WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM debit_note WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM credit_note WHERE bill_id = ?', [billId]);
    });
    res.json(ok('Purchase Price Reset'));
  })
);

/**
 * Change the purchase header of items and recalculate bill_purchase_header rows.
 * Mirrors change_purchase_item_header.php.
 */
router.post(
  '/change-purchase-header',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body.projectidpost);
    const billId = toNum(req.body.billId ?? req.body.billidpost);
    const itemIds = asArray(req.body.projectItemIds ?? req.body.projectitemidarrvalpost);
    const purchaseHeaderIds = asArray(req.body.purchaseHeaderIds ?? req.body.purchaseheaderidarrvalpost);

    await withTransaction(async (conn) => {
      for (let i = 0; i < itemIds.length; i++) {
        await conn.query('UPDATE project_item SET purchase_header_id = ? WHERE id = ?', [
          toNum(purchaseHeaderIds[i]), toNum(itemIds[i]),
        ]);
        await conn.query(
          'UPDATE bill_item SET purchase_header_id = ? WHERE bill_id = ? AND project_id = ? AND project_item_id = ?',
          [toNum(purchaseHeaderIds[i]), billId, projectId, toNum(itemIds[i])]
        );
      }
      // Rebuild bill_purchase_header from distinct headers now on the bill items.
      await conn.query('DELETE FROM bill_purchase_header WHERE bill_id = ? AND project_id = ?', [billId, projectId]);
      const [distinctPh] = await conn.query(
        `SELECT DISTINCT purchase_header_id FROM bill_item
         WHERE project_id = ? AND bill_id = ? AND purchase_header_id IS NOT NULL AND purchase_header_id <> 0
         ORDER BY purchase_header_id`,
        [projectId, billId]
      );
      for (const ph of distinctPh as any[]) {
        await conn.query(
          'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
          [billId, projectId, ph.purchase_header_id]
        );
      }
    });
    res.json(ok('Purchase Header Updated'));
  })
);

/** Delete a monthly bill and its dependents - mirrors delete_monthly_bill.php. */
router.delete(
  '/:billId',
  asyncHandler(async (req, res) => {
    const billId = toNum(req.params.billId);
    await withTransaction(async (conn) => {
      await conn.query('UPDATE bill SET is_deleted = 1 WHERE id = ?', [billId]);
      await conn.query('DELETE FROM bill_item WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM bill_header WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM bill_purchase_header WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM bill_invoice_mapping WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM debit_note WHERE bill_id = ?', [billId]);
      await conn.query('DELETE FROM credit_note WHERE bill_id = ?', [billId]);
    });
    res.json(ok('Bill Deleted'));
  })
);

export default router;
