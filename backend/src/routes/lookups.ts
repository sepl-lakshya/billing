import { Router } from 'express';
import { query } from '../db';
import { asyncHandler, ok } from '../utils/helpers';

const router = Router();

/**
 * Returns every lookup / reference table in one payload so the frontend can
 * populate dropdowns without many round-trips. Mirrors all the "select * from
 * <lookup>" queries scattered across the legacy modals.
 */
router.get(
  '/',
  asyncHandler(async (_req, res) => {
    const [
      months,
      years,
      states,
      gstSlabs,
      subscriptionTerms,
      billingTerms,
      cloudCategories,
      adjustmentTypes,
      creditNoteTypes,
      debitNoteTypes,
      unitMeasures,
      discoveryStatuses,
      contractYears,
      contractMonths,
      billingProgress,
    ] = await Promise.all([
      query('SELECT value, name FROM month ORDER BY value'),
      query('SELECT value, name FROM year ORDER BY value'),
      query('SELECT value, name FROM states ORDER BY name'),
      query('SELECT id, name, percentage FROM gst_slab ORDER BY percentage'),
      query('SELECT id, name, months FROM subscription_term ORDER BY id'),
      query('SELECT id, name, months FROM billing_term ORDER BY id'),
      query('SELECT value, name FROM cloud_category ORDER BY name'),
      query('SELECT value, name FROM adjustment_type ORDER BY value'),
      query('SELECT value, name FROM credit_note_type ORDER BY value'),
      query('SELECT value, name FROM debit_note_type ORDER BY value'),
      query('SELECT value, name FROM unit_measure ORDER BY value'),
      query('SELECT value, name FROM project_item_discovery ORDER BY value'),
      query('SELECT value, name FROM contract_year ORDER BY value'),
      query('SELECT value, name FROM contract_month ORDER BY value'),
      query('SELECT value, name FROM billing_progress ORDER BY value'),
    ]);

    res.json(
      ok('Lookups', {
        months,
        years,
        states,
        gstSlabs,
        subscriptionTerms,
        billingTerms,
        cloudCategories,
        adjustmentTypes,
        creditNoteTypes,
        debitNoteTypes,
        unitMeasures,
        discoveryStatuses,
        contractYears,
        contractMonths,
        billingProgress,
      })
    );
  })
);

export default router;
