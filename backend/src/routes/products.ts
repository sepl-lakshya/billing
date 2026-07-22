import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

/** List cloud products with OEM + category names (mirrors manageproductsection.php). */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(`
      SELECT p.id, p.name, p.oem, p.category, p.sub_category, p.is_active, p.created_on,
             (SELECT name FROM oem WHERE id = p.oem) AS oem_name,
             'Cloud' AS category_name,
             (SELECT name FROM cloud_category WHERE value = p.sub_category) AS sub_category_name
      FROM product p
      WHERE p.is_active = 1 AND p.category = 1
      ORDER BY p.created_on DESC
    `);
    res.json(ok('Products', rows));
  })
);

/** Add cloud product - mirrors add_product.php validation. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const name = toStr(req.body['product-name'] ?? req.body.name);
    const oem = toStr(req.body['product-oem'] ?? req.body.oem);
    const cloudCategory = toStr(req.body['cloud-category'] ?? req.body.cloudCategory);

    if (oem === '0' || oem === '') return res.json(fail('Please select product OEM'));
    if (cloudCategory === '0' || cloudCategory === '') return res.json(fail('Please select cloud category'));
    if (name === '') return res.json(fail('Please enter product name'));

    const existing = await queryOne(
      'SELECT id FROM product WHERE name = ? AND category = 1 AND sub_category = ?',
      [name, cloudCategory]
    );
    if (existing) return res.json(fail('Product name already exist'));

    await execute(
      'INSERT INTO product (name, oem, category, sub_category, created_on) VALUES (?,?,1,?,?)',
      [name, oem, cloudCategory, nowDateTime()]
    );
    res.json(ok('Product Added'));
  })
);

/** Soft delete - mirrors delete_product.php (UPDATE is_active = 0). */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    await execute('UPDATE product SET is_active = 0 WHERE id = ?', [id]);
    res.json(ok('Product Deleted'));
  })
);

export default router;
