ALTER TABLE `invoices` ADD COLUMN `invoice_system_id` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `invoices` ADD COLUMN `invoices_to` DATETIME NULL DEFAULT NULL AFTER `invoices_system_id`;