
 UPDATE Company set business_type = "Einkahlutafélag (ehf)" WHERE business_type = "EinkahlutafÃ©lag (ehf)";
 UPDATE Company set business_type = "Einkahlutafélag (ehf)" WHERE business_type = "Einkahlutafélag (ehf)";
 UPDATE Company set business_type = "Hlutafélag (hf)" WHERE business_type = "HlutafÃ©lag (hf)";
 UPDATE Company set business_type = "Hlutafélag (hf)" WHERE business_type = "Hlutafélag (hf)";
 UPDATE Company set business_type = "Opinbert hlutafélag (ohf)" WHERE business_type = "Opinbert hlutafélag (ohf)";
 UPDATE Company set business_type = "Opinber stofnun" WHERE business_type = "Opinber stofnun";
 UPDATE Company set business_type = "Opinber stofnun" WHERE business_type = "opinber aðili";
 UPDATE Company set business_type = "Sameignafélag (sf)" WHERE business_type = "Sameignafélag (sf)";
 UPDATE Company set business_type = "Samvinnufélag (svf)" WHERE business_type = "Samvinnufélag (sf)";
 UPDATE Company set business_type = "Samlagsfélag (slf)" WHERE business_type = "Samlagsfélag (slf)";
 UPDATE Company set business_type = "Háskóli" WHERE business_type = "Háskóli";
 UPDATE Company set business_type = "Félagasamtök" WHERE business_type = "félagasamtök";
 UPDATE Company set business_type = "Einstaklingur" WHERE business_type = "einstaklingur";

 UPDATE Company set number_of_employees = "200 eða fleiri" WHERE number_of_employees = "200+";

 ALTER TABLE `User`
   ADD oauth_key VARCHAR (255),
   ADD oauth_type varchar (20);

 ALTER TABLE `User`
   ADD gender VARCHAR (20) DEFAULT NULL;

 ALTER TABLE `User`
  ADD get_message TINYINT (1),
  ADD get_notify TINYINT (1);
 
 UPDATE `User` set get_message = 1, get_notify = 1;
 
 ALTER TABLE `User`
   ADD UNIQUE INDEX `unique_oauth_key` (`oauth_key`);
 
-- UPDATE `User`
--   SET `oauth_key`='100000279755387', `oauth_type`='facebook'
--   WHERE `id`='2199';
 
 ALTER TABLE `Event_has_Guest`
   ADD name VARCHAR (255) AFTER event_id;
 

ALTER TABLE `Event`
  ADD capacity INT (4) AFTER address;

ALTER TABLE `Group_has_User`
  ADD `notify` INT (4) AFTER `type`;

UPDATE  `Group_has_User` SET `notify` = 1;

DROP TABLE IF EXISTS `Conference`;
CREATE TABLE IF NOT EXISTS `Conference` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL,
  `body` text,
  `location` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `capacity` int(4) DEFAULT NULL,
  `conference_date` date DEFAULT NULL,
  `conference_time` time DEFAULT NULL,
  `conference_end` time DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `lat` double(11,8) DEFAULT NULL,
  `lng` double(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- INSERT INTO `Conference` (`subject`, `body`, `location`, `address`, `capacity`, `conference_date`, `conference_time`, `conference_end`) VALUES
--  ('Vorráðstefna Stjórvísi',
--  '<p>Nú er komið að vorráðstefnu Stjórnvísi, en hún er jafnan haldin á vorin.  Stundum er hún þó haldin á haustin, en kallast þá haustráðtefna.  Stundum eru tvær ráðstefnur, bæði á vorin og haustin, en það er önnur saga.</p><p>Nú á s.s. að blása til sóknar og halda flotta ráðstefnu.  Endilega skoðaðu dagskrána hérna fyrir neðan.</p>', 'Harpa, Ráðstefnuhús', 'Austurbakka 2', '2000', '2015-03-10', '09:00', '17:00');

DROP TABLE IF EXISTS `Group_has_Conference`;
CREATE TABLE IF NOT EXISTS  `Group_has_Conference` (
  `conference_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `primary` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `fk_Conference_has_Group_Group1` (`group_id`),
  KEY `fk_Conference_has_Group_Conference1` (`conference_id`),
  CONSTRAINT `fk_Conference_has_Group_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Conference_has_Group_Group1` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Conference_has_User`;
CREATE TABLE IF NOT EXISTS  `Conference_has_User` (
  `conference_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `attending` tinyint(3) unsigned DEFAULT NULL,
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`conference_id`,`user_id`),
  KEY `fk_Conference_has_User_User1` (`user_id`),
  KEY `fk_Conference_has_User_Conference1` (`conference_id`),
  CONSTRAINT `fk_Conference_has_User_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_Conference_has_User_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Conference_has_Guest`;
CREATE TABLE IF NOT EXISTS  `Conference_has_Guest` (
  `conference_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`conference_id`,`email`),
  KEY `fk_Conference_has_Guest_Event1` (`conference_id`),
  CONSTRAINT `fk_Conference_has_Guest_Event1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ConferenceGallery`;
CREATE TABLE IF NOT EXISTS  `ConferenceGallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ConferenceGallery_Conference1` (`conference_id`),
  CONSTRAINT `fk_ConferenceGallery_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ConferenceMedia`;
CREATE TABLE IF NOT EXISTS  `ConferenceMedia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `conference_id` int(10) unsigned DEFAULT NULL,
  `description` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ConferenceMedia_Conference1` (`conference_id`),
  CONSTRAINT `fk_ConferenceMedia_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;