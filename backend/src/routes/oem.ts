import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

/** List OEMs (mirrors manageoemsection.php). */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(
      'SELECT id, name, is_active, created_on FROM oem WHERE is_active = 1 ORDER BY created_on DESC'
    );
    res.json(ok('OEM', rows));
  })
);

/** Add OEM - mirrors add_oem.php. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const name = toStr(req.body['oem-name'] ?? req.body.name);
    if (name === '') return res.json(fail('Please enter OEM name'));

    const existing = await queryOne('SELECT id FROM oem WHERE name = ?', [name]);
    if (existing) return res.json(fail('OEM name already exist'));

    await execute('INSERT INTO oem (name, created_on) VALUES (?,?)', [name, nowDateTime()]);
    res.json(ok('OEM Added'));
  })
);

/** Soft delete - mirrors delete_oem.php. */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    await execute('UPDATE oem SET is_active = 0 WHERE id = ?', [toNum(req.params.id)]);
    res.json(ok('OEM Deleted'));
  })
);

export default router;
