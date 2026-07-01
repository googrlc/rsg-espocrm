-- ============================================================
-- Back-fill Renewal v6 — COMMIT
-- Usage: mysql espocrm < backfill_renewal_v6_commit.sql
-- ONLY run after reviewing the dry-run output.
-- ============================================================

BEGIN;

-- -------------------------------------------------------
-- 1. Add new columns (idempotent — IF NOT EXISTS)
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
-- 2. Migrate stage → pipeline_stage + disposition
--    Idempotent: only updates rows not yet migrated.
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
-- 3. Verify counts after migration
-- -------------------------------------------------------
SELECT 'Renewal migration counts' AS check_label;
SELECT
    stage,
    pipeline_stage,
    disposition,
    COUNT(*) AS cnt
FROM renewal
GROUP BY stage, pipeline_stage, disposition
ORDER BY stage, pipeline_stage;

SELECT
    SUM(CASE WHEN pipeline_stage IS NULL THEN 1 ELSE 0 END) AS unmigrated_count,
    COUNT(*) AS total_renewals
FROM renewal;

COMMIT;

-- -------------------------------------------------------
-- Inverse / rollback companion — run this to undo
-- -------------------------------------------------------
-- BEGIN;
-- UPDATE renewal
-- SET pipeline_stage = NULL,
--     disposition = NULL
-- WHERE pipeline_stage IS NOT NULL
--    OR disposition IS NOT NULL;
-- COMMIT;
