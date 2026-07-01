-- =====================================================================================
-- Renewal Loop v6 — DATA back-fill (DRY RUN). MySQL 8 / EspoCRM `renewal` table.
-- Safe to run repeatedly: wraps all DML in BEGIN ... ROLLBACK so nothing persists.
--
-- !!! DEPLOY SEQUENCE (read before running) !!!
-- MySQL DDL (ALTER/CREATE/DROP) is NOT transactional — it CANNOT be rolled back.
-- So this dry-run is DML-ONLY and ASSUMES Phase 1 already ran:
--   Phase 1 (add-only): apply the v6 entityDefs with new fields ADDED but legacy
--     fields/links NOT yet removed, then `Rebuild` in EspoCRM. Creates the new
--     columns (`pipeline_stage`, `disposition`, `ams_*`, `supabase_event_uuid`,
--     the `renewal_worksheet` table) WITHOUT dropping legacy columns yet.
--   Phase 2 (this back-fill): run backfill_renewal_v6_commit.sql to copy
--     stage -> pipeline_stage, synthesize disposition, and seed RenewalWorksheet rows.
--   Phase 3 (drop-old): remove legacy fields/links from entityDefs, Rebuild (drops
--     stage, lost_reason, expected_commission, last_contact_*, the 4 checkboxes, and
--     the newPolicy/contact/teams/tasks relationships), THEN run
--     backfill_renewal_v6_phase3_drop_orphans.sql to drop the 17 orphan columns.
--
-- v6 §9.2: the 4 legacy checkboxes are NOT mirrored to the worksheet (zero non-null
-- in production). They are dropped, not migrated.
-- =====================================================================================

START TRANSACTION;

-- Guard: fail fast if Phase 1 hasn't added the new columns/table yet.
SET @has_ps := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'renewal'
                  AND COLUMN_NAME = 'pipeline_stage');
SET @has_ws := (SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'renewal_worksheet');
IF @has_ps = 0 OR @has_ws = 0 THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Phase 1 not run: pipeline_stage column and/or renewal_worksheet table missing. Apply v6 entityDefs (add-only) + Rebuild first.';
END IF;

-- 1) pipeline_stage <- stage (terminal outcomes collapse to Closed)
UPDATE renewal
SET pipeline_stage = CASE stage
    WHEN 'Renewed - Won' THEN 'Closed'
    WHEN 'Lost' THEN 'Closed'
    ELSE stage
END
WHERE stage IS NOT NULL AND stage <> '' AND (pipeline_stage IS NULL OR pipeline_stage = '');

-- 2) disposition <- stage + lost_reason  (matches Hermes LEGACY_LOST_REASON_TO_DISPOSITION)
UPDATE renewal
SET disposition = CASE
    WHEN stage = 'Renewed - Won' THEN 'renewed'
    WHEN stage = 'Lost' AND lost_reason = 'Price'         THEN 'lost_price'
    WHEN stage = 'Lost' AND lost_reason = 'Coverage'      THEN 'lost_coverage'
    WHEN stage = 'Lost' AND lost_reason = 'Unresponsive'  THEN 'lost_no_response'
    WHEN stage = 'Lost' AND lost_reason = 'Moved carrier' THEN 'rewritten'
    WHEN stage = 'Lost'                                 THEN 'do_not_renew'  -- unmapped/Other
    ELSE ''
END
WHERE stage IN ('Renewed - Won','Lost') AND (disposition IS NULL OR disposition = '');

-- 3) Seed one RenewalWorksheet per Renewal (1:1). lob_variant from line_of_business.
--    No checkbox mirroring (v6 §9.2). Column names follow RenewalWorksheet entityDefs
--    dbName (camelCase) — VERIFY against actual renewal_worksheet columns after Phase 1.
INSERT INTO renewal_worksheet
    (id, name, lob_variant, state, completion_type, renewal_id, account_id, created_at, modified_at)
SELECT
    REPLACE(UUID(),'-',''), CONCAT(name, ' — Worksheet'),
    CASE line_of_business
        WHEN 'Personal Auto'      THEN 'personal_auto'
        WHEN 'Homeowners'         THEN 'homeowners'
        WHEN 'Commercial Auto'    THEN 'commercial_auto'
        WHEN 'General Liability'  THEN 'general_liability'
        WHEN 'Workers Comp'       THEN 'workers_comp'
        ELSE 'default'
    END,
    'not_started', '', id, account_id, NOW(), NOW()
FROM renewal
WHERE deleted = 0;

-- ===== PREVIEW (read-only) =====
SELECT 'pipeline_stage mapping' AS check_name;
SELECT stage, pipeline_stage, COUNT(*) n FROM renewal GROUP BY stage, pipeline_stage ORDER BY stage;
SELECT 'disposition mapping' AS check_name;
SELECT stage, lost_reason, disposition, COUNT(*) n FROM renewal GROUP BY stage, lost_reason, disposition ORDER BY stage;
SELECT 'worksheet seed count' AS check_name;
SELECT lob_variant, COUNT(*) n FROM renewal_worksheet GROUP BY lob_variant ORDER BY lob_variant;

ROLLBACK;
-- Nothing persisted. Inspect the SELECT outputs above. When correct, run backfill_renewal_v6_commit.sql.
