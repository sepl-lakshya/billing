import { Router } from 'express';
import { query, execute } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

// =====================================================================
//  DEBIT NOTES
// =====================================================================

/** All debit notes (mirrors managedebitnotesection.php). */
router.get(
  '/debit',
  asyncHandler(async (_req, res) => {
    const rows = await query(`
      SELECT dn.*,
             (SELECT name FROM project WHERE id = dn.project_id) AS project_name,
             (SELECT name FROM debit_note_type WHERE value = dn.type) AS type_name,
             (SELECT name FROM credit_note_type WHERE value = dn.credit_type) AS credit_type_name,
             (SELECT number FROM purchase_invoice WHERE id = dn.invoice_id) AS invoice_number,
             (SELECT COALESCE(SUM(amount),0) FROM credit_note WHERE debit_note_id = dn.id) AS cn_val
      FROM debit_note dn
      ORDER BY dn.id DESC
    `);
    res.json(ok('Debit Notes', rows));
  })
);

/** Debit notes for a project (mirrors cloudprojectdebitnotesection.php). */
router.get(
  '/debit/project/:projectId',
  asyncHandler(async (req, res) => {
    const rows = await query(
      `SELECT dn.*,
              (SELECT name FROM debit_note_type WHERE value = dn.type) AS type_name,
              (SELECT name FROM credit_note_type WHERE value = dn.credit_type) AS credit_type_name,
              (SELECT name FROM month WHERE value = (SELECT month FROM bill WHERE id = dn.bill_id)) AS month_name,
              (SELECT year FROM bill WHERE id = dn.bill_id) AS year_val,
              (SELECT COALESCE(SUM(amount),0) FROM credit_note WHERE debit_note_id = dn.id) AS cn_val,
              (SELECT number FROM purchase_invoice WHERE id = dn.invoice_id) AS invoice_number
       FROM debit_note dn WHERE dn.project_id = ? ORDER BY dn.id DESC`,
      [toNum(req.params.projectId)]
    );
    res.json(ok('Debit Notes', rows));
  })
);

/** Create debit note - mirrors create_debit_note.php. */
router.post(
  '/debit',
  asyncHandler(async (req, res) => {
    const type = toNum(req.body.type ?? req.body['create-debit-note-type']);
    const projectId = toNum(req.body.projectId ?? req.body['create-debit-note-project-id']);
    const billId = toNum(req.body.billId ?? req.body['create-debit-note-bill-id']);
    const invoiceId = toNum(req.body.invoiceId ?? req.body['create-debit-note-invoice-id']);
    const amount = toStr(req.body.amount ?? req.body['create-debit-note-amount']);
    const creditType = toStr(req.body.creditType ?? req.body['create-debit-note-credit-type']);
    const remark = toStr(req.body.remark ?? req.body['create-debit-note-remark']);

    if (!projectId || !billId || !invoiceId || !type) return res.json(fail('Error : Missing Parameters'));
    if (amount === '') return res.json(fail('Please enter debit note amount'));
    if (creditType === '0' || creditType === '') return res.json(fail('Please select DN type'));

    await execute(
      `INSERT INTO debit_note (project_id, bill_id, invoice_id, type, credit_type, amount, remark, created_on)
       VALUES (?,?,?,?,?,?,?,?)`,
      [projectId, billId, invoiceId, type, toNum(creditType), toNum(amount), remark, nowDateTime()]
    );
    res.json(ok('Debit Note Created'));
  })
);

/** Delete debit note (cascade credit notes) - mirrors delete_debit_note.php. */
router.delete(
  '/debit/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    await execute('DELETE FROM credit_note WHERE debit_note_id = ?', [id]);
    await execute('DELETE FROM debit_note WHERE id = ?', [id]);
    res.json(ok('Debit Note Deleted'));
  })
);

// =====================================================================
//  CREDIT NOTES
// =====================================================================

/** All credit notes (mirrors managecreditnotesection.php). */
router.get(
  '/credit',
  asyncHandler(async (_req, res) => {
    const rows = await query(`
      SELECT cn.*,
             (SELECT name FROM project WHERE id = cn.project_id) AS project_name,
             (SELECT number FROM purchase_invoice WHERE id = cn.invoice_id) AS invoice_number
      FROM credit_note cn
      ORDER BY cn.id DESC
    `);
    res.json(ok('Credit Notes', rows));
  })
);

/** Credit notes for a project (mirrors cloudprojectcreditnotesection.php). */
router.get(
  '/credit/project/:projectId',
  asyncHandler(async (req, res) => {
    const rows = await query(
      `SELECT cn.*,
              (SELECT number FROM purchase_invoice WHERE id = cn.invoice_id) AS invoice_number
       FROM credit_note cn WHERE cn.project_id = ? ORDER BY cn.id DESC`,
      [toNum(req.params.projectId)]
    );
    res.json(ok('Credit Notes', rows));
  })
);

/** Create credit note - mirrors create_credit_note.php. */
router.post(
  '/credit',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.body.projectId ?? req.body['create-credit-note-project-id']);
    const billId = toNum(req.body.billId ?? req.body['create-credit-note-bill-id']);
    const invoiceId = toNum(req.body.invoiceId ?? req.body['create-credit-note-invoice-id']);
    const debitNoteId = toNum(req.body.debitNoteId ?? req.body['create-credit-note-dn-id']);
    const referenceNo = toStr(req.body.referenceNo ?? req.body['create-credit-note-reference-no']);
    const amount = toStr(req.body.amount ?? req.body['create-credit-note-amount']);
    const remark = toStr(req.body.remark ?? req.body['create-credit-note-remark']);

    if (!projectId || !billId || !invoiceId || !debitNoteId) return res.json(fail('Error : Missing Parameters'));
    if (referenceNo === '') return res.json(fail('Please enter reference number'));
    if (amount === '') return res.json(fail('Please enter credit amount'));

    await execute(
      `INSERT INTO credit_note (debit_note_id, project_id, bill_id, invoice_id, reference_no, amount, remark, created_on)
       VALUES (?,?,?,?,?,?,?,?)`,
      [debitNoteId, projectId, billId, invoiceId, referenceNo, toNum(amount), remark, nowDateTime()]
    );
    res.json(ok('Credit Note Created'));
  })
);

/** Delete credit note - mirrors delete_credit_note.php. */
router.delete(
  '/credit/:id',
  asyncHandler(async (req, res) => {
    await execute('DELETE FROM credit_note WHERE id = ?', [toNum(req.params.id)]);
    res.json(ok('Credit Note Deleted'));
  })
);

export default router;
