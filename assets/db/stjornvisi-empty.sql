-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: stjornvisi_staging
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=710 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_end` time DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `lat` double(11,8) DEFAULT NULL,
  `lng` double(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=576 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1518 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=308 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `Group_has_User`
--

DROP TABLE IF EXISTS `Group_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_User` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `fk_Group_has_User_User1` (`user_id`),
  CONSTRAINT `fk_Group_has_User_Group` FOREIGN KEY (`group_id`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Group_has_User_User1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1011 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=552 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `get_message` tinyint(1) DEFAULT NULL,
  `get_notify` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_oauth_key` (`oauth_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3752 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-03-19 22:48:55
