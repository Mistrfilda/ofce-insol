CREATE TABLE `log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `log_message` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `log_users_id` int(11) NOT NULL,
  `log_time` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_users_id` (`log_users_id`),
  CONSTRAINT `log_users_id` FOREIGN KEY (`log_users_id`) REFERENCES `users` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;