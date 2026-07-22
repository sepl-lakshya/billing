import { Router } from 'express';
import { query, queryOne } from '../db';
import { asyncHandler, ok } from '../utils/helpers';

const router = Router();

/** Dashboard summary counts. */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const [
      cloudProjects,
      products,
      oems,
      distributors,
      invoices,
      bills,
    ] = await Promise.all([
      queryOne('SELECT COUNT(*) AS c FROM project WHERE is_active = 1 AND product_category = 1'),
      queryOne('SELECT COUNT(*) AS c FROM product WHERE is_active = 1'),
      queryOne('SELECT COUNT(*) AS c FROM oem WHERE is_active = 1'),
      queryOne('SELECT COUNT(*) AS c FROM distributor WHERE is_active = 1'),
      queryOne('SELECT COUNT(*) AS c FROM purchase_invoice WHERE is_deleted = 0'),
      queryOne('SELECT COUNT(*) AS c FROM bill WHERE is_deleted = 0'),
    ]);

    // Cloud projects with months that still have no bill (project start -> last month).
    const pendingBilling = await query(
      `SELECT p.id, p.hash, p.name,
              GREATEST(TIMESTAMPDIFF(MONTH, p.start_date, DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) + 1, 0)
                - (SELECT COUNT(*) FROM bill b WHERE b.project_id = p.id AND b.is_deleted = 0) AS months_pending
       FROM project p
       WHERE p.is_active = 1 AND p.product_category = 1 AND p.start_date IS NOT NULL
       HAVING months_pending > 0
       ORDER BY months_pending DESC
       LIMIT 10`
    );

    // Bills that are not yet fully priced (portal -> sales -> purchase pipeline).
    const inProgress = await query(
      `SELECT b.id, b.project_id, b.month, b.year, b.status, b.purchase_header_status, b.progress,
              (SELECT name FROM month WHERE value = b.month) AS month_name,
              (SELECT name FROM project WHERE id = b.project_id) AS project_name,
              (SELECT hash FROM project WHERE id = b.project_id) AS project_hash,
              (SELECT COALESCE(SUM(portal_price),0) FROM bill_item WHERE bill_id = b.id) AS portal_total
       FROM bill b
       WHERE b.is_deleted = 0 AND (b.status < 3 OR b.purchase_header_status = 0)
       ORDER BY b.year DESC, b.month DESC
       LIMIT 10`
    );

    // Portal vs sales totals for the last 6 billed months (trend).
    const trend = await query(
      `SELECT b.year, b.month,
              (SELECT name FROM month WHERE value = b.month) AS month_name,
              COALESCE(SUM(bi.portal_price),0) AS portal_total,
              COALESCE(SUM(bi.sales_price),0) AS sales_total
       FROM bill b
       LEFT JOIN bill_item bi ON bi.bill_id = b.id
       WHERE b.is_deleted = 0
       GROUP BY b.year, b.month
       ORDER BY b.year DESC, b.month DESC
       LIMIT 6`
    );

    res.json(
      ok('Dashboard', {
        cloudProjects: cloudProjects?.c ?? 0,
        products: products?.c ?? 0,
        oems: oems?.c ?? 0,
        distributors: distributors?.c ?? 0,
        invoices: invoices?.c ?? 0,
        bills: bills?.c ?? 0,
        pendingBilling,
        inProgress,
        trend: (trend as any[]).reverse(),
      })
    );
  })
);

export default router;
