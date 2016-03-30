-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: stjornvisi
-- ------------------------------------------------------
-- Server version	5.1.73

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Anaegjuvogin`
--

DROP TABLE IF EXISTS `Anaegjuvogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Anaegjuvogin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `body` text,
  `created` datetime DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Anaegjuvogin`
--



--
-- Table structure for table `Article`
--

DROP TABLE IF EXISTS `Article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `body` text,
  `summary` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `published` datetime DEFAULT NULL COMMENT '	',
  `venue` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Article`
--



--
-- Temporary table structure for view `ArticleEntry`
--

DROP TABLE IF EXISTS `ArticleEntry`;
/*!50001 DROP VIEW IF EXISTS `ArticleEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `ArticleEntry` (
 `id` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `body` tinyint NOT NULL,
  `summary` tinyint NOT NULL,
  `created` tinyint NOT NULL,
  `published` tinyint NOT NULL,
  `venue` tinyint NOT NULL,
  `author` tinyint NOT NULL,
  `author_avatar` tinyint NOT NULL,
  `author_info` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ArticleTag`
--

DROP TABLE IF EXISTS `ArticleTag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ArticleTag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ArticleTag`
--



--
-- Table structure for table `Article_has_ArticleTag`
--

DROP TABLE IF EXISTS `Article_has_ArticleTag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Article_has_ArticleTag` (
  `article_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`),
  KEY `fk_Article_has_ArticleTag_ArticleTag1` (`tag_id`),
  KEY `fk_Article_has_ArticleTag_Article1` (`article_id`),
  CONSTRAINT `fk_Article_has_ArticleTag_Article1` FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Article_has_ArticleTag_ArticleTag1` FOREIGN KEY (`tag_id`) REFERENCES `ArticleTag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Article_has_ArticleTag`
--



--
-- Table structure for table `Author`
--

DROP TABLE IF EXISTS `Author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Author` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `info` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Author`
--



--
-- Table structure for table `Author_has_Article`
--

DROP TABLE IF EXISTS `Author_has_Article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Author_has_Article` (
  `author_id` int(10) unsigned NOT NULL,
  `article_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`author_id`,`article_id`),
  KEY `fk_Author_has_Article_Article1` (`article_id`),
  KEY `fk_Author_has_Article_Author1` (`author_id`),
  CONSTRAINT `fk_Author_has_Article_Article1` FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Author_has_Article_Author1` FOREIGN KEY (`author_id`) REFERENCES `Author` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Author_has_Article`
--



--
-- Table structure for table `BoardMember`
--

DROP TABLE IF EXISTS `BoardMember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BoardMember` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `info` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BoardMember`
--



--
-- Temporary table structure for view `BoardMemberEntry`
--

DROP TABLE IF EXISTS `BoardMemberEntry`;
/*!50001 DROP VIEW IF EXISTS `BoardMemberEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `BoardMemberEntry` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `company` tinyint NOT NULL,
  `avatar` tinyint NOT NULL,
  `info` tinyint NOT NULL,
  `boardmember_id` tinyint NOT NULL,
  `term` tinyint NOT NULL,
  `is_chairman` tinyint NOT NULL,
  `is_reserve` tinyint NOT NULL,
  `is_manager` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `BoardMemberTerm`
--

DROP TABLE IF EXISTS `BoardMemberTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BoardMemberTerm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `boardmember_id` int(10) unsigned NOT NULL,
  `term` varchar(45) DEFAULT NULL,
  `is_chairman` tinyint(4) DEFAULT NULL,
  `is_reserve` tinyint(4) DEFAULT NULL,
  `is_manager` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `BoardMemberHasTerms` (`boardmember_id`),
  CONSTRAINT `BoardMemberHasTerms` FOREIGN KEY (`boardmember_id`) REFERENCES `BoardMember` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BoardMemberTerm`
--



--
-- Table structure for table `Company`
--

DROP TABLE IF EXISTS `Company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `ssn` varchar(10) NOT NULL,
  `address` varchar(60) DEFAULT NULL,
  `zip` varchar(45) DEFAULT NULL,
  `website` varchar(60) DEFAULT NULL,
  `number_of_employees` varchar(30) DEFAULT NULL,
  `business_type` varchar(30) DEFAULT NULL,
  `safe_name` varchar(60) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  UNIQUE KEY `safename_UNIQUE` (`safe_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Company`
--



--
-- Temporary table structure for view `CompanyEntryWithUserCount`
--

DROP TABLE IF EXISTS `CompanyEntryWithUserCount`;
/*!50001 DROP VIEW IF EXISTS `CompanyEntryWithUserCount`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `CompanyEntryWithUserCount` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `ssn` tinyint NOT NULL,
  `address` tinyint NOT NULL,
  `zip` tinyint NOT NULL,
  `number_of_employees` tinyint NOT NULL,
  `business_type` tinyint NOT NULL,
  `safe_name` tinyint NOT NULL,
  `no_of_users` tinyint NOT NULL,
  `created` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `CompanyUser`
--

DROP TABLE IF EXISTS `CompanyUser`;
/*!50001 DROP VIEW IF EXISTS `CompanyUser`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `CompanyUser` (
 `user_id` tinyint NOT NULL,
  `company_id` tinyint NOT NULL,
  `key_user` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `title` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Company_has_User`
--

DROP TABLE IF EXISTS `Company_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Company_has_User` (
  `user_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL DEFAULT '0',
  `key_user` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`user_id`,`company_id`),
  KEY `fk_Company_has_User_User1` (`user_id`),
  KEY `fk_Company_has_User_Company1` (`company_id`),
  CONSTRAINT `fk_Company_has_User_Company1` FOREIGN KEY (`company_id`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_Company_has_User_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Company_has_User`
--



--
-- Table structure for table `Conference`
--

DROP TABLE IF EXISTS `Conference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Conference` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Conference`
--



--
-- Table structure for table `ConferenceGallery`
--

DROP TABLE IF EXISTS `ConferenceGallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ConferenceGallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ConferenceGallery_Conference1` (`conference_id`),
  CONSTRAINT `fk_ConferenceGallery_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ConferenceGallery`
--



--
-- Table structure for table `ConferenceMedia`
--

DROP TABLE IF EXISTS `ConferenceMedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ConferenceMedia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `conference_id` int(10) unsigned DEFAULT NULL,
  `description` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ConferenceMedia_Conference1` (`conference_id`),
  CONSTRAINT `fk_ConferenceMedia_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ConferenceMedia`
--



--
-- Table structure for table `Conference_has_Guest`
--

DROP TABLE IF EXISTS `Conference_has_Guest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Conference_has_Guest` (
  `conference_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`conference_id`,`email`),
  KEY `fk_Conference_has_Guest_Event1` (`conference_id`),
  CONSTRAINT `fk_Conference_has_Guest_Event1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Conference_has_Guest`
--



--
-- Table structure for table `Conference_has_User`
--

DROP TABLE IF EXISTS `Conference_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Conference_has_User` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Conference_has_User`
--



--
-- Table structure for table `Event`
--

DROP TABLE IF EXISTS `Event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Event` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Event`
--



--
-- Temporary table structure for view `EventEntry`
--

DROP TABLE IF EXISTS `EventEntry`;
/*!50001 DROP VIEW IF EXISTS `EventEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `EventEntry` (
 `id` tinyint NOT NULL,
  `subject` tinyint NOT NULL,
  `body` tinyint NOT NULL,
  `location` tinyint NOT NULL,
  `address` tinyint NOT NULL,
  `event_date` tinyint NOT NULL,
  `event_time` tinyint NOT NULL,
  `event_end` tinyint NOT NULL,
  `avatar` tinyint NOT NULL,
  `lat` tinyint NOT NULL,
  `lng` tinyint NOT NULL,
  `group_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `EventGallery`
--

DROP TABLE IF EXISTS `EventGallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EventGallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventGallery_Event1` (`event_id`),
  CONSTRAINT `fk_EventGallery_Event1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EventGallery`
--



--
-- Table structure for table `EventMedia`
--

DROP TABLE IF EXISTS `EventMedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EventMedia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `event_id` int(10) unsigned DEFAULT NULL,
  `description` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventMedia_Event1` (`event_id`),
  CONSTRAINT `fk_EventMedia_Event1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EventMedia`
--



--
-- Temporary table structure for view `EventMediaUserEntry`
--

DROP TABLE IF EXISTS `EventMediaUserEntry`;
/*!50001 DROP VIEW IF EXISTS `EventMediaUserEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `EventMediaUserEntry` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `event_id` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `created` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `attending` tinyint NOT NULL,
  `register_time` tinyint NOT NULL,
  `subject` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `EventUserEntry`
--

DROP TABLE IF EXISTS `EventUserEntry`;
/*!50001 DROP VIEW IF EXISTS `EventUserEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `EventUserEntry` (
 `name` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `event_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `attending` tinyint NOT NULL,
  `register_time` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Event_has_Guest`
--

DROP TABLE IF EXISTS `Event_has_Guest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Event_has_Guest` (
  `event_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`,`email`),
  KEY `fk_Event_has_Guest_Event1` (`event_id`),
  CONSTRAINT `fk_Event_has_Guest_Event1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Event_has_Guest`
--



--
-- Table structure for table `Event_has_User`
--

DROP TABLE IF EXISTS `Event_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Event_has_User` (
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `attending` tinyint(3) unsigned DEFAULT NULL,
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`,`user_id`),
  KEY `fk_Event_has_User_User1` (`user_id`),
  KEY `fk_Event_has_User_Event1` (`event_id`),
  CONSTRAINT `fk_Event_has_User_Event1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_Event_has_User_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Event_has_User`
--



--
-- Table structure for table `Group`
--

DROP TABLE IF EXISTS `Group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `name_short` varchar(45) NOT NULL,
  `description` text,
  `objective` text,
  `what_is` text,
  `how_operates` text,
  `for_whom` text,
  `url` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group`
--



--
-- Temporary table structure for view `GroupUser`
--

DROP TABLE IF EXISTS `GroupUser`;
/*!50001 DROP VIEW IF EXISTS `GroupUser`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `GroupUser` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `passwd` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `created_date` tinyint NOT NULL,
  `modified_date` tinyint NOT NULL,
  `frequency` tinyint NOT NULL,
  `is_admin` tinyint NOT NULL,
  `company_id` tinyint NOT NULL,
  `company_name` tinyint NOT NULL,
  `group_id` tinyint NOT NULL,
  `type` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `GroupUserWithGroupNames`
--

DROP TABLE IF EXISTS `GroupUserWithGroupNames`;
/*!50001 DROP VIEW IF EXISTS `GroupUserWithGroupNames`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `GroupUserWithGroupNames` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `passwd` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `created_date` tinyint NOT NULL,
  `modified_date` tinyint NOT NULL,
  `frequency` tinyint NOT NULL,
  `is_admin` tinyint NOT NULL,
  `company_id` tinyint NOT NULL,
  `company_name` tinyint NOT NULL,
  `group_id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `group_name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Group_has_Conference`
--

DROP TABLE IF EXISTS `Group_has_Conference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_Conference` (
  `conference_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `primary` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `fk_Conference_has_Group_Group1` (`group_id`),
  KEY `fk_Conference_has_Group_Conference1` (`conference_id`),
  CONSTRAINT `fk_Conference_has_Group_Conference1` FOREIGN KEY (`conference_id`) REFERENCES `Conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Conference_has_Group_Group1` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group_has_Conference`
--



--
-- Table structure for table `Group_has_Event`
--

DROP TABLE IF EXISTS `Group_has_Event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_Event` (
  `event_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `primary` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `fk_Event_has_Group_Group1` (`group_id`),
  KEY `fk_Event_has_Group_Event1` (`event_id`),
  CONSTRAINT `fk_Event_has_Group_Event1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Event_has_Group_Group1` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group_has_Event`
--



--
-- Table structure for table `Group_has_User`
--

DROP TABLE IF EXISTS `Group_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_User` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `notify` int(4) DEFAULT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `fk_Group_has_User_User1` (`user_id`),
  CONSTRAINT `fk_Group_has_User_Group` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Group_has_User_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group_has_User`
--



--
-- Table structure for table `JobTitle`
--

DROP TABLE IF EXISTS `JobTitle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `JobTitle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JobTitle`
--



--
-- Table structure for table `Log`
--

DROP TABLE IF EXISTS `Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT NULL,
  `message` text,
  `priority` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Log`
--



--
-- Table structure for table `MeetingMinute`
--

DROP TABLE IF EXISTS `MeetingMinute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MeetingMinute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `summary` text,
  `body` text,
  `created` datetime NOT NULL,
  `time` datetime NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `author_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_MeetingMinute_Group1` (`group_id`),
  KEY `fk_MeetingMinute_User1` (`author_id`),
  CONSTRAINT `fk_MeetingMinute_Group1` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_MeetingMinute_User1` FOREIGN KEY (`author_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MeetingMinute`
--



--
-- Temporary table structure for view `MeetingMinuteEntry`
--

DROP TABLE IF EXISTS `MeetingMinuteEntry`;
/*!50001 DROP VIEW IF EXISTS `MeetingMinuteEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `MeetingMinuteEntry` (
 `id` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `summary` tinyint NOT NULL,
  `body` tinyint NOT NULL,
  `created` tinyint NOT NULL,
  `time` tinyint NOT NULL,
  `group_id` tinyint NOT NULL,
  `author_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `name_short` tinyint NOT NULL,
  `url` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `News`
--

DROP TABLE IF EXISTS `News`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `News` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `avatar` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_News_Group1` (`group_id`),
  KEY `fk_News_User1` (`user_id`),
  CONSTRAINT `fk_News_Group1` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_News_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `News`
--



--
-- Temporary table structure for view `NewsEntry`
--

DROP TABLE IF EXISTS `NewsEntry`;
/*!50001 DROP VIEW IF EXISTS `NewsEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `NewsEntry` (
 `id` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `body` tinyint NOT NULL,
  `avatar` tinyint NOT NULL,
  `created_date` tinyint NOT NULL,
  `modified_date` tinyint NOT NULL,
  `group_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `name_short` tinyint NOT NULL,
  `url` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Page`
--

DROP TABLE IF EXISTS `Page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `body` text,
  `created` datetime NOT NULL,
  `affected` datetime NOT NULL,
  `editor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`label`),
  KEY `fk_table1_User1` (`editor_id`),
  CONSTRAINT `fk_table1_User1` FOREIGN KEY (`editor_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Page`
--



--
-- Table structure for table `Semposium`
--

DROP TABLE IF EXISTS `Semposium`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Semposium` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `ssn` varchar(11) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `payee` varchar(100) DEFAULT NULL,
  `payeessn` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Semposium`
--



--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `passwd` char(32) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `frequency` int(10) unsigned NOT NULL DEFAULT '0',
  `is_admin` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oauth_key` varchar(255) DEFAULT NULL,
  `oauth_type` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `get_message` tinyint(1) NOT NULL DEFAULT '1',
  `get_notify` tinyint(1) NOT NULL DEFAULT '1',
  `email_event_upcoming` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_global_all` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_group_manager` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_group_all` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_event_all` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_event_participant` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_global_manager` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `email_global_chairman` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_oauth_key` (`oauth_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--



--
-- Temporary table structure for view `UserEntry`
--

DROP TABLE IF EXISTS `UserEntry`;
/*!50001 DROP VIEW IF EXISTS `UserEntry`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `UserEntry` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `passwd` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `created_date` tinyint NOT NULL,
  `modified_date` tinyint NOT NULL,
  `frequency` tinyint NOT NULL,
  `company_id` tinyint NOT NULL,
  `is_admin` tinyint NOT NULL,
  `company_name` tinyint NOT NULL,
  `safe_name` tinyint NOT NULL,
  `key_user` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(10) unsigned NOT NULL,
  `handle` char(32) DEFAULT NULL,
  `body` varchar(8192) NOT NULL,
  `md5` char(32) NOT NULL,
  `timeout` decimal(14,4) unsigned DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `message_handle` (`handle`),
  KEY `message_queueid` (`queue_id`),
  CONSTRAINT `message_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`queue_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--



--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue_name` varchar(100) NOT NULL,
  `timeout` smallint(5) unsigned NOT NULL DEFAULT '30',
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

--
-- Table structure for table `Email`
--

DROP TABLE IF EXISTS `Email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `body` text,
  `hash` char(32) NOT NULL,
  `user_hash` char(32) NOT NULL,
  `type` varchar(20) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `touched` tinyint(1) NOT NULL DEFAULT '0',
  `agent` varchar(255) DEFAULT NULL,
  `headers` text,
  PRIMARY KEY (`id`),
  KEY `k_email_hash` (`hash`),
  KEY `k_email_user_hash` (`user_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Email`
--


--
-- Final view structure for view `ArticleEntry`
--

/*!50001 DROP TABLE IF EXISTS `ArticleEntry`*/;
/*!50001 DROP VIEW IF EXISTS `ArticleEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `ArticleEntry` AS select `art`.`id` AS `id`,`art`.`title` AS `title`,`art`.`body` AS `body`,`art`.`summary` AS `summary`,`art`.`created` AS `created`,`art`.`published` AS `published`,`art`.`venue` AS `venue`,`auth`.`name` AS `author`,`auth`.`avatar` AS `author_avatar`,`auth`.`info` AS `author_info` from ((`Article` `art` join `Author_has_Article` `aha` on((`art`.`id` = `aha`.`article_id`))) join `Author` `auth` on((`aha`.`author_id` = `auth`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `BoardMemberEntry`
--

/*!50001 DROP TABLE IF EXISTS `BoardMemberEntry`*/;
/*!50001 DROP VIEW IF EXISTS `BoardMemberEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE VIEW `BoardMemberEntry` AS select `bm`.`id` AS `id`,`bm`.`name` AS `name`,`bm`.`email` AS `email`,`bm`.`company` AS `company`,`bm`.`avatar` AS `avatar`,`bm`.`info` AS `info`,`bmt`.`boardmember_id` AS `boardmember_id`,`bmt`.`term` AS `term`,`bmt`.`is_chairman` AS `is_chairman`,`bmt`.`is_reserve` AS `is_reserve`,`bmt`.`is_manager` AS `is_manager` from (`BoardMember` `bm` join `BoardMemberTerm` `bmt` on((`bm`.`id` = `bmt`.`boardmember_id`))) order by `bmt`.`term` desc,`bmt`.`is_chairman` desc,`bmt`.`is_manager` desc,`bmt`.`is_reserve`,`bm`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `CompanyEntryWithUserCount`
--

/*!50001 DROP TABLE IF EXISTS `CompanyEntryWithUserCount`*/;
/*!50001 DROP VIEW IF EXISTS `CompanyEntryWithUserCount`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `CompanyEntryWithUserCount` AS select `c`.`id` AS `id`,`c`.`name` AS `name`,`c`.`ssn` AS `ssn`,`c`.`address` AS `address`,`c`.`zip` AS `zip`,`c`.`number_of_employees` AS `number_of_employees`,`c`.`business_type` AS `business_type`,`c`.`safe_name` AS `safe_name`,(select count(`Company_has_User`.`company_id`) AS `count(company_id)` from `Company_has_User` where (`Company_has_User`.`company_id` = `c`.`id`)) AS `no_of_users`,`c`.`created` AS `created` from `Company` `c` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `CompanyUser`
--

/*!50001 DROP TABLE IF EXISTS `CompanyUser`*/;
/*!50001 DROP VIEW IF EXISTS `CompanyUser`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `CompanyUser` AS select `CU`.`user_id` AS `user_id`,`CU`.`company_id` AS `company_id`,`CU`.`key_user` AS `key_user`,`U`.`name` AS `name`,`U`.`email` AS `email`,`U`.`title` AS `title` from (`Company_has_User` `CU` join `User` `U` on((`CU`.`user_id` = `U`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `EventEntry`
--

/*!50001 DROP TABLE IF EXISTS `EventEntry`*/;
/*!50001 DROP VIEW IF EXISTS `EventEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `EventEntry` AS select `E`.`id` AS `id`,`E`.`subject` AS `subject`,`E`.`body` AS `body`,`E`.`location` AS `location`,`E`.`address` AS `address`,`E`.`event_date` AS `event_date`,`E`.`event_time` AS `event_time`,`E`.`event_end` AS `event_end`,`E`.`avatar` AS `avatar`,`E`.`lat` AS `lat`,`E`.`lng` AS `lng`,`G`.`group_id` AS `group_id` from (`Event` `E` join `Group_has_Event` `G` on((`E`.`id` = `G`.`event_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `EventMediaUserEntry`
--

/*!50001 DROP TABLE IF EXISTS `EventMediaUserEntry`*/;
/*!50001 DROP VIEW IF EXISTS `EventMediaUserEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `EventMediaUserEntry` AS select `M`.`id` AS `id`,`M`.`name` AS `name`,`M`.`event_id` AS `event_id`,`M`.`description` AS `description`,`M`.`created` AS `created`,`EU`.`user_id` AS `user_id`,`EU`.`attending` AS `attending`,`EU`.`register_time` AS `register_time`,`E`.`subject` AS `subject` from ((`Event_has_User` `EU` join `EventMedia` `M` on((`EU`.`event_id` = `M`.`event_id`))) join `Event` `E` on((`EU`.`event_id` = `E`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `EventUserEntry`
--

/*!50001 DROP TABLE IF EXISTS `EventUserEntry`*/;
/*!50001 DROP VIEW IF EXISTS `EventUserEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `EventUserEntry` AS select `U`.`name` AS `name`,`U`.`email` AS `email`,`E`.`event_id` AS `event_id`,`E`.`user_id` AS `user_id`,`E`.`attending` AS `attending`,`E`.`register_time` AS `register_time` from (`Event_has_User` `E` join `User` `U` on((`E`.`user_id` = `U`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `GroupUser`
--

/*!50001 DROP TABLE IF EXISTS `GroupUser`*/;
/*!50001 DROP VIEW IF EXISTS `GroupUser`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `GroupUser` AS select `U`.`id` AS `id`,`U`.`name` AS `name`,`U`.`passwd` AS `passwd`,`U`.`email` AS `email`,`U`.`title` AS `title`,`U`.`created_date` AS `created_date`,`U`.`modified_date` AS `modified_date`,`U`.`frequency` AS `frequency`,`U`.`is_admin` AS `is_admin`,`CU`.`company_id` AS `company_id`,`C`.`name` AS `company_name`,`G`.`group_id` AS `group_id`,`G`.`type` AS `type` from (((`User` `U` join `Group_has_User` `G` on((`U`.`id` = `G`.`user_id`))) join `CompanyUser` `CU` on((`U`.`id` = `CU`.`user_id`))) join `Company` `C` on((`CU`.`company_id` = `C`.`id`))) order by `G`.`type` desc,`U`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `GroupUserWithGroupNames`
--

/*!50001 DROP TABLE IF EXISTS `GroupUserWithGroupNames`*/;
/*!50001 DROP VIEW IF EXISTS `GroupUserWithGroupNames`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `GroupUserWithGroupNames` AS select `U`.`id` AS `id`,`U`.`name` AS `name`,`U`.`passwd` AS `passwd`,`U`.`email` AS `email`,`U`.`title` AS `title`,`U`.`created_date` AS `created_date`,`U`.`modified_date` AS `modified_date`,`U`.`frequency` AS `frequency`,`U`.`is_admin` AS `is_admin`,`CU`.`company_id` AS `company_id`,`C`.`name` AS `company_name`,`G`.`group_id` AS `group_id`,`G`.`type` AS `type`,`Gr`.`name` AS `group_name` from ((((`User` `U` join `Group_has_User` `G` on((`U`.`id` = `G`.`user_id`))) join `Group` `Gr` on((`G`.`group_id` = `Gr`.`id`))) join `CompanyUser` `CU` on((`U`.`id` = `CU`.`user_id`))) join `Company` `C` on((`CU`.`company_id` = `C`.`id`))) order by `G`.`type` desc,`U`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `MeetingMinuteEntry`
--

/*!50001 DROP TABLE IF EXISTS `MeetingMinuteEntry`*/;
/*!50001 DROP VIEW IF EXISTS `MeetingMinuteEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE DEFINER=`stjornvisi_t`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `MeetingMinuteEntry` AS select `M`.`id` AS `id`,`M`.`title` AS `title`,`M`.`summary` AS `summary`,`M`.`body` AS `body`,`M`.`created` AS `created`,`M`.`time` AS `time`,`M`.`group_id` AS `group_id`,`M`.`author_id` AS `author_id`,`G`.`name` AS `name`,`G`.`name_short` AS `name_short`,`G`.`url` AS `url` from (`MeetingMinute` `M` left join `Group` `G` on((`G`.`id` = `M`.`group_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `NewsEntry`
--

/*!50001 DROP TABLE IF EXISTS `NewsEntry`*/;
/*!50001 DROP VIEW IF EXISTS `NewsEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `NewsEntry` AS select `N`.`id` AS `id`,`N`.`title` AS `title`,`N`.`body` AS `body`,`N`.`avatar` AS `avatar`,`N`.`created_date` AS `created_date`,`N`.`modified_date` AS `modified_date`,`N`.`group_id` AS `group_id`,`N`.`user_id` AS `user_id`,`G`.`name` AS `name`,`G`.`name_short` AS `name_short`,`G`.`url` AS `url` from (`News` `N` left join `Group` `G` on((`N`.`group_id` = `G`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `UserEntry`
--

/*!50001 DROP TABLE IF EXISTS `UserEntry`*/;
/*!50001 DROP VIEW IF EXISTS `UserEntry`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `UserEntry` AS select `U`.`id` AS `id`,`U`.`name` AS `name`,`U`.`email` AS `email`,`U`.`passwd` AS `passwd`,`U`.`title` AS `title`,`U`.`created_date` AS `created_date`,`U`.`modified_date` AS `modified_date`,`U`.`frequency` AS `frequency`,`CU`.`company_id` AS `company_id`,`U`.`is_admin` AS `is_admin`,`C`.`name` AS `company_name`,`C`.`safe_name` AS `safe_name`,`CU`.`key_user` AS `key_user` from ((`User` `U` left join `Company_has_User` `CU` on((`U`.`id` = `CU`.`user_id`))) left join `Company` `C` on((`CU`.`company_id` = `C`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-19 10:34:32
