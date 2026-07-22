import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, getName, nowDateTime } from '../utils/helpers';
import { requireRole, invalidateUserCache } from '../middleware/auth';

const router = Router();

/** Current identity (who am I) — used by the frontend for role-based UI. */
router.get(
  '/me',
  asyncHandler(async (req, res) => {
    res.json(ok('Me', req.user));
  })
);

// All user-management writes are admin-only under AUTH_MODE=entra
// (no-op in AUTH_MODE=none because the system identity is admin).
router.use((req, res, next) => {
  if (req.method === 'GET') return next();
  invalidateUserCache();
  return requireRole('admin')(req, res, next);
});

/**
 * Users management. Authentication was removed, so the Azure fields are gone;
 * we keep the users table for "created_by" / assignment references.
 */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const rows = await query(
      `SELECT id, full_name, email, username, user_type, is_active, created_on
       FROM users WHERE is_active = 1 ORDER BY created_on DESC`
    );
    res.json(ok('Users', rows));
  })
);

/** Add user - simplified from add_user.php (no Azure object id). */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const fullName = toStr(req.body['full-name'] ?? req.body.fullName);
    const email = toStr(req.body['user-email'] ?? req.body.email);
    const userType = toNum(req.body['user-type'] ?? req.body.userType, 2);

    if (fullName === '') return res.json(fail('Please enter full name'));
    if (email === '') return res.json(fail('Please enter E-mail'));

    const existing = await queryOne('SELECT id, is_active FROM users WHERE email = ?', [email]);
    if (existing && existing.is_active === 1) return res.json(fail('User already exist'));

    if (existing) {
      await execute(
        'UPDATE users SET is_active = 1, full_name = ?, user_type = ?, created_on = ? WHERE id = ?',
        [fullName, userType, nowDateTime(), existing.id]
      );
      return res.json(ok('User Added'));
    }

    await execute(
      'INSERT INTO users (hash, email, username, full_name, user_type, is_active, created_on) VALUES (?,?,?,?,?,1,?)',
      [getName(64), email, '', fullName, userType, nowDateTime()]
    );
    res.json(ok('User Added'));
  })
);

/** Soft delete - mirrors delete_user.php. Never disable the system user (1). */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    const id = toNum(req.params.id);
    if (id === 1) return res.json(fail('Cannot delete the system user'));
    await execute('UPDATE users SET is_active = 0 WHERE id = ?', [id]);
    res.json(ok('User Deleted'));
  })
);

export default router;
