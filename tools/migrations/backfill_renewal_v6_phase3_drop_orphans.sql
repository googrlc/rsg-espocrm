-- =====================================================================================
-- Renewal Loop v6 — Phase 3 orphan-column drop. Run AFTER the Phase 3 "drop-old"
-- Rebuild (which removes the legacy field/link columns: stage, lost_reason,
-- expected_commission, expected_commission_currency, last_contact_method,
-- last_contact_date, the 4 checkboxes, and the newPolicy/contact/teams/tasks
-- relationships). EspoCRM Rebuild does NOT touch columns that have no field def,
-- so these 16 orphans survive the rebuild and must be dropped explicitly here.
--
-- Idempotent + safe: only drops columns that still exist (MySQL 8 has no
-- DROP COLUMN IF EXISTS, so this builds the ALTER dynamically from
-- information_schema and runs it via prepared statement — re-running is a no-op).
-- =====================================================================================

SET @cols := NULL;
SELECT GROUP_CONCAT('DROP COLUMN `', COLUMN_NAME, '`' SEPARATOR ', ')
INTO @cols
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'renewal'
  AND COLUMN_NAME IN (
    'business_segment','commission_delta','competitive_quote_count','contact_attempts',
    'decision_date','email_sequence_started','loss_runs_pulled','lost_to_competitor',
    'new_business_commission_rate','new_carrier','outcome','outreach_start_date',
    'premium_change_category','probability','remarketing_reason','retention_risk'
  );

IF @cols IS NOT NULL THEN
  SET @ddl := CONCAT('ALTER TABLE `renewal` ', @cols);
  PREPARE stmt FROM @ddl;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
  SELECT CONCAT('Dropped orphan columns: ', @cols) AS result;
ELSE
  SELECT 'No orphan columns found — already clean (no-op).' AS result;
END IF;

-- Verify: only v6-managed columns + EspoCRM system columns remain.
SELECT COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'renewal'
ORDER BY COLUMN_NAME;
