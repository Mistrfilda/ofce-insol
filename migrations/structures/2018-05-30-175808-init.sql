CREATE TABLE `invoices` (
  `invoices_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `invoices_persons_id` int(11) NOT NULL,
  `invoices_from` datetime NOT NULL,
  `invoices_type` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`invoices_id`),
  KEY `invoices_persons_id` (`invoices_persons_id`),
  CONSTRAINT `invoices_persons_id` FOREIGN KEY (`invoices_persons_id`) REFERENCES `persons` (`persons_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `persons` (
  `persons_id` int(11) NOT NULL AUTO_INCREMENT,
  `persons_ag_id` int(10) unsigned DEFAULT NULL,
  `persons_birth_id` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `persons_company_id` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `persons_year` int(11) NOT NULL DEFAULT '0',
  `persons_firstname` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `persons_lastname` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `persons_actual_invoice_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`persons_id`),
  KEY `persons_birth_id` (`persons_birth_id`) USING BTREE,
  KEY `persons_company_id` (`persons_company_id`) USING BTREE,
  KEY `persons_actual_invoice_id` (`persons_actual_invoice_id`),
  CONSTRAINT `persons_actual_invoice_id` FOREIGN KEY (`persons_actual_invoice_id`) REFERENCES `invoices` (`invoices_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `users` (
  `users_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_login` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `users_password` varchar(64) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;