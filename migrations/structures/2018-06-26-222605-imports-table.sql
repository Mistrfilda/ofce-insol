CREATE TABLE `imports` (
  `imports_id` int(11) NOT NULL AUTO_INCREMENT,
  `imports_time` datetime NOT NULL,
  `imports_users_id` int(11) NOT NULL,
  `imports_type` varchar(128) NOT NULL,
  `imports_log` text default NULL,
  PRIMARY KEY (`imports_id`),
  KEY `imports_users_id` (`imports_users_id`),
  CONSTRAINT `imports_users_id` FOREIGN KEY (`imports_users_id`) REFERENCES `users` (`users_id`)
);