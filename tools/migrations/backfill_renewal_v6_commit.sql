-- =====================================================================================
-- Renewal Loop v6 — DATA back-fill (COMMIT). Run AFTER Phase 1 (add-only rebuild).
-- Persists the migration. Idempotent guards: only updates rows still unmigrated.
-- v6 §9.2: 4 legacy checkboxes are NOT mirrored (dropped, not migrated).
-- See backfill_renewal_v6_dryrun.sql for the deploy sequence + preview queries.
-- =====================================================================================

START TRANSACTION;

-- 1) pipeline_stage <- stage
UPDATE renewal
SET pipeline_stage = CASE stage
    WHEN 'Renewed - Won' THEN 'Closed'
    WHEN 'Lost' THEN 'Closed'
    ELSE stage
END
WHERE stage IS NOT NULL AND stage <> '' AND (pipeline_stage IS NULL OR pipeline_stage = '');

-- 2) disposition <- stage + lost_reason
UPDATE renewal
SET disposition = CASE
    WHEN stage = 'Renewed - Won' THEN 'renewed'
    WHEN stage = 'Lost' AND lost_reason = 'Price'         THEN 'lost_price'
    WHEN stage = 'Lost' AND lost_reason = 'Coverage'      THEN 'lost_coverage'
    WHEN stage = 'Lost' AND lost_reason = 'Unresponsive'  THEN 'lost_no_response'
    WHEN stage = 'Lost' AND lost_reason = 'Moved carrier' THEN 'rewritten'
    WHEN stage = 'Lost'                                 THEN 'do_not_renew'
    ELSE ''
END
WHERE stage IN ('Renewed - Won','Lost') AND (disposition IS NULL OR disposition = '');

-- 3) Seed RenewalWorksheet rows (only for renewals not yet seeded — idempotent)
INSERT INTO renewal_worksheet
    (id, name, lob_variant, state, completion_type, renewal_id, account_id, created_at, modified_at)
SELECT
    REPLACE(UUID(),'-',''), CONCAT(r.name, ' — Worksheet'),
    CASE r.line_of_business
        WHEN 'Personal Auto'      THEN 'personal_auto'
        WHEN 'Homeowners'         THEN 'homeowners'
        WHEN 'Commercial Auto'    THEN 'commercial_auto'
        WHEN 'General Liability'  THEN 'general_liability'
        WHEN 'Workers Comp'       THEN 'workers_comp'
        ELSE 'default'
    END,
    'not_started', '', r.id, r.account_id, NOW(), NOW()
FROM renewal r
LEFT JOIN renewal_worksheet w ON w.renewal_id = r.id
WHERE r.deleted = 0 AND w.id IS NULL;

COMMIT;

-- Verify
SELECT stage, pipeline_stage, disposition, COUNT(*) n FROM renewal GROUP BY stage, pipeline_stage, disposition ORDER BY stage;
SELECT lob_variant, COUNT(*) n FROM renewal_worksheet GROUP BY lob_variant;
