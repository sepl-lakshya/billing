import { Router } from 'express';
import multer from 'multer';
import { query, queryOne, execute, generateUniqueHash, withTransaction } from '../db';
import { asyncHandler, ok, fail, toNum, toStr, nowDateTime } from '../utils/helpers';
import { parseConsumptionFile, aggregateConsumption } from '../utils/consumption';

const router = Router();
const upload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 30 * 1024 * 1024 } });

/** Recreate the exact project_item.resource_id identity that commit uses. */
function resourceIdentity(resource: string, header: string, subscription: string): string {
  return toStr(resource || `${header}::${toStr(subscription)}`).slice(-180);
}

/** Previous calendar month for a given 1-based month/year. */
function previousMonth(month: number, year: number): { month: number; year: number } {
  return month <= 1 ? { month: 12, year: year - 1 } : { month: month - 1, year };
}

/** Last calendar day (YYYY-MM-DD) of a 1-based month. */
function lastDayOfMonth(year: number, month: number): string {
  const day = new Date(year, month, 0).getDate();
  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

/**
 * Compare a freshly aggregated preview against the project's current active
 * items so a MONTHLY (delta) import can show exactly what changed:
 *   - continuing: resource still present (price/qty refreshed; carries old cost)
 *   - new:        resource never seen before
 *   - stopped:    an active item that is no longer present in the upload
 * Matching mirrors the commit identity (resource path tail + model), so the diff
 * shown here is exactly what the commit will act on.
 */
async function computeMonthlyDiff(projectId: number, preview: any) {
  const existing = await query(
    `SELECT pi.id, pi.resource_id, pi.model, pi.unit_price, pi.header_id,
            (SELECT name FROM product WHERE id = pi.product) AS product_name,
            (SELECT name FROM project_header WHERE id = pi.header_id) AS header_name,
            (SELECT name FROM purchase_header WHERE id = pi.purchase_header_id) AS purchase_header_name
     FROM project_item pi
     WHERE pi.project_id = ? AND pi.is_deleted = 0 AND pi.status = 1`,
    [projectId]
  );

  // Last month's portal costs give a real old-vs-new comparison in the review.
  const lastBill = await queryOne(
    'SELECT id, month, year FROM bill WHERE project_id = ? AND is_deleted = 0 ORDER BY year DESC, month DESC LIMIT 1',
    [projectId]
  );
  const lastCost = new Map<number, number>();
  if (lastBill) {
    const bItems = await query('SELECT project_item_id, portal_price FROM bill_item WHERE bill_id = ?', [lastBill.id]);
    for (const bi of bItems as any[]) lastCost.set(Number(bi.project_item_id), Number(bi.portal_price ?? 0));
  }

  // Mirror the commit identity (resource_id + product + model) case-insensitively,
  // matching MySQL's ci collation so case-variant duplicate resources collapse the
  // same way the commit will collapse them (and never show as spurious "new").
  const key = (rid: string, product: string, model: string) =>
    `${String(rid ?? '').toLowerCase()}||${String(product ?? '').toLowerCase()}||${String(model ?? '').toLowerCase()}`;
  const byKey = new Map<string, any>();
  for (const e of existing as any[]) byKey.set(key(e.resource_id, e.product_name, e.model), e);

  const matched = new Set<string>();
  let newCount = 0;
  let continuingCount = 0;
  for (const g of preview.groups) {
    for (const it of g.items) {
      const rid = resourceIdentity(it.resource, g.header, it.subscription);
      const k = key(rid, it.productName, it.model);
      const ex = byKey.get(k);
      if (ex) {
        matched.add(k);
        it.changeType = 'continuing';
        it.existingItemId = ex.id;
        it.existingHeaderId = ex.header_id;
        it.existingHeaderName = ex.header_name;
        it.prevUnitPrice = Number(ex.unit_price ?? 0);
        it.prevCost = lastCost.has(Number(ex.id)) ? lastCost.get(Number(ex.id)) : null;
        continuingCount++;
      } else {
        it.changeType = 'new';
        newCount++;
      }
    }
  }

  const stopped = (existing as any[])
    .filter((e) => !matched.has(key(e.resource_id, e.product_name, e.model)))
    .map((e) => ({
      id: e.id,
      resource_id: e.resource_id,
      model: e.model,
      product_name: e.product_name,
      header_name: e.header_name,
      purchase_header_name: e.purchase_header_name,
      unit_price: Number(e.unit_price ?? 0),
      last_cost: lastCost.has(Number(e.id)) ? lastCost.get(Number(e.id)) : null,
    }));

  return {
    newCount,
    continuingCount,
    stoppedCount: stopped.length,
    stopped,
    lastBill: lastBill ? { month: Number(lastBill.month), year: Number(lastBill.year) } : null,
  };
}

/**
 * Import context for a project: which phase the next import is (setup vs
 * monthly), the assigned distributor + the distributor picklist, the last
 * billed month and a suggested next month. Lets the UI adapt before any upload.
 */
router.get(
  '/:projectId/context',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const project = await queryOne('SELECT id, name, distributor, start_date FROM project WHERE id = ?', [projectId]);
    if (!project) return res.json(fail('Project not found'));

    const itemRow = await queryOne(
      'SELECT COUNT(*) AS c FROM project_item WHERE project_id = ? AND is_deleted = 0 AND status = 1', [projectId]);
    const billRow = await queryOne('SELECT COUNT(*) AS c FROM bill WHERE project_id = ? AND is_deleted = 0', [projectId]);
    const itemCount = Number(itemRow?.c ?? 0);
    const billCount = Number(billRow?.c ?? 0);
    const lastBill = await queryOne(
      'SELECT month, year FROM bill WHERE project_id = ? AND is_deleted = 0 ORDER BY year DESC, month DESC LIMIT 1',
      [projectId]
    );
    const distributors = await query('SELECT id, name FROM distributor WHERE is_active = 1 ORDER BY name');
    const headers = await query('SELECT id, name FROM project_header WHERE project_id = ? AND is_deleted = 0 ORDER BY id', [projectId]);
    const purchaseHeaders = await query(
      'SELECT id, name FROM purchase_header WHERE (project_id = ? OR project_id = 0) AND is_active = 1 ORDER BY name',
      [projectId]
    );
    const discountRow = await queryOne('SELECT COUNT(*) AS c FROM project_discount WHERE project_id = ?', [projectId]);
    const hasDiscount = Number(discountRow?.c ?? 0) > 0;

    const mode: 'setup' | 'monthly' = itemCount > 0 ? 'monthly' : 'setup';
    // Suggest the month after the last bill for a monthly import, else the
    // previous calendar month (the usual "bill last month's consumption" case).
    let sMonth: number;
    let sYear: number;
    if (mode === 'monthly' && lastBill) {
      const n = Number(lastBill.month) >= 12 ? { month: 1, year: Number(lastBill.year) + 1 } : { month: Number(lastBill.month) + 1, year: Number(lastBill.year) };
      sMonth = n.month;
      sYear = n.year;
    } else {
      const now = new Date();
      sMonth = now.getMonth() === 0 ? 12 : now.getMonth();
      sYear = now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear();
    }

    res.json(ok('Import Context', {
      mode,
      itemCount,
      billCount,
      distributor: Number(project.distributor ?? 0),
      distributors,
      headers,
      purchaseHeaders,
      hasDiscount,
      lastBill: lastBill ? { month: Number(lastBill.month), year: Number(lastBill.year) } : null,
      suggestedMonth: sMonth,
      suggestedYear: sYear,
    }));
  })
);

/**
 * Parse one or more Azure/partner consumption files (CSV or XLSX) and return an
 * aggregated purchase-header -> product preview. Nothing is written to the DB;
 * the frontend shows this for review before the user commits. For a monthly
 * import (mode='monthly', or auto-detected when the project already has items)
 * each line is annotated new/continuing and a `diff` (incl. stopped resources)
 * is attached.
 */
router.post(
  '/:projectId/preview',
  upload.array('files', 6),
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const files = (req.files as Express.Multer.File[]) || [];
    if (files.length === 0) return res.json(fail('Please upload at least one consumption file'));

    const groupBy = toStr(req.body.groupBy) || 'resource';
    const productBy = toStr(req.body.productBy) || 'meterName';
    const resourceBy = toStr(req.body.resourceBy) || undefined;

    let rows: any[] = [];
    for (const f of files) {
      try {
        rows = rows.concat(parseConsumptionFile(f.buffer, f.originalname));
      } catch (e: any) {
        return res.json(fail(`Failed to parse ${f.originalname}: ${e.message}`));
      }
    }
    if (rows.length === 0) return res.json(fail('No rows found in the uploaded file(s)'));

    const preview: any = aggregateConsumption(rows, groupBy, productBy, resourceBy);

    const requestedMode = toStr(req.body.mode);
    const activeItems = await queryOne(
      'SELECT COUNT(*) AS c FROM project_item WHERE project_id = ? AND is_deleted = 0 AND status = 1', [projectId]);
    const mode = requestedMode === 'setup' || requestedMode === 'monthly'
      ? requestedMode
      : (Number(activeItems?.c ?? 0) > 0 ? 'monthly' : 'setup');
    preview.mode = mode;
    if (mode === 'monthly') preview.diff = await computeMonthlyDiff(projectId, preview);

    res.json(ok('Consumption Preview', preview));
  })
);

/** Ensure a cloud product master exists by name; returns its id. */
async function ensureProduct(conn: any, name: string): Promise<number> {
  const [rows] = await conn.query('SELECT id FROM product WHERE name = ? AND category = 1 LIMIT 1', [name]);
  if ((rows as any[]).length) return (rows as any[])[0].id;
  const [r]: any = await conn.query(
    'INSERT INTO product (name, oem, category, sub_category, is_active, created_on) VALUES (?,?,1,0,1,?)',
    [name, 0, nowDateTime()]
  );
  return r.insertId;
}

/** Resolve the RI/PAYG discount applying to a month (same logic as bills.ts). */
async function resolveDiscountFor(projectId: number, month: number, year: number) {
  const dd = `${year}-${String(month).padStart(2, '0')}-15`;
  let row = await queryOne(
    `SELECT ri_discount, payg_discount FROM project_discount
     WHERE from_date < ? AND to_date > ? AND project_id = ? LIMIT 1`,
    [dd, dd, projectId]
  );
  if (!row) {
    row = await queryOne(
      `SELECT ri_discount, payg_discount FROM project_discount
       WHERE project_id = ? AND to_date IS NULL LIMIT 1`,
      [projectId]
    );
  }
  return { ri: Number(row?.ri_discount ?? 0), payg: Number(row?.payg_discount ?? 0) };
}

/**
 * Commit a reviewed consumption preview: create/reuse the sales header +
 * purchase header per group, create project items for each included line, and
 * (optionally) create the month's bill with portal prices pre-filled from the
 * Azure consumption cost.
 *
 * The commit is IDEMPOTENT for monthly re-imports: existing project items are
 * matched by (resource_id, product, model) and reused instead of duplicated,
 * and bill items are upserted, so importing the same customer every month only
 * adds genuinely new resources.
 */
router.post(
  '/:projectId/commit',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const groups = Array.isArray(req.body.groups) ? req.body.groups : [];
    const month = toNum(req.body.month);
    const year = toNum(req.body.year);
    const createBill = !!req.body.createBill;
    if (groups.length === 0) return res.json(fail('Nothing to import'));

    const project = await queryOne('SELECT name, start_date, distributor FROM project WHERE id = ?', [projectId]);
    const deploymentStart = project?.start_date || nowDateTime().slice(0, 10);
    const projectName = toStr(project?.name) || `Project ${projectId}`;

    // Two-phase import: 'setup' (first import — assign the distributor and create
    // the sales + purchase headers) vs 'monthly' (delta import — reuse existing
    // headers, refresh prices, disable stopped resources, carry sales forward).
    const activeItemRow = await queryOne(
      'SELECT COUNT(*) AS c FROM project_item WHERE project_id = ? AND is_deleted = 0 AND status = 1', [projectId]);
    const requestedMode = toStr(req.body.mode);
    const mode = requestedMode === 'setup' || requestedMode === 'monthly'
      ? requestedMode
      : (Number(activeItemRow?.c ?? 0) > 0 ? 'monthly' : 'setup');
    const passedDistributor = toNum(req.body.distributor, 0);
    const projectDistributor = mode === 'setup' && passedDistributor > 0 ? passedDistributor : toNum(project?.distributor, 0);
    const stoppedIds: number[] = Array.isArray(req.body.stopped)
      ? req.body.stopped.map((x: any) => toNum(x)).filter((x: number) => x > 0)
      : [];

    const discount = createBill && month && year
      ? await resolveDiscountFor(projectId, month, year)
      : { ri: 0, payg: 0 };

    let itemsCreated = 0;
    let itemsMatched = 0;
    let headersCreated = 0;

    const result = await withTransaction(async (conn) => {
      // Track the project_item ids + costs we create, for optional bill fill.
      const created: Array<{ itemId: number; headerId: number; purchaseHeaderId: number; cost: number }> = [];

      // Honour "purchase headers stay as first assigned": reuse the header each
      // model already uses on this project (resolved from live items) rather than
      // matching by name, which would duplicate after a manual rename.
      const [modelPhRows]: any = await conn.query(
        `SELECT model, purchase_header_id, COUNT(*) AS c FROM project_item
         WHERE project_id = ? AND is_deleted = 0 AND purchase_header_id <> 0 AND model IN ('RI','PAYG')
         GROUP BY model, purchase_header_id ORDER BY c DESC`,
        [projectId]
      );
      const existingModelPh: Partial<Record<'RI' | 'PAYG', number>> = {};
      for (const r of modelPhRows as any[]) {
        const m: 'RI' | 'PAYG' = r.model === 'RI' ? 'RI' : 'PAYG';
        if (existingModelPh[m] == null) existingModelPh[m] = Number(r.purchase_header_id);
      }

      // The old portal keeps exactly TWO purchase headers per cloud project:
      // one for RI consumption and one for PAYG. Items are assigned by model.
      const purchaseHeaderNames: Record<'RI' | 'PAYG', string> = {
        RI: `Reserved Instances (RI) Consumption - ${projectName}`,
        PAYG: `Pay-as-you-go (PAYG) Consumption - ${projectName}`,
      };
      const purchaseHeaderIds: Partial<Record<'RI' | 'PAYG', number>> = {};
      const ensurePurchaseHeader = async (model: 'RI' | 'PAYG'): Promise<number> => {
        if (purchaseHeaderIds[model]) return purchaseHeaderIds[model]!;
        // Reuse the model's existing header first (monthly imports never re-create).
        if (existingModelPh[model]) {
          purchaseHeaderIds[model] = existingModelPh[model]!;
          return existingModelPh[model]!;
        }
        const name = purchaseHeaderNames[model];
        const [rows]: any = await conn.query(
          'SELECT id FROM purchase_header WHERE project_id = ? AND name = ? AND is_active = 1 LIMIT 1',
          [projectId, name]
        );
        let id: number;
        if (rows.length) id = rows[0].id;
        else {
          const [pr]: any = await conn.query(
            'INSERT INTO purchase_header (name, project_id, is_active, created_on) VALUES (?,?,1,?)',
            [name, projectId, nowDateTime()]
          );
          id = pr.insertId;
        }
        purchaseHeaderIds[model] = id;
        return id;
      };

      // Resolve a sales header for a group LAZILY: only look it up / create it
      // when a NEW item actually needs one, so re-imports never spawn empty
      // duplicate headers. In a "manual header" import each group carries the id
      // of an existing project_header (g.headerId) to place products into.
      for (const g of groups) {
        const headerName = toStr(g.header) || 'Cloud Services';
        const explicitHeaderId = toNum(g.headerId, 0);
        const groupPurchaseHeaderId = toNum(g.purchaseHeaderId, 0);
        const includedItems = (g.items || []).filter((it: any) => it.include !== false);
        if (includedItems.length === 0) continue;

        let resolvedHeaderId: number | null = explicitHeaderId > 0 ? explicitHeaderId : null;
        const resolveHeaderId = async (): Promise<number> => {
          if (resolvedHeaderId) return resolvedHeaderId;
          const [phRows]: any = await conn.query(
            'SELECT id FROM project_header WHERE project_id = ? AND name = ? AND is_deleted = 0 LIMIT 1',
            [projectId, headerName]
          );
          let id: number;
          if (phRows.length) {
            id = phRows[0].id;
          } else {
            const hash = await generateUniqueHash(['project_header', 'project_item'], 32);
            const [hr]: any = await conn.query(
              'INSERT INTO project_header (hash, project_id, name, quantity, amount, is_deleted) VALUES (?,?,?,0,0,0)',
              [hash, projectId, headerName]
            );
            id = hr.insertId;
            headersCreated++;
          }
          resolvedHeaderId = id;
          return id;
        };

        for (const it of includedItems) {
          const productId = await ensureProduct(conn, toStr(it.productName) || 'Azure Service');
          const model: 'RI' | 'PAYG' = it.model === 'RI' ? 'RI' : 'PAYG';
          // A group can pin all its products to a chosen purchase header; otherwise
          // fall back to the automatic RI / PAYG header for the item's model.
          const purchaseHeaderId = groupPurchaseHeaderId > 0 ? groupPurchaseHeaderId : await ensurePurchaseHeader(model);
          // Identity: full resource path when available; otherwise scope by the
          // bar name so identical no-resource meters in different bars never collide.
          // Keep the TAIL of long paths — the head is a shared /subscriptions/… prefix.
          const resourceId = toStr(it.resource || `${headerName}::${toStr(it.subscription)}`).slice(-180);

          // Idempotent matching: reuse an existing live item for the same
          // resource + product + model instead of creating a duplicate.
          const [existing]: any = await conn.query(
            `SELECT id, header_id, purchase_header_id FROM project_item
             WHERE project_id = ? AND resource_id = ? AND product = ? AND model = ?
               AND is_deleted = 0 AND status = 1
             LIMIT 1`,
            [projectId, resourceId, productId, model]
          );

          if (existing.length) {
            const ex = existing[0];
            // Where the item should live: an explicitly chosen header (manual
            // mode) wins; otherwise keep the item's current header — never move
            // it just because a re-import re-grouped resources differently.
            const targetHeader = explicitHeaderId > 0
              ? explicitHeaderId
              : (ex.header_id ?? await resolveHeaderId());
            const targetPurchaseHeader = groupPurchaseHeaderId > 0
              ? groupPurchaseHeaderId
              : (ex.purchase_header_id ?? purchaseHeaderId);
            // Keep quantity/unit price fresh from the latest month's data.
            await conn.query(
              'UPDATE project_item SET unit_price = ?, quantity = ?, header_id = ?, purchase_header_id = ? WHERE id = ?',
              [toNum(it.unitPrice), toNum(it.quantity), targetHeader, targetPurchaseHeader, ex.id]
            );
            created.push({
              itemId: ex.id,
              headerId: targetHeader,
              purchaseHeaderId: targetPurchaseHeader,
              cost: toNum(it.cost),
            });
            itemsMatched++;
          } else {
            const newHeaderId = await resolveHeaderId();
            const hash = await generateUniqueHash(['project_header', 'project_item'], 32);
            const [ir]: any = await conn.query(
              `INSERT INTO project_item
                (hash, resource_id, project_id, header_id, product, product_type, distributor,
                 deployment_start, deployment_end, model, unit_measure, unit_price, quantity,
                 deployed_product, discovery_status, purchase_header_id, description, status)
               VALUES (?,?,?,?,?,?,?,?,NULL,?,?,?,?,?,?,?,?,1)`,
              [
                hash, resourceId, projectId, newHeaderId, productId,
                null, projectDistributor || null, deploymentStart, model, null, toNum(it.unitPrice), toNum(it.quantity),
                null, null, purchaseHeaderId, toStr(it.meterCategory),
              ]
            );
            created.push({ itemId: ir.insertId, headerId: newHeaderId, purchaseHeaderId, cost: toNum(it.cost) });
            itemsCreated++;
          }
        }
      }

      // Optionally create/fill the monthly bill portal prices from the cost.
      let billId: number | null = null;
      if (createBill && month && year && created.length) {
        let [billRows]: any = await conn.query(
          'SELECT id FROM bill WHERE project_id = ? AND month = ? AND year = ? AND is_deleted = 0 LIMIT 1',
          [projectId, month, year]
        );
        if (billRows.length) {
          billId = billRows[0].id;
          // Refresh discounts on a still-draft bill (earlier import may have had none).
          await conn.query(
            'UPDATE bill SET ri_discount = ?, payg_discount = ? WHERE id = ? AND status <= 1',
            [discount.ri, discount.payg, billId]
          );
        } else {
          const hash = await generateUniqueHash(['bill']);
          const [br]: any = await conn.query(
            'INSERT INTO bill (hash, project_id, month, year, ri_discount, payg_discount, status, created_on) VALUES (?,?,?,?,?,?,1,?)',
            [hash, projectId, month, year, discount.ri, discount.payg, nowDateTime()]
          );
          billId = br.insertId;
        }

        // bill_header rows (per distinct sales header). Carry forward the most
        // recent prior month's sales amount as an editable default so the user
        // rarely has to re-enter customer pricing each month.
        const [prevBillRows]: any = await conn.query(
          `SELECT id FROM bill WHERE project_id = ? AND is_deleted = 0 AND (year < ? OR (year = ? AND month < ?))
           ORDER BY year DESC, month DESC LIMIT 1`,
          [projectId, year, year, month]
        );
        const carriedSales = new Map<number, number>();
        if (prevBillRows.length) {
          const [prevHeaders]: any = await conn.query(
            'SELECT header_id, amount FROM bill_header WHERE bill_id = ? AND is_deleted = 0',
            [prevBillRows[0].id]
          );
          for (const h of prevHeaders as any[]) carriedSales.set(Number(h.header_id), Number(h.amount ?? 0));
        }

        const headerIds = [...new Set(created.map((c) => c.headerId))];
        for (const hid of headerIds) {
          const [ex]: any = await conn.query(
            'SELECT id FROM bill_header WHERE bill_id = ? AND project_id = ? AND header_id = ? LIMIT 1',
            [billId, projectId, hid]
          );
          if (!ex.length) {
            await conn.query(
              'INSERT INTO bill_header (bill_id, project_id, header_id, amount, is_deleted) VALUES (?,?,?,?,0)',
              [billId, projectId, hid, carriedSales.get(hid) ?? 0]
            );
          }
        }
        // bill_item rows (portal_price = Azure cost) — upsert to stay idempotent.
        // Collapse duplicates (same project item hit from multiple preview
        // lines) by SUMMING their costs — overwriting would drop money.
        const byItem = new Map<number, { itemId: number; purchaseHeaderId: number; cost: number }>();
        for (const c of created) {
          const ex = byItem.get(c.itemId);
          if (ex) ex.cost += c.cost;
          else byItem.set(c.itemId, { itemId: c.itemId, purchaseHeaderId: c.purchaseHeaderId, cost: c.cost });
        }
        for (const c of byItem.values()) {
          const [biRows]: any = await conn.query(
            'SELECT id FROM bill_item WHERE bill_id = ? AND project_item_id = ? LIMIT 1',
            [billId, c.itemId]
          );
          if (biRows.length) {
            await conn.query(
              'UPDATE bill_item SET portal_price = ?, purchase_header_id = ? WHERE id = ?',
              [c.cost, c.purchaseHeaderId, biRows[0].id]
            );
          } else {
            await conn.query(
              'INSERT INTO bill_item (bill_id, project_id, project_item_id, purchase_header_id, portal_price) VALUES (?,?,?,?,?)',
              [billId, projectId, c.itemId, c.purchaseHeaderId, c.cost]
            );
          }
        }
        // bill_purchase_header rows (per distinct purchase header)
        const phIds = [...new Set(created.map((c) => c.purchaseHeaderId))];
        for (const pid of phIds) {
          const [ex]: any = await conn.query(
            'SELECT id FROM bill_purchase_header WHERE bill_id = ? AND project_id = ? AND purchase_header_id = ? LIMIT 1',
            [billId, projectId, pid]
          );
          if (!ex.length) {
            await conn.query(
              'INSERT INTO bill_purchase_header (bill_id, project_id, purchase_header_id, amount) VALUES (?,?,?,0)',
              [billId, projectId, pid]
            );
          }
        }
      }

      // Monthly import: disable resources that stopped (billed last month, gone
      // now). deployment_end = last day of the month before the imported month,
      // so they drop out of future bills but stay on record.
      if (stoppedIds.length) {
        const end = month && year
          ? (() => { const p = previousMonth(month, year); return lastDayOfMonth(p.year, p.month); })()
          : nowDateTime().slice(0, 10);
        for (const sid of stoppedIds) {
          await conn.query(
            'UPDATE project_item SET status = 0, deployment_end = ? WHERE id = ? AND project_id = ? AND is_deleted = 0',
            [end, sid, projectId]
          );
        }
      }

      // Setup import: stamp the chosen distributor onto the project.
      if (mode === 'setup' && passedDistributor > 0) {
        await conn.query('UPDATE project SET distributor = ? WHERE id = ?', [passedDistributor, projectId]);
      }

      return { billId };
    });

    const parts: string[] = [];
    if (itemsCreated) parts.push(`${itemsCreated} new item${itemsCreated > 1 ? 's' : ''}`);
    if (itemsMatched) parts.push(`${itemsMatched} existing item${itemsMatched > 1 ? 's' : ''} updated`);
    if (headersCreated) parts.push(`${headersCreated} new header${headersCreated > 1 ? 's' : ''}`);
    if (stoppedIds.length) parts.push(`${stoppedIds.length} stopped resource${stoppedIds.length > 1 ? 's' : ''} disabled`);
    const msg = parts.length ? `Imported: ${parts.join(', ')}` : 'Nothing imported';
    res.json(ok(msg, {
      mode, itemsCreated, itemsMatched, headersCreated, itemsStopped: stoppedIds.length,
      billId: result.billId, discount,
    }));
  })
);

export default router;
