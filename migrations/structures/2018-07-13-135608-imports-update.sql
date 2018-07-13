ALTER TABLE `persons` ADD COLUMN `persons_imported_on` DATETIME DEFAULT NULL;

ALTER TABLE `persons` ADD COLUMN `persons_imports_id` INT NULL DEFAULT NULL;

ALTER TABLE `invoices` ADD COLUMN `invoices_imports_id` INT NULL DEFAULT NULL;

ALTER TABLE `imports` CHANGE COLUMN `imports_log` `imports_log` MEDIUMTEXT NULL;