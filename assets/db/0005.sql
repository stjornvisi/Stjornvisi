# 0005.sql Adds tables for disallowed dates for events

DROP TABLE IF EXISTS `EventDatepicker`;
/*!50001 DROP VIEW IF EXISTS `EventDatepicker`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
  /*!50001 CREATE TABLE `EventDatepicker` (
  `timestamp` INT UNSIGNED NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;