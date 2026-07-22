import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

/** List distributors (mirrors managedistributorssection.php). */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(
      'SELECT id, name, is_active, created_on FROM distributor WHERE is_active = 1 ORDER BY created_on DESC'
    );
    res.json(ok('Distributors', rows));
  })
);

/** Add distributor - mirrors add_distributor.php. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const name = toStr(req.body['distributor-name'] ?? req.body.name);
    if (name === '') return res.json(fail('Please enter distributor name'));

    const existing = await queryOne('SELECT id FROM distributor WHERE name = ?', [name]);
    if (existing) return res.json(fail('Distributor name already exist'));

    await execute('INSERT INTO distributor (name, created_on) VALUES (?,?)', [name, nowDateTime()]);
    res.json(ok('Distributor Added'));
  })
);

/** Soft delete - mirrors delete_distributor.php. */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    await execute('UPDATE distributor SET is_active = 0 WHERE id = ?', [toNum(req.params.id)]);
    res.json(ok('Distributor Deleted'));
  })
);

export default router;
