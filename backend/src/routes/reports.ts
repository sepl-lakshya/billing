import { Router } from 'express';
import { query } from '../db';
import { asyncHandler, ok, toStr } from '../utils/helpers';

const router = Router();

function projectClause(projectId: string, alias = 'p') {
  if (!projectId || projectId === 'All' || projectId === '0') return { sql: '', params: [] as any[] };
  return { sql: ` AND ${alias}.project_id = ?`, params: [Number(projectId)] };
}

router.get(
  '/',
  asyncHandler(async (req, res) => {
    const type = toStr(req.query.type || 'full');
    const projectId = toStr(req.query.projectId || 'All');

    if (type === 'full') {
      const projectFilter = projectId && projectId !== 'All' && projectId !== '0' ? ' AND p.id = ?' : '';
      const params = projectFilter ? [Number(projectId)] : [];
      const rows = await query(
        `SELECT p.id, p.name, p.city, (SELECT name FROM states WHERE value = p.state) AS state_name,
                p.tender_ref_no, p.start_date,
                (SELECT COUNT(*) FROM project_item WHERE project_id = p.id AND is_deleted = 0) AS item_count,
                (SELECT COUNT(*) FROM bill WHERE project_id = p.id AND is_deleted = 0) AS bill_count,
                (SELECT COALESCE(SUM(portal_price),0) FROM bill_item WHERE project_id = p.id) AS portal_total,
                (SELECT COALESCE(SUM(sales_price),0) FROM bill_item WHERE project_id = p.id) AS sales_total,
                (SELECT COALESCE(SUM(purchase_price),0) FROM bill_item WHERE project_id = p.id) AS purchase_total
         FROM project p
         WHERE p.is_active = 1 AND p.product_category = 1${projectFilter}
         ORDER BY p.created_on DESC`,
        params
      );
      return res.json(ok('Full Report', rows));
    }

    if (type === 'bill') {
      const filter = projectClause(projectId, 'b');
      const rows = await query(
        `SELECT b.id, b.project_id, (SELECT name FROM project WHERE id = b.project_id) AS project_name,
                (SELECT name FROM month WHERE value = b.month) AS month_name, b.year, b.status, b.progress,
                b.ri_discount, b.payg_discount,
                (SELECT COALESCE(SUM(portal_price),0) FROM bill_item WHERE bill_id = b.id) AS portal_total,
                (SELECT COALESCE(SUM(amount),0) FROM bill_header WHERE bill_id = b.id AND is_deleted = 0) AS sales_total,
                (SELECT COALESCE(SUM(purchase_price),0) FROM bill_item WHERE bill_id = b.id) AS purchase_total
         FROM bill b WHERE b.is_deleted = 0${filter.sql}
         ORDER BY b.year DESC, b.month DESC`,
        filter.params
      );
      return res.json(ok('Bill Report', rows));
    }

    if (type === 'dn') {
      const filter = projectClause(projectId, 'dn');
      const rows = await query(
        `SELECT dn.*, (SELECT name FROM project WHERE id = dn.project_id) AS project_name,
                (SELECT number FROM purchase_invoice WHERE id = dn.invoice_id) AS invoice_number,
                (SELECT name FROM debit_note_type WHERE value = dn.type) AS type_name,
                (SELECT name FROM credit_note_type WHERE value = dn.credit_type) AS credit_type_name,
                (SELECT COALESCE(SUM(amount),0) FROM credit_note WHERE debit_note_id = dn.id) AS credit_total
         FROM debit_note dn WHERE 1=1${filter.sql} ORDER BY dn.created_on DESC`,
        filter.params
      );
      return res.json(ok('DN Report', rows));
    }

    const filter = projectClause(projectId, 'pi');
    const rows = await query(
      `SELECT pi.*, (SELECT name FROM project WHERE id = pi.project_id) AS project_name,
              (SELECT name FROM gst_slab WHERE percentage = pi.gst_slab) AS gst_name,
              (SELECT bill_id FROM bill_invoice_mapping WHERE invoice_id = pi.id AND project_id = pi.project_id LIMIT 1) AS bill_id
       FROM purchase_invoice pi WHERE pi.is_deleted = 0${filter.sql} ORDER BY pi.created_on DESC`,
      filter.params
    );
    return res.json(ok('Invoice Report', rows));
  })
);

export default router;
