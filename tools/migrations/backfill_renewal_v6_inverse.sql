-- =====================================================================================
-- Renewal Loop v6 — INVERSE migration (rollback). Run BEFORE reverting the entityDefs
-- to the pre-v6 schema. Restores legacy `stage`/`lost_reason` from the v6 fields, then
-- removes the seeded RenewalWorksheet rows. Assumes Phase 3 (drop-old) has NOT yet run.
-- v6 §9.2: checkboxes were never mirrored, so there is nothing to restore for them.
-- =====================================================================================

START TRANSACTION;

-- Restore stage <- pipeline_stage + disposition (inverse of the forward map)
UPDATE renewal
SET stage = CASE
    WHEN disposition IN ('renewed','rewritten') THEN 'Renewed - Won'
    WHEN disposition IS NOT NULL AND disposition <> '' THEN 'Lost'
    WHEN pipeline_stage = 'Closed' THEN 'Renewed - Won'  -- closed w/o disposition: assume won
    ELSE pipeline_stage
END
WHERE stage IS NULL OR stage = '';

-- Restore lost_reason <- disposition (inverse map)
UPDATE renewal
SET lost_reason = CASE disposition
    WHEN 'lost_price'        THEN 'Price'
    WHEN 'lost_coverage'     THEN 'Coverage'
    WHEN 'lost_no_response'  THEN 'Unresponsive'
    WHEN 'rewritten'         THEN 'Moved carrier'
    WHEN 'do_not_renew'      THEN 'Other'
    ELSE NULL
END
WHERE stage = 'Lost' AND (lost_reason IS NULL OR lost_reason = '');

-- Remove seeded worksheet rows (preserves manually-created worksheets made after
-- the back-fill: keyed on state='not_started' AND completion_type='')
DELETE w FROM renewal_worksheet w
WHERE w.state = 'not_started' AND (w.completion_type IS NULL OR w.completion_type = '');

COMMIT;

SELECT stage, lost_reason, COUNT(*) n FROM renewal GROUP BY stage, lost_reason ORDER BY stage;
SELECT COUNT(*) AS worksheets_remaining FROM renewal_worksheet;
