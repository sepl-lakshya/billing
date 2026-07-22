import { Router } from 'express';
import { query, execute, queryOne } from '../db';
import { asyncHandler, ok, fail, toStr, toNum, nowDateTime } from '../utils/helpers';

const router = Router();

/**
 * List purchase headers. project_id = 0 means a global/shared header.
 * Optional ?projectId= filters to that project's headers plus the global ones.
 */
router.get(
  '/',
  asyncHandler(async (req, res) => {
    const projectId = req.query.projectId ? toNum(req.query.projectId) : undefined;
    let rows;
    if (projectId !== undefined) {
      rows = await query(
        `SELECT ph.*, (SELECT name FROM project WHERE id = ph.project_id) AS project_name
         FROM purchase_header ph
         WHERE ph.is_active = 1 AND (ph.project_id = ? OR ph.project_id = 0)
         ORDER BY ph.name`,
        [projectId]
      );
    } else {
      rows = await query(
        `SELECT ph.*, (SELECT name FROM project WHERE id = ph.project_id) AS project_name
         FROM purchase_header ph
         WHERE ph.is_active = 1
         ORDER BY ph.created_on DESC`
      );
    }
    res.json(ok('Purchase Headers', rows));
  })
);

/** Add purchase header - mirrors add_purchase_header.php. */
router.post(
  '/',
  asyncHandler(async (req, res) => {
    const name = toStr(req.body['purchase-header-name'] ?? req.body.name);
    // project_id defaults to 0 (global) when not supplied.
    const projectId = toNum(req.body['purchase-header-project-id'] ?? req.body.projectId, 0);

    if (name === '') return res.json(fail('Please enter purchase header name'));

    const existing = await queryOne(
      'SELECT id FROM purchase_header WHERE name = ? AND project_id = ?',
      [name, projectId]
    );
    if (existing) return res.json(fail('Header name already exist'));

    await execute(
      'INSERT INTO purchase_header (name, project_id, created_on) VALUES (?,?,?)',
      [name, projectId, nowDateTime()]
    );
    res.json(ok('Purchase Header Added'));
  })
);

/** Soft delete - mirrors delete_purchase_header.php. */
router.delete(
  '/:id',
  asyncHandler(async (req, res) => {
    await execute('UPDATE purchase_header SET is_active = 0 WHERE id = ?', [toNum(req.params.id)]);
    res.json(ok('Purchase Header Deleted'));
  })
);

export default router;
