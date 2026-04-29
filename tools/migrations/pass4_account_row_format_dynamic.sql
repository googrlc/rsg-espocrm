-- Optional: reduce InnoDB "Row size too large" risk on wide `account` rows (MySQL 8 / InnoDB).
-- Run only after backup; verify with SHOW TABLE STATUS LIKE 'account';
-- If this fails, work with a DBA (convert large VARCHAR fields to TEXT, etc.).

ALTER TABLE `account` ROW_FORMAT=DYNAMIC;
