CREATE TABLE IF NOT EXISTS `Email`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `body` TEXT,
  `hash` CHAR(32) NOT NULL,
  `user_hash` CHAR(32) NOT NULL,
  `type` varchar(20) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `touched` tinyint(1) NOT NULL DEFAULT 0,
  `agent` varchar(255),
  `headers` TEXT,
  PRIMARY KEY (`id`),
  KEY `k_email_hash` (`hash`),
  KEY `k_email_user_hash` (`user_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- update `User` set get_notify = 1;
-- ALTER TABLE `User` MODIFY COLUMN `get_notify` tinyint(1) NOT NULL DEFAULT 1;


update `User` set get_message = 1;
ALTER TABLE `User` MODIFY COLUMN `get_message` tinyint(1) NOT NULL DEFAULT 1;