
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
  ADD get_message TINYINT (1),
  ADD get_notify TINYINT (1);
 
 UPDATE `User` set get_message = 1, get_notify = 1;
 
 ALTER TABLE `User`
   ADD UNIQUE INDEX `unique_oauth_key` (`oauth_key`);
 
 UPDATE `User`
   SET `oauth_key`='100000279755387', `oauth_type`='facebook'
   WHERE `id`='2199';
 
 ALTER TABLE `Event_has_Guest`
   ADD name VARCHAR (255) AFTER event_id;
 

ALTER TABLE `Event`
  ADD capacity INT (4) AFTER address;

ALTER TABLE `Group_has_User`
  ADD `notify` INT (4) AFTER `type`;

UPDATE  `Group_has_User` SET `notify` = 1;

CREATE TABLE `Conference` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL,
  `body` text,
  `location` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `capacity` int(4) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_end` time DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `lat` double(11,8) DEFAULT NULL,
  `lng` double(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
