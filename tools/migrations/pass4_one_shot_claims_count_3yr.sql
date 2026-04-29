-- Unblock Account GET / email import: ensure column `claims_count_3yr` exists on `account`.
-- Run after: USE your_espocrm_database;
-- MySQL 8.0.2+ (uses RENAME COLUMN when possible).

SET @dbname = DATABASE();

SELECT COUNT(*) INTO @old_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'account' AND COLUMN_NAME = 'claimsCount3yr';

SELECT COUNT(*) INTO @new_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'account' AND COLUMN_NAME = 'claims_count_3yr';

-- Rename camelCase -> snake_case if needed
SET @sql = IF(
    @old_exists > 0 AND @new_exists = 0,
    'ALTER TABLE `account` RENAME COLUMN `claimsCount3yr` TO `claims_count_3yr`',
    IF(
        @old_exists = 0 AND @new_exists = 0,
        'ALTER TABLE `account` ADD COLUMN `claims_count_3yr` INT NULL',
        'SELECT 1'
    )
);

PREPARE esp_stmt FROM @sql;
EXECUTE esp_stmt;
DEALLOCATE PREPARE esp_stmt;
