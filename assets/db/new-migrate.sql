-- CREATE TABLE IF NOT EXISTS `Email`(
--   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `subject` varchar(255) NOT NULL,
--   `body` TEXT,
--   `hash` CHAR(32) NOT NULL,
--   `user_hash` CHAR(32) NOT NULL,
--   `type` varchar(20) NOT NULL,
--   `entity_id` int(10) unsigned DEFAULT NULL,
--   `params` varchar(255) DEFAULT NULL,
--   `created` datetime NOT NULL,
--   `modified` datetime NOT NULL,
--   `touched` tinyint(1) NOT NULL DEFAULT 0,
--   `agent` varchar(255),
--   `headers` TEXT,
--   PRIMARY KEY (`id`),
--   KEY `k_email_hash` (`hash`),
--   KEY `k_email_user_hash` (`user_hash`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `Group` ADD `avatar` varchar(255) DEFAULT NULL AFTER `name_short`;
ALTER TABLE `Group` ADD `summary` TEXT DEFAULT NULL AFTER `avatar`;
ALTER TABLE `Group` ADD `body` TEXT DEFAULT NULL AFTER `summary`;

update `Group` set summary = description;
update `Group` set body = CONCAT_WS(' ', objective, what_is, how_operates, for_whom);

ALTER TABLE `Group` DROP COLUMN `description`;
ALTER TABLE `Group` DROP COLUMN `objective`;
ALTER TABLE `Group` DROP COLUMN `what_is`;
ALTER TABLE `Group` DROP COLUMN `how_operates`;
ALTER TABLE `Group` DROP COLUMN `for_whom`;
