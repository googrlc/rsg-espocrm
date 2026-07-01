-- ============================================================
-- Back-fill Renewal v6 — DRY RUN
-- Usage: mysql espocrm < backfill_renewal_v6_dryrun.sql
-- Wraps everything in a transaction that rolls back at the end.
-- Review the SELECT output before running the commit version.
-- ============================================================

BEGIN;

-- -------------------------------------------------------
-- 1. Rename stage → pipeline_stage
--    Add the new column if it doesn't exist yet (EspoCRM
--    rebuild will create it; this handles pre-rebuild runs).
-- -------------------------------------------------------
ALTER TABLE renewal
    ADD COLUMN IF NOT EXISTS pipeline_stage VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS disposition    VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS renewal_proposed_premium DECIMAL(20,4) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS renewal_proposed_premium_currency VARCHAR(3) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS carrier_premium_change DECIMAL(10,4) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nc_policy_id   VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nc_renewal_id  VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nc_premium     DECIMAL(20,4) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nc_premium_currency VARCHAR(3) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nc_last_sync_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS hermes_task_id VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS hermes_sweep_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS loop_stage     VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS loop_updated_at DATETIME DEFAULT NULL;

-- -------------------------------------------------------
-- 2. Migrate stage values into pipeline_stage + disposition
--    Active pipeline stages copy straight across.
--    Terminal stages (Renewed - Won, Lost) set disposition.
-- -------------------------------------------------------
UPDATE renewal
SET pipeline_stage = CASE stage
        WHEN 'Renewed - Won' THEN 'Negotiating'
        WHEN 'Lost'          THEN 'Identified'
        ELSE stage
    END,
    disposition = CASE stage
        WHEN 'Renewed - Won' THEN 'renewed'
        WHEN 'Lost'          THEN 'lost'
        ELSE NULL
    END
WHERE pipeline_stage IS NULL
  AND stage IS NOT NULL;

-- -------------------------------------------------------
-- 3. Preview — show what will change (SELECT only)
-- -------------------------------------------------------
SELECT
    id,
    stage          AS old_stage,
    pipeline_stage AS new_pipeline_stage,
    disposition    AS new_disposition
FROM renewal
ORDER BY created_at DESC
LIMIT 100;

SELECT
    stage,
    COUNT(*) AS count,
    SUM(CASE WHEN pipeline_stage IS NOT NULL THEN 1 ELSE 0 END) AS migrated
FROM renewal
GROUP BY stage
ORDER BY count DESC;

-- Roll back so nothing is committed.
ROLLBACK;
