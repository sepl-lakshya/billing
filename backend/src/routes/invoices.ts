import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

/** All invoices (mirrors manageinvoicesection.php). */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(`
      SELECT pi.*,
             (SELECT name FROM project WHERE id = pi.project_id) AS project_name,
             (SELECT name FROM gst_slab WHERE percentage = pi.gst_slab) AS gst_name,
             (SELECT bill_id FROM bill_invoice_mapping WHERE invoice_id = pi.id AND project_id = pi.project_id LIMIT 1) AS bill_id
      FROM purchase_invoice pi
      WHERE pi.is_deleted = 0
      ORDER BY pi.created_on DESC
    `);
    res.json(ok('Invoices', rows));
  })
);

/** Invoices for a project (mirrors cloudprojectinvoicesection.php). */
router.get(
  '/project/:projectId',
  asyncHandler(async (req, res) => {
    const rows = await query(
      `SELECT pi.*,
              (SELECT name FROM gst_slab WHERE percentage = pi.gst_slab) AS gst_name,
              (SELECT bill_id FROM bill_invoice_mapping WHERE invoice_id = pi.id AND project_id = pi.project_id LIMIT 1) AS bill_id
       FROM purchase_invoice pi
       WHERE pi.is_deleted = 0 AND pi.project_id = ?
       ORDER BY pi.created_on DESC`,
      [toNum(req.params.projectId)]
    );
    res.json(ok('Invoices', rows));
  })
);

/** Add invoice - mirrors add_invoice.php. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body['add-invoice-project-id']);
    const referenceNo = toStr(req.body.referenceNo ?? req.body['add-invoice-reference-no']);
    const amount = toStr(req.body.amount ?? req.body['add-invoice-amount']);
    const gstSlab = toStr(req.body.gstSlab ?? req.body['add-invoice-gst-slab']);
    let date = toStr(req.body.date ?? req.body['add-invoice-date']);
    const description = toStr(req.body.description ?? req.body['add-invoice-desc']);
    const invoiceType = toStr(req.body.type ?? req.body['add-invoice-type']);

    if (!projectId) return res.json(fail('Please select project'));
    if (referenceNo === '') return res.json(fail('Please enter invoice reference number'));
    if (amount === '') return res.json(fail('Please enter invoice total amount'));
    if (gstSlab === '0' || gstSlab === '') return res.json(fail('Please select GST Slab'));
    if (invoiceType === '') return res.json(fail('Please select invoice type'));

    const existing = await queryOne('SELECT id FROM purchase_invoice WHERE number = ? AND is_deleted = 0', [referenceNo]);
    if (existing) return res.json(fail('Invoice reference number already exist'));

    if (date === '') date = nowDateTime().slice(0, 10);

    const result = await execute(
      `INSERT INTO purchase_invoice (project_id, number, amount, gst_slab, invoice_date, description, type, is_deleted, created_on)
       VALUES (?,?,?,?,?,?,?,0,?)`,
      [projectId, referenceNo, toNum(amount), toNum(gstSlab), date, description, toNum(invoiceType, 0), nowDateTime()]
    );
    res.json(ok('Invoice Added', { id: result.insertId }));
  })
);

/** Delete invoice + dependents - mirrors delete_invoice.php. */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    await execute('DELETE FROM bill_invoice_mapping WHERE invoice_id = ?', [id]);
    await execute('DELETE FROM debit_note WHERE invoice_id = ?', [id]);
    await execute('DELETE FROM credit_note WHERE invoice_id = ?', [id]);
    await execute('DELETE FROM purchase_invoice WHERE id = ?', [id]);
    res.json(ok('Invoice Deleted'));
  })
);

/** Invoices available to tag to a bill (mirrors purchasebillinvoicesection.php). */
router.get(
  '/project/:projectId/untagged',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.projectId);
    const rows = await query(
      `SELECT id, number, amount, gst_slab FROM purchase_invoice pi
       WHERE pi.is_deleted = 0 AND pi.project_id = ?
         AND id NOT IN (SELECT invoice_id FROM bill_invoice_mapping WHERE project_id = ?)`,
      [projectId, projectId]
    );
    res.json(ok('Untagged Invoices', rows));
  })
);

/** Tag invoice to a bill - mirrors add_purchase_bill_invoice.php. */
router.post(
  '/bill-mapping',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body['add-purchase-bill-invoice-project-id']);
    const billId = toNum(req.body.billId ?? req.body['add-purchase-bill-invoice-bill-id']);
    const invoiceId = toNum(req.body.invoiceId ?? req.body['add-purchase-bill-invoice-id']);
    if (!projectId || !billId || !invoiceId) return res.json(fail('Error : Missing parameters'));

    const alreadyTagged = await queryOne(
      'SELECT id FROM bill_invoice_mapping WHERE bill_id = ? AND project_id = ? AND invoice_id = ?',
      [billId, projectId, invoiceId]
    );
    if (alreadyTagged) return res.json(fail('This invoice is already linked to this bill'));

    await execute('INSERT INTO bill_invoice_mapping (bill_id, project_id, invoice_id) VALUES (?,?,?)', [
      billId, projectId, invoiceId,
    ]);
    await execute('UPDATE debit_note SET bill_id = ? WHERE invoice_id = ? AND project_id = ?', [
      billId, invoiceId, projectId,
    ]);
    res.json(ok('Invoice Tagged'));
  })
);

/** Untag invoice from a bill - mirrors delete_purchase_bill_invoice.php. */
router.delete(
  '/bill-mapping',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.query.projectId);
    const billId = toNum(req.body.billId ?? req.query.billId);
    const invoiceId = toNum(req.body.invoiceId ?? req.query.invoiceId);
    await execute('DELETE FROM bill_invoice_mapping WHERE bill_id = ? AND invoice_id = ? AND project_id = ?', [
      billId, invoiceId, projectId,
    ]);
    await execute('DELETE FROM debit_note WHERE bill_id = ? AND invoice_id = ? AND project_id = ?', [
      billId, invoiceId, projectId,
    ]);
    await execute('DELETE FROM credit_note WHERE bill_id = ? AND invoice_id = ? AND project_id = ?', [
      billId, invoiceId, projectId,
    ]);
    res.json(ok('Invoice Untagged'));
  })
);

export default router;
