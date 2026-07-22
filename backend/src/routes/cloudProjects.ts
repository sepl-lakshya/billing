import { Router } from 'express';
import multer from 'multer';
import fs from 'fs';
import path from 'path';
import { config } from '../config';
import { query, execute, queryOne, generateUniqueHash } from '../db';
import {
  asyncHandler, ok, fail, toStr, toNum, nowDateTime, explode, generateMonths, getName,
} from '../utils/helpers';

const router = Router();

// ---- File upload storage for attachments ----
const attachmentDir = path.join(config.uploadDir, 'attachment');
if (!fs.existsSync(attachmentDir)) fs.mkdirSync(attachmentDir, { recursive: true });
const upload = multer({
  storage: multer.diskStorage({
    destination: (_req, _file, cb) => cb(null, attachmentDir),
    filename: (_req, file, cb) => {
      const ext = path.extname(file.originalname);
      cb(null, `${Date.now()}_${getName(16)}${ext}`);
    },
  }),
  limits: { fileSize: 25 * 1024 * 1024 },
});

// =====================================================================
//  PROJECT CRUD
// =====================================================================

/** List cloud projects (mirrors cloudprojectssection.php, auth filter removed). */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(`
      SELECT p.*,
             (SELECT name FROM states WHERE value = p.state) AS state_name,
             (SELECT name FROM distributor WHERE id = p.distributor) AS distributor_name,
             (SELECT full_name FROM users WHERE id = p.created_by) AS created_by_name,
             (SELECT email FROM users WHERE id = p.created_by) AS created_by_email
      FROM project p
      WHERE p.is_active = 1 AND p.product_category = 1
      ORDER BY p.created_on DESC
    `);
    res.json(ok('Cloud Projects', rows));
  })
);

/** Get a single project (accepts numeric id or hash). */
router.get(
  '/:id',
  asyncHandler(async (req, res) => {
    const idOrHash = req.params.id;
    const project = await queryOne(
      `SELECT p.*, (SELECT name FROM states WHERE value = p.state) AS state_name,
              (SELECT name FROM distributor WHERE id = p.distributor) AS distributor_name,
              (SELECT full_name FROM users WHERE id = p.created_by) AS created_by_name,
              (SELECT COALESCE(SUM(bi.portal_price),0) FROM bill_item bi INNER JOIN bill b ON b.id = bi.bill_id WHERE b.project_id = p.id AND b.is_deleted = 0) AS total_portal,
              (SELECT COALESCE(SUM(bh.amount),0) FROM bill_header bh INNER JOIN bill b ON b.id = bh.bill_id WHERE b.project_id = p.id AND b.is_deleted = 0 AND bh.is_deleted = 0) AS total_sales,
              (SELECT COALESCE(SUM(bph.amount),0) FROM bill_purchase_header bph INNER JOIN bill b ON b.id = bph.bill_id WHERE b.project_id = p.id AND b.is_deleted = 0) AS total_purchase,
              (SELECT COALESCE(SUM(pi.amount),0) FROM purchase_invoice pi INNER JOIN bill_invoice_mapping m ON m.invoice_id = pi.id WHERE m.project_id = p.id AND pi.is_deleted = 0) AS total_invoiced,
              (SELECT COUNT(*) FROM bill WHERE project_id = p.id AND is_deleted = 0) AS bill_count,
              (SELECT COUNT(*) FROM project_item WHERE project_id = p.id AND is_deleted = 0 AND status = 1) AS item_count
       FROM project p WHERE p.id = ? OR p.hash = ? LIMIT 1`,
      [toNum(idOrHash), idOrHash]
    );
    if (!project) return res.json(fail('Project not found'));
    res.json(ok('Project', project));
  })
);

/** Create project - mirrors create_project.php. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const name = toStr(req.body['project-name'] ?? req.body.name);
    const city = toStr(req.body['project-city'] ?? req.body.city);
    const state = toStr(req.body['project-state'] ?? req.body.state);
    const tenderNo = toStr(req.body['tender-ref-no'] ?? req.body.tenderRefNo);
    const startDate = toStr(req.body['project-start-date'] ?? req.body.startDate);
    const productCategory = toNum(req.body['product-category'] ?? req.body.productCategory, 1);
    const distributor = toNum(req.body['project-distributor'] ?? req.body.distributor, 0);
    const description = toStr(req.body['project-description'] ?? req.body.description);

    if (name === '') return res.json(fail('Please enter project name'));
    if (city === '') return res.json(fail('Please enter project city'));
    if (state === '0' || state === '') return res.json(fail('Please select project state'));
    if (tenderNo === '') return res.json(fail('Please enter tender number'));
    if (startDate === '') return res.json(fail('Please select project start date'));

    const existing = await queryOne('SELECT id FROM project WHERE name = ?', [name]);
    if (existing) return res.json(fail('Project name already exist'));

    const hash = await generateUniqueHash(['project']);
    const result = await execute(
      `INSERT INTO project
        (hash, created_by, product_category, name, city, state, distributor, tender_ref_no, start_date, description, is_active, created_on)
       VALUES (?,?,?,?,?,?,?,?,?,?,1,?)`,
      [hash, config.systemUserId, productCategory, name, city, state, distributor, tenderNo, startDate, description, nowDateTime()]
    );
    res.json(ok('Project Created', { id: result.insertId, hash }));
  })
);

/** Update project - mirrors update_cloud_project.php. */
router.put(
  '/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    const city = toStr(req.body['edit-project-city'] ?? req.body.city);
    const state = toStr(req.body['edit-project-state'] ?? req.body.state);
    const distributor = toNum(req.body['edit-project-distributor'] ?? req.body.distributor, 0);
    const tenderNo = toStr(req.body['edit-tender-ref-no'] ?? req.body.tenderRefNo);
    const description = toStr(req.body['edit-project-description'] ?? req.body.description);

    await execute(
      'UPDATE project SET city = ?, state = ?, distributor = ?, tender_ref_no = ?, description = ? WHERE id = ?',
      [city, state, distributor, tenderNo, description, id]
    );
    res.json(ok('Project Updated'));
  })
);

/** Delete project + all related records - mirrors delete_cloud_project.php cascade. */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    // Remove attachment files from disk first.
    const attachments = await query('SELECT file_name FROM project_attachment WHERE project_id = ?', [id]);
    for (const a of attachments) {
      const fp = path.join(attachmentDir, a.file_name);
      if (fs.existsSync(fp)) {
        try { fs.unlinkSync(fp); } catch { /* ignore */ }
      }
    }
    const tables = [
      'project_discount', 'project_item', 'bill', 'bill_item', 'project_user_mapping',
      'bill_header', 'bill_invoice_mapping', 'bill_purchase_header',
      'debit_note', 'credit_note',
      'project_header', 'purchase_header', 'purchase_invoice',
      'project_adjustment', 'project_attachment',
    ];
    for (const t of tables) {
      await execute(`DELETE FROM ${t} WHERE project_id = ?`, [id]);
    }
    await execute('DELETE FROM project WHERE id = ?', [id]);
    res.json(ok('Project Deleted'));
  })
);

// =====================================================================
//  PROJECT MONTH LIST (for billing)
// =====================================================================
router.get(
  '/:id/months',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    const project = await queryOne('SELECT start_date FROM project WHERE id = ?', [id]);
    const months = generateMonths(project?.start_date ?? null);
    res.json(ok('Months', months));
  })
);

// =====================================================================
//  HEADERS
// =====================================================================
router.get(
  '/:id/headers',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    const headers = await query(
      'SELECT * FROM project_header WHERE project_id = ? AND is_deleted = 0 ORDER BY id',
      [id]
    );
    res.json(ok('Headers', headers));
  })
);

/** Add header - mirrors add_cloud_project_header.php. */
router.post(
  '/:id/headers',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const name = toStr(req.body['add-header-name'] ?? req.body.name);
    const quantity = toNum(req.body['add-header-quantity'] ?? req.body.quantity, 0);
    const description = toStr(req.body['add-header-description'] ?? req.body.description);
    if (name === '') return res.json(fail('Please enter header name'));

    const hash = await generateUniqueHash(['project_header', 'project_item'], 32);
    await execute(
      'INSERT INTO project_header (hash, project_id, name, quantity, amount, description, is_deleted) VALUES (?,?,?,?,0,?,0)',
      [hash, projectId, name, quantity, description]
    );
    res.json(ok('Header Added'));
  })
);

/** Edit header - mirrors edit_cloud_project_header.php. */
router.put(
  '/:id/headers/:headerId',
  asyncHandler(async (req, res) => {
    const headerId = toNum(req.params.headerId);
    const projectId = toNum(req.params.id);
    const name = toStr(req.body['edit-header-name'] ?? req.body.name);
    const quantity = toNum(req.body['edit-header-quantity'] ?? req.body.quantity, 0);
    const description = toStr(req.body['edit-header-description'] ?? req.body.description);
    if (name === '') return res.json(fail('Please enter header name'));

    await execute(
      'UPDATE project_header SET name = ?, quantity = ?, description = ? WHERE id = ? AND project_id = ?',
      [name, quantity, description, headerId, projectId]
    );
    res.json(ok('Header Updated'));
  })
);

/** Soft delete header + its items - mirrors delete_cloud_project_header.php. */
router.delete(
  '/headers/:headerId',
  asyncHandler(async (req, res) => {
    const headerId = toNum(req.params.headerId);
    await execute('UPDATE project_item SET is_deleted = 1 WHERE header_id = ?', [headerId]);
    await execute('UPDATE project_header SET is_deleted = 1 WHERE id = ?', [headerId]);
    res.json(ok('Header Deleted'));
  })
);

// =====================================================================
//  ITEMS
// =====================================================================

/** List items with product/header/lookup names (mirrors viewcloudprojectproductlistsection.php). */
router.get(
  '/:id/items',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    const items = await query(`
      SELECT pi.*,
             (SELECT name FROM project_header WHERE id = pi.header_id) AS header_name,
             (SELECT name FROM product WHERE id = pi.product) AS product_name,
             (SELECT name FROM product WHERE id = pi.deployed_product) AS deployed_product_name,
             (SELECT name FROM distributor WHERE id = pi.distributor) AS distributor_name,
             (SELECT name FROM unit_measure WHERE value = pi.unit_measure) AS unit_measure_name,
             (SELECT name FROM cloud_category WHERE value = pi.product_type) AS product_type_name,
             (SELECT name FROM project_item_discovery WHERE value = pi.discovery_status) AS discovery_name,
             (SELECT name FROM purchase_header WHERE id = pi.purchase_header_id) AS purchase_header_name
      FROM project_item pi
      WHERE pi.project_id = ? AND pi.is_deleted = 0
      ORDER BY pi.header_id, pi.id
    `, [id]);
    res.json(ok('Items', items));
  })
);

/**
 * Add one or more items to a header. Accepts either an existing header id
 * (add-item-header-id) or a new header name (header-name). Item fields can be
 * supplied as legacy comma arrays (productarrpost...) or as an `items` JSON array.
 * Mirrors add_cloud_project_product.php.
 */
router.post(
  '/:id/items',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    let headerId = toNum(req.body['add-item-header-id'] ?? req.body.headerId, 0);
    const headerName = toStr(req.body['header-name'] ?? req.body.headerName);

    if (!headerId) {
      if (headerName === '') return res.json(fail('Please enter header name.'));
      const hash = await generateUniqueHash(['project_header', 'project_item'], 32);
      const r = await execute(
        'INSERT INTO project_header (hash, project_id, name, amount, is_deleted) VALUES (?,?,?,0,0)',
        [hash, projectId, headerName]
      );
      headerId = r.insertId;
    }

    // Build the item list either from a JSON `items` array or the legacy arrays.
    type Item = Record<string, any>;
    let items: Item[] = [];
    if (Array.isArray(req.body.items)) {
      items = req.body.items;
    } else {
      const products = explode(req.body.productarrpost);
      const productTypes = explode(req.body.producttypearrpost);
      const distributors = explode(req.body.distributorarrpost);
      const deployStarts = explode(req.body.deploymentstartarrpost);
      const deployEnds = explode(req.body.deploymentendarrpost);
      const unitMeasures = explode(req.body.unitmeasurearrpost);
      const unitPrices = explode(req.body.unitpricearrpost);
      const quantities = explode(req.body.quantityarrpost);
      const deployedProducts = explode(req.body.productdeployedarrpost);
      const discoveries = explode(req.body.productdiscoveryarrpost);
      const purchaseHeaders = explode(req.body.purchaseheaderarrpost);
      const descriptions = explode(req.body.productdescarrpost);
      const resourceIds = explode(req.body.resourceidarrpost);
      const models = explode(req.body.modelarrpost);
      for (let i = 0; i < products.length; i++) {
        items.push({
          product: products[i], productType: productTypes[i], distributor: distributors[i],
          deploymentStart: deployStarts[i], deploymentEnd: deployEnds[i], unitMeasure: unitMeasures[i],
          unitPrice: unitPrices[i], quantity: quantities[i], deployedProduct: deployedProducts[i],
          discoveryStatus: discoveries[i], purchaseHeaderId: purchaseHeaders[i], description: descriptions[i],
          resourceId: resourceIds[i], model: models[i],
        });
      }
    }

    if (items.length === 0) return res.json(fail('Error : No item found'));

    for (const it of items) {
      const deploymentEnd = toStr(it.deploymentEnd) || null;
      const status = deploymentEnd ? 0 : 1;
      const hash = await generateUniqueHash(['project_header', 'project_item'], 32);
      await execute(
        `INSERT INTO project_item
          (hash, resource_id, project_id, header_id, product, product_type, distributor,
           deployment_start, deployment_end, model, unit_measure, unit_price, quantity,
           deployed_product, discovery_status, purchase_header_id, description, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`,
        [
          hash, toStr(it.resourceId), projectId, headerId, toNum(it.product) || null,
          toNum(it.productType) || null, toNum(it.distributor) || null,
          toStr(it.deploymentStart) || null, deploymentEnd, toStr(it.model),
          toNum(it.unitMeasure) || null, toNum(it.unitPrice, 0), toNum(it.quantity, 0),
          toNum(it.deployedProduct) || null, toNum(it.discoveryStatus) || null,
          toNum(it.purchaseHeaderId, 0), toStr(it.description), status,
        ]
      );
    }
    res.json(ok('Product Added', { headerId }));
  })
);

/** Get single item (for edit form). */
router.get(
  '/items/:itemId',
  asyncHandler(async (req, res) => {
    const item = await queryOne('SELECT * FROM project_item WHERE id = ?', [toNum(req.params.itemId)]);
    if (!item) return res.json(fail('Item not found'));
    res.json(ok('Item', item));
  })
);

/** Edit item - mirrors edit_cloud_project_item.php. */
router.put(
  '/items/:itemId',
  asyncHandler(async (req, res) => {
    const itemId = toNum(req.params.itemId);
    const unitMeasure = toNum(req.body['edit-unit-measure'] ?? req.body.unitMeasure) || null;
    const unitPrice = toNum(req.body['edit-unit-price'] ?? req.body.unitPrice, 0);
    const quantity = toNum(req.body['edit-quantity'] ?? req.body.quantity, 0);
    const deployedProduct = toNum(req.body['edit-product-deployed'] ?? req.body.deployedProduct) || null;
    const discovery = toNum(req.body['edit-product-discovery'] ?? req.body.discoveryStatus) || null;
    const purchaseHeader = toNum(req.body['edit-product-purchase-header'] ?? req.body.purchaseHeaderId, 0);
    const resourceId = toStr(req.body['edit-product-resource-id'] ?? req.body.resourceId);
    const description = toStr(req.body['edit-product-desc'] ?? req.body.description);

    await execute(
      `UPDATE project_item SET unit_measure = ?, unit_price = ?, quantity = ?, deployed_product = ?,
        discovery_status = ?, purchase_header_id = ?, resource_id = ?, description = ? WHERE id = ?`,
      [unitMeasure, unitPrice, quantity, deployedProduct, discovery, purchaseHeader, resourceId, description, itemId]
    );
    res.json(ok('Item Updated'));
  })
);

/** Soft delete item - mirrors delete_project_item.php. */
router.delete(
  '/items/:itemId',
  asyncHandler(async (req, res) => {
    await execute('UPDATE project_item SET is_deleted = 1 WHERE id = ?', [toNum(req.params.itemId)]);
    res.json(ok('Item Deleted'));
  })
);

/** Disable item (set deployment end) - mirrors disable_project_item.php. */
router.post(
  '/items/:itemId/disable',
  asyncHandler(async (req, res) => {
    const itemId = toNum(req.params.itemId);
    const deploymentEnd = toStr(req.body['deactivate-item-deployment-end'] ?? req.body.deploymentEnd);
    await execute('UPDATE project_item SET status = 0, deployment_end = ? WHERE id = ?', [deploymentEnd || null, itemId]);
    res.json(ok('Item Deactivated'));
  })
);

// =====================================================================
//  DISCOUNTS
// =====================================================================
router.get(
  '/:id/discounts',
  asyncHandler(async (req, res) => {
    const rows = await query(
      'SELECT * FROM project_discount WHERE project_id = ? ORDER BY from_date',
      [toNum(req.params.id)]
    );
    res.json(ok('Discounts', rows));
  })
);

/**
 * Re-apply the currently configured discount to every bill that is not yet
 * finalized, so adding or changing a discount AFTER a bill was created (e.g. by
 * importing consumption before any discount existed) still takes effect. A bill
 * is considered locked only once its purchase is confirmed
 * (status >= 3 AND purchase_header_status = 1); everything else is refreshed.
 */
async function refreshBillDiscounts(projectId: number) {
  const bills = await query(
    `SELECT id, month, year FROM bill
     WHERE project_id = ? AND is_deleted = 0 AND NOT (status >= 3 AND purchase_header_status = 1)`,
    [projectId]
  );
  for (const b of bills) {
    const dd = `${b.year}-${String(b.month).padStart(2, '0')}-15`;
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
    await execute('UPDATE bill SET ri_discount = ?, payg_discount = ? WHERE id = ?',
      [Number(row?.ri_discount ?? 0), Number(row?.payg_discount ?? 0), b.id]);
  }
}

/** Add first discount - mirrors add_cloud_project_discount.php (from_date = project start). */
router.post(
  '/:id/discounts',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const ri = toStr(req.body['add-ri-discount'] ?? req.body.riDiscount);
    const payg = toStr(req.body['add-payg-discount'] ?? req.body.paygDiscount);
    const creditDays = toStr(req.body['add-credit-days'] ?? req.body.creditDays);
    if (ri === '') return res.json(fail('Please enter RI discount'));
    if (payg === '') return res.json(fail('Please select PAYG discount'));
    if (creditDays === '') return res.json(fail('Please enter credit days'));

    await execute(
      `INSERT INTO project_discount (project_id, from_date, to_date, ri_discount, payg_discount, credit_days, created_on)
       VALUES (?, (SELECT start_date FROM project WHERE id = ?), NULL, ?, ?, ?, CURDATE())`,
      [projectId, projectId, toNum(ri), toNum(payg), toNum(creditDays)]
    );
    await refreshBillDiscounts(projectId);
    res.json(ok('Discount added'));
  })
);

/** Update discount from a chosen month - mirrors update_cloud_project_discount.php. */
router.put(
  '/:id/discounts',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const ri = toStr(req.body['change-ri-discount'] ?? req.body.riDiscount);
    const payg = toStr(req.body['change-payg-discount'] ?? req.body.paygDiscount);
    const creditDays = toStr(req.body['change-credit-days'] ?? req.body.creditDays);
    const year = toNum(req.body['discount-year'] ?? req.body.year);
    const month = toNum(req.body['discount-month'] ?? req.body.month);
    if (payg === '') return res.json(fail('Please enter PAYG discount'));
    if (ri === '') return res.json(fail('Please enter RI discount'));
    if (creditDays === '') return res.json(fail('Please enter credit days'));
    if (!year) return res.json(fail('Please select year'));
    if (!month) return res.json(fail('Please select month'));

    // to_date = last day of the previous month; from_date = 1st of chosen month.
    const prev = new Date(year, month - 1, 1);
    prev.setDate(0); // last day of previous month
    const pad = (n: number) => String(n).padStart(2, '0');
    const toDate = `${prev.getFullYear()}-${pad(prev.getMonth() + 1)}-${pad(prev.getDate())}`;
    const fromDate = `${year}-${pad(month)}-01`;

    await execute('UPDATE project_discount SET to_date = ? WHERE project_id = ? AND to_date IS NULL', [toDate, projectId]);
    await execute(
      `INSERT INTO project_discount (project_id, from_date, to_date, ri_discount, payg_discount, credit_days, created_on)
       VALUES (?, ?, NULL, ?, ?, ?, CURDATE())`,
      [projectId, fromDate, toNum(ri), toNum(payg), toNum(creditDays)]
    );
    await refreshBillDiscounts(projectId);
    res.json(ok('Discount Updated'));
  })
);

// =====================================================================
//  ADJUSTMENTS
// =====================================================================
router.get(
  '/:id/adjustments',
  asyncHandler(async (req, res) => {
    const rows = await query(
      `SELECT pa.*, (SELECT name FROM adjustment_type WHERE value = pa.adjustment_type_id) AS adjustment_name
       FROM project_adjustment pa WHERE pa.project_id = ? ORDER BY pa.id DESC`,
      [toNum(req.params.id)]
    );
    res.json(ok('Adjustments', rows));
  })
);

/** Add adjustment - mirrors add_cloud_project_adjustment.php (reference not already a debit note). */
router.post(
  '/:id/adjustments',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const type = toNum(req.body['add-adjustment-type'] ?? req.body.adjustmentType);
    const amount = toStr(req.body['add-adjustment-amount'] ?? req.body.amount);
    const referenceNo = toStr(req.body['add-adjustment-reference-number'] ?? req.body.referenceNo);
    if (!type) return res.json(fail('Please select adjustment type'));
    if (amount === '') return res.json(fail('Please enter amount'));

    await execute(
      'INSERT INTO project_adjustment (project_id, adjustment_type_id, amount, reference_no, created_on) VALUES (?,?,?,?,?)',
      [projectId, type, toNum(amount), referenceNo, nowDateTime()]
    );
    res.json(ok('Adjustment Added'));
  })
);

router.delete(
  '/:id/adjustments/:adjustmentId',
  asyncHandler(async (req, res) => {
    await execute('DELETE FROM project_adjustment WHERE id = ? AND project_id = ?', [
      toNum(req.params.adjustmentId), toNum(req.params.id),
    ]);
    res.json(ok('Adjustment Deleted'));
  })
);

// =====================================================================
//  ATTACHMENTS
// =====================================================================
router.get(
  '/:id/attachments',
  asyncHandler(async (req, res) => {
    const rows = await query('SELECT * FROM project_attachment WHERE project_id = ? ORDER BY id DESC', [
      toNum(req.params.id),
    ]);
    res.json(ok('Attachments', rows));
  })
);

/** Upload attachment - mirrors add_attachment_file.php. */
router.post(
  '/:id/attachments',
  upload.single('file'),
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const title = toStr(req.body['file-title'] ?? req.body.title);
    if (!req.file) return res.json(fail('Please choose a file'));
    await execute(
      'INSERT INTO project_attachment (project_id, title, original_name, file_name, created_on) VALUES (?,?,?,?,?)',
      [projectId, title, req.file.originalname, req.file.filename, nowDateTime()]
    );
    res.json(ok('File Uploaded'));
  })
);

/** Delete attachment (file + row) - mirrors delete_attachment_file.php. */
router.delete(
  '/:id/attachments/:attachmentId',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const attachmentId = toNum(req.params.attachmentId);
    const att = await queryOne('SELECT file_name FROM project_attachment WHERE id = ? AND project_id = ?', [
      attachmentId, projectId,
    ]);
    if (att?.file_name) {
      const fp = path.join(attachmentDir, att.file_name);
      if (fs.existsSync(fp)) {
        try { fs.unlinkSync(fp); } catch { /* ignore */ }
      }
    }
    await execute('DELETE FROM project_attachment WHERE id = ? AND project_id = ?', [attachmentId, projectId]);
    res.json(ok('File Deleted'));
  })
);

// =====================================================================
//  ASSIGNMENTS
// =====================================================================
router.get(
  '/:id/assignments',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const assigned = await query(
      `SELECT pum.*,
              (SELECT full_name FROM users WHERE id = pum.user_id) AS user_full_name,
              (SELECT email FROM users WHERE id = pum.user_id) AS user_email
       FROM project_user_mapping pum WHERE pum.project_id = ?`,
      [projectId]
    );
    const available = await query(
      `SELECT id, full_name, email FROM users
       WHERE is_active = 1 AND user_type = 2
         AND id NOT IN (SELECT user_id FROM project_user_mapping WHERE project_id = ?)
         AND id NOT IN (SELECT created_by FROM project WHERE id = ?)`,
      [projectId, projectId]
    );
    res.json(ok('Assignments', { assigned, available }));
  })
);

/** Assign user - mirrors assign_cloud_project.php. */
router.post(
  '/:id/assignments',
  asyncHandler(async (req, res) => {
    const projectId = toNum(req.params.id);
    const userId = toNum(req.body['assign-user-id'] ?? req.body.userId);
    if (!userId) return res.json(fail('Please select a user'));
    const existing = await queryOne(
      'SELECT id FROM project_user_mapping WHERE project_id = ? AND user_id = ?',
      [projectId, userId]
    );
    if (existing) return res.json(fail('User already assigned'));
    await execute(
      'INSERT INTO project_user_mapping (project_id, user_id, assigned_by, created_on) VALUES (?,?,?,?)',
      [projectId, userId, config.systemUserId, nowDateTime()]
    );
    res.json(ok('User Assigned'));
  })
);

router.delete(
  '/:id/assignments/:userId',
  asyncHandler(async (req, res) => {
    await execute('DELETE FROM project_user_mapping WHERE project_id = ? AND user_id = ?', [
      toNum(req.params.id), toNum(req.params.userId),
    ]);
    res.json(ok('User Unassigned'));
  })
);

export default router;
