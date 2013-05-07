-- MySQL dump 10.13  Distrib 5.5.31, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: playsms
-- ------------------------------------------------------
-- Server version	5.5.31-0ubuntu0.12.04.1

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
-- Table structure for table `playsms_featureAutoreply`
--

DROP TABLE IF EXISTS `playsms_featureAutoreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureAutoreply` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `autoreply_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `autoreply_keyword` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`autoreply_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureAutoreply`
--

LOCK TABLES `playsms_featureAutoreply` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutoreply` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutoreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureAutoreply_scenario`
--

DROP TABLE IF EXISTS `playsms_featureAutoreply_scenario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureAutoreply_scenario` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `autoreply_scenario_id` int(11) NOT NULL AUTO_INCREMENT,
  `autoreply_id` int(11) NOT NULL DEFAULT '0',
  `autoreply_scenario_param1` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param2` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param3` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param4` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param5` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param6` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param7` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_result` text NOT NULL,
  PRIMARY KEY (`autoreply_scenario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureAutoreply_scenario`
--

LOCK TABLES `playsms_featureAutoreply_scenario` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutoreply_scenario` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutoreply_scenario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureAutosend`
--

DROP TABLE IF EXISTS `playsms_featureAutosend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureAutosend` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `autosend_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `autosend_message` varchar(200) NOT NULL,
  `autosend_number` varchar(20) NOT NULL,
  `autosend_enable` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`autosend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureAutosend`
--

LOCK TABLES `playsms_featureAutosend` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutosend` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutosend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureAutosend_time`
--

DROP TABLE IF EXISTS `playsms_featureAutosend_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureAutosend_time` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `time_id` int(11) NOT NULL AUTO_INCREMENT,
  `autosend_id` int(11) NOT NULL,
  `autosend_time` varchar(20) NOT NULL,
  `sent` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureAutosend_time`
--

LOCK TABLES `playsms_featureAutosend_time` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutosend_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutosend_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureBoard`
--

DROP TABLE IF EXISTS `playsms_featureBoard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureBoard` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `board_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `board_keyword` varchar(100) NOT NULL DEFAULT '',
  `board_forward_email` varchar(250) NOT NULL DEFAULT '',
  `board_pref_template` text NOT NULL,
  PRIMARY KEY (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureBoard`
--

LOCK TABLES `playsms_featureBoard` WRITE;
/*!40000 ALTER TABLE `playsms_featureBoard` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureBoard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureBoard_log`
--

DROP TABLE IF EXISTS `playsms_featureBoard_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureBoard_log` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `in_id` int(11) NOT NULL AUTO_INCREMENT,
  `in_gateway` varchar(100) NOT NULL DEFAULT '',
  `in_sender` varchar(20) NOT NULL DEFAULT '',
  `in_masked` varchar(20) NOT NULL DEFAULT '',
  `in_keyword` varchar(20) NOT NULL DEFAULT '',
  `in_msg` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureBoard_log`
--

LOCK TABLES `playsms_featureBoard_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureBoard_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureBoard_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCommand`
--

DROP TABLE IF EXISTS `playsms_featureCommand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCommand` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `command_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `command_keyword` varchar(10) NOT NULL DEFAULT '',
  `command_exec` text NOT NULL,
  `command_return_as_reply` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`command_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCommand`
--

LOCK TABLES `playsms_featureCommand` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCustom`
--

DROP TABLE IF EXISTS `playsms_featureCustom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCustom` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `custom_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `custom_keyword` varchar(10) NOT NULL DEFAULT '',
  `custom_url` text NOT NULL,
  `custom_return_as_reply` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`custom_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCustom`
--

LOCK TABLES `playsms_featureCustom` WRITE;
/*!40000 ALTER TABLE `playsms_featureCustom` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCustom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureInboxgroup`
--

DROP TABLE IF EXISTS `playsms_featureInboxgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureInboxgroup` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `in_receiver` varchar(20) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `creation_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `exclusive` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureInboxgroup`
--

LOCK TABLES `playsms_featureInboxgroup` WRITE;
/*!40000 ALTER TABLE `playsms_featureInboxgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureInboxgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureInboxgroup_catchall`
--

DROP TABLE IF EXISTS `playsms_featureInboxgroup_catchall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureInboxgroup_catchall` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `uid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureInboxgroup_catchall`
--

LOCK TABLES `playsms_featureInboxgroup_catchall` WRITE;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_catchall` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_catchall` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureInboxgroup_log_in`
--

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureInboxgroup_log_in` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(20) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `sms_receiver` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureInboxgroup_log_in`
--

LOCK TABLES `playsms_featureInboxgroup_log_in` WRITE;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_log_in` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_log_in` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureInboxgroup_log_out`
--

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_out`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureInboxgroup_log_out` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_in_id` bigint(20) NOT NULL,
  `smslog_id` bigint(20) NOT NULL,
  `catchall` tinyint(4) NOT NULL DEFAULT '0',
  `uid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureInboxgroup_log_out`
--

LOCK TABLES `playsms_featureInboxgroup_log_out` WRITE;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_log_out` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_log_out` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureInboxgroup_members`
--

DROP TABLE IF EXISTS `playsms_featureInboxgroup_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureInboxgroup_members` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `uid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureInboxgroup_members`
--

LOCK TABLES `playsms_featureInboxgroup_members` WRITE;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureInboxgroup_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll`
--

DROP TABLE IF EXISTS `playsms_featurePoll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featurePoll` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `poll_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `poll_title` varchar(250) NOT NULL DEFAULT '',
  `poll_keyword` varchar(10) NOT NULL DEFAULT '',
  `poll_enable` int(11) NOT NULL DEFAULT '0',
  `poll_msg_valid` varchar(100) NOT NULL,
  `poll_msg_invalid` varchar(100) NOT NULL,
  `poll_message_valid` varchar(100) NOT NULL,
  `poll_message_invalid` varchar(100) NOT NULL,
  PRIMARY KEY (`poll_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featurePoll`
--

LOCK TABLES `playsms_featurePoll` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll_choice`
--

DROP TABLE IF EXISTS `playsms_featurePoll_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featurePoll_choice` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `choice_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL DEFAULT '0',
  `choice_title` varchar(250) NOT NULL DEFAULT '',
  `choice_keyword` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`choice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featurePoll_choice`
--

LOCK TABLES `playsms_featurePoll_choice` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll_log`
--

DROP TABLE IF EXISTS `playsms_featurePoll_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featurePoll_log` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL DEFAULT '0',
  `choice_id` int(11) NOT NULL DEFAULT '0',
  `poll_sender` varchar(20) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`result_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featurePoll_log`
--

LOCK TABLES `playsms_featurePoll_log` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureQuiz`
--

DROP TABLE IF EXISTS `playsms_featureQuiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureQuiz` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `quiz_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `quiz_keyword` varchar(20) NOT NULL,
  `quiz_question` varchar(100) NOT NULL,
  `quiz_answer` varchar(20) NOT NULL,
  `quiz_msg_correct` varchar(100) NOT NULL,
  `quiz_msg_incorrect` varchar(100) NOT NULL,
  `quiz_enable` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureQuiz`
--

LOCK TABLES `playsms_featureQuiz` WRITE;
/*!40000 ALTER TABLE `playsms_featureQuiz` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureQuiz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureQuiz_log`
--

DROP TABLE IF EXISTS `playsms_featureQuiz_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureQuiz_log` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `answer_id` int(4) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(4) NOT NULL DEFAULT '0',
  `quiz_answer` varchar(100) NOT NULL DEFAULT '',
  `quiz_sender` varchar(20) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL,
  PRIMARY KEY (`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureQuiz_log`
--

LOCK TABLES `playsms_featureQuiz_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureQuiz_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureQuiz_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSubscribe`
--

DROP TABLE IF EXISTS `playsms_featureSubscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSubscribe` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `subscribe_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `subscribe_keyword` varchar(20) NOT NULL,
  `subscribe_msg` varchar(200) NOT NULL,
  `unsubscribe_msg` varchar(200) NOT NULL,
  `subscribe_enable` int(11) NOT NULL DEFAULT '0',
  `subscribe_param` varchar(20) NOT NULL,
  `unsubscribe_param` varchar(20) NOT NULL,
  `forward_param` varchar(20) NOT NULL,
  PRIMARY KEY (`subscribe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSubscribe`
--

LOCK TABLES `playsms_featureSubscribe` WRITE;
/*!40000 ALTER TABLE `playsms_featureSubscribe` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSubscribe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSubscribe_member`
--

DROP TABLE IF EXISTS `playsms_featureSubscribe_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSubscribe_member` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe_id` int(11) NOT NULL DEFAULT '0',
  `member_number` varchar(20) NOT NULL,
  `member_since` varchar(20) NOT NULL,
  PRIMARY KEY (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSubscribe_member`
--

LOCK TABLES `playsms_featureSubscribe_member` WRITE;
/*!40000 ALTER TABLE `playsms_featureSubscribe_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSubscribe_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSubscribe_msg`
--

DROP TABLE IF EXISTS `playsms_featureSubscribe_msg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSubscribe_msg` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe_id` int(11) NOT NULL,
  `msg` varchar(200) NOT NULL,
  `create_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `counter` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSubscribe_msg`
--

LOCK TABLES `playsms_featureSubscribe_msg` WRITE;
/*!40000 ALTER TABLE `playsms_featureSubscribe_msg` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSubscribe_msg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSurvey`
--

DROP TABLE IF EXISTS `playsms_featureSurvey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSurvey` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `creation_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `keyword` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  `started` tinyint(4) NOT NULL,
  `running` tinyint(4) NOT NULL,
  `session` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSurvey`
--

LOCK TABLES `playsms_featureSurvey` WRITE;
/*!40000 ALTER TABLE `playsms_featureSurvey` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSurvey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSurvey_log`
--

DROP TABLE IF EXISTS `playsms_featureSurvey_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSurvey_log` (
  `c_timestamp` int(11) NOT NULL,
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `link_id` varchar(50) NOT NULL,
  `smslog_id` bigint(20) NOT NULL,
  `session` varchar(50) NOT NULL,
  `creation_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `question_number` int(11) NOT NULL,
  `question` varchar(140) NOT NULL,
  `incoming` tinyint(4) NOT NULL,
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_sender` varchar(20) NOT NULL,
  `in_receiver` varchar(20) NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSurvey_log`
--

LOCK TABLES `playsms_featureSurvey_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureSurvey_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSurvey_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSurvey_members`
--

DROP TABLE IF EXISTS `playsms_featureSurvey_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSurvey_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSurvey_members`
--

LOCK TABLES `playsms_featureSurvey_members` WRITE;
/*!40000 ALTER TABLE `playsms_featureSurvey_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSurvey_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureSurvey_questions`
--

DROP TABLE IF EXISTS `playsms_featureSurvey_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureSurvey_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `question` varchar(140) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureSurvey_questions`
--

LOCK TABLES `playsms_featureSurvey_questions` WRITE;
/*!40000 ALTER TABLE `playsms_featureSurvey_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureSurvey_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayClickatell_apidata`
--

DROP TABLE IF EXISTS `playsms_gatewayClickatell_apidata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayClickatell_apidata` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`apidata_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayClickatell_apidata`
--

LOCK TABLES `playsms_gatewayClickatell_apidata` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_apidata` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_apidata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayClickatell_config`
--

DROP TABLE IF EXISTS `playsms_gatewayClickatell_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayClickatell_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'clickatell',
  `cfg_api_id` varchar(20) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_sender` varchar(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayClickatell_config`
--

LOCK TABLES `playsms_gatewayClickatell_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayClickatell_config` VALUES (0,'clickatell','123456','playsms','playsms','PlaySMS','https://api.clickatell.com/http','/var/spool/playsms',10,'','+0700');
/*!40000 ALTER TABLE `playsms_gatewayClickatell_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayGnokii_config`
--

DROP TABLE IF EXISTS `playsms_gatewayGnokii_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayGnokii_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'gnokii',
  `cfg_path` varchar(250) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayGnokii_config`
--

LOCK TABLES `playsms_gatewayGnokii_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayGnokii_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayGnokii_config` VALUES (0,'gnokii','/var/spool/playsms');
/*!40000 ALTER TABLE `playsms_gatewayGnokii_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayKannel_config`
--

DROP TABLE IF EXISTS `playsms_gatewayKannel_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayKannel_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'kannel',
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_global_sender` varchar(20) DEFAULT NULL,
  `cfg_bearerbox_host` varchar(250) DEFAULT NULL,
  `cfg_sendsms_port` varchar(10) DEFAULT NULL,
  `cfg_playsms_web` varchar(250) DEFAULT NULL,
  `cfg_dlr` int(11) NOT NULL DEFAULT '31',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700',
  `cfg_admin_url` varchar(250) DEFAULT NULL,
  `cfg_admin_password` varchar(50) DEFAULT NULL,
  `cfg_admin_port` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayKannel_config`
--

LOCK TABLES `playsms_gatewayKannel_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayKannel_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayKannel_config` VALUES (0,'kannel','/var/spool/playsms','playsms','playsms','','127.0.0.1','13131','http://localhost/playsms',31,'','+0700','','',0);
/*!40000 ALTER TABLE `playsms_gatewayKannel_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayKannel_dlr`
--

DROP TABLE IF EXISTS `playsms_gatewayKannel_dlr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayKannel_dlr` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `kannel_dlr_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `kannel_dlr_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`kannel_dlr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayKannel_dlr`
--

LOCK TABLES `playsms_gatewayKannel_dlr` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayKannel_dlr` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayKannel_dlr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayMsgtoolbox`
--

DROP TABLE IF EXISTS `playsms_gatewayMsgtoolbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayMsgtoolbox` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayMsgtoolbox`
--

LOCK TABLES `playsms_gatewayMsgtoolbox` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayMsgtoolbox_config`
--

DROP TABLE IF EXISTS `playsms_gatewayMsgtoolbox_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayMsgtoolbox_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'msgtoolbox',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_route` varchar(5) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_global_sender` varchar(20) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayMsgtoolbox_config`
--

LOCK TABLES `playsms_gatewayMsgtoolbox_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayMsgtoolbox_config` VALUES (0,'msgtoolbox','http://serverX.msgtoolbox.com/api/current/send/message.php','1','playsms','password','playSMS','+0700');
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayNexmo`
--

DROP TABLE IF EXISTS `playsms_gatewayNexmo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayNexmo` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` varchar(40) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `network` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayNexmo`
--

LOCK TABLES `playsms_gatewayNexmo` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayNexmo` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayNexmo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayNexmo_config`
--

DROP TABLE IF EXISTS `playsms_gatewayNexmo_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayNexmo_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'nexmo',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_api_key` varchar(100) DEFAULT NULL,
  `cfg_api_secret` varchar(100) DEFAULT NULL,
  `cfg_global_sender` varchar(20) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayNexmo_config`
--

LOCK TABLES `playsms_gatewayNexmo_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayNexmo_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayNexmo_config` VALUES (0,'nexmo','https://rest.nexmo.com/sms/json','12345678','87654321','playSMS','+0700');
/*!40000 ALTER TABLE `playsms_gatewayNexmo_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewaySmstools_dlr`
--

DROP TABLE IF EXISTS `playsms_gatewaySmstools_dlr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewaySmstools_dlr` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `smslog_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `smslog_id` (`smslog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewaySmstools_dlr`
--

LOCK TABLES `playsms_gatewaySmstools_dlr` WRITE;
/*!40000 ALTER TABLE `playsms_gatewaySmstools_dlr` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewaySmstools_dlr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTemplate_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTemplate_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTemplate_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'template',
  `cfg_path` varchar(250) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTemplate_config`
--

LOCK TABLES `playsms_gatewayTemplate_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTemplate_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTemplate_config` VALUES (0,'template','/usr/local');
/*!40000 ALTER TABLE `playsms_gatewayTemplate_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayUplink`
--

DROP TABLE IF EXISTS `playsms_gatewayUplink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayUplink` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `up_id` int(11) NOT NULL AUTO_INCREMENT,
  `up_local_slid` int(11) NOT NULL DEFAULT '0',
  `up_remote_slid` int(11) NOT NULL DEFAULT '0',
  `up_status` tinyint(4) NOT NULL DEFAULT '0',
  `up_remote_queue_code` varchar(32) NOT NULL,
  `up_dst` varchar(100) NOT NULL,
  PRIMARY KEY (`up_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayUplink`
--

LOCK TABLES `playsms_gatewayUplink` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayUplink` DISABLE KEYS */;
INSERT INTO `playsms_gatewayUplink` VALUES (0,1,3,259,1,'','');
/*!40000 ALTER TABLE `playsms_gatewayUplink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayUplink_config`
--

DROP TABLE IF EXISTS `playsms_gatewayUplink_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayUplink_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'uplink',
  `cfg_master` varchar(250) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_token` varchar(32) DEFAULT NULL,
  `cfg_global_sender` varchar(20) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayUplink_config`
--

LOCK TABLES `playsms_gatewayUplink_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayUplink_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayUplink_config` VALUES (0,'uplink','http://playsms.master.url','','','','','/var/spool/playsms','','+0700');
/*!40000 ALTER TABLE `playsms_gatewayUplink_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblBilling`
--

DROP TABLE IF EXISTS `playsms_tblBilling`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblBilling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_timestamp` int(11) NOT NULL,
  `post_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `smslog_id` int(11) NOT NULL,
  `rate` float NOT NULL,
  `credit` double NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `charge` double NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblBilling`
--

LOCK TABLES `playsms_tblBilling` WRITE;
/*!40000 ALTER TABLE `playsms_tblBilling` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblBilling` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblConfig_main`
--

DROP TABLE IF EXISTS `playsms_tblConfig_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblConfig_main` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_web_title` varchar(250) DEFAULT NULL,
  `cfg_email_service` varchar(250) DEFAULT NULL,
  `cfg_email_footer` varchar(250) DEFAULT NULL,
  `cfg_gateway_module` varchar(20) DEFAULT NULL,
  `cfg_gateway_number` varchar(100) DEFAULT NULL,
  `cfg_themes_module` varchar(100) DEFAULT NULL,
  `cfg_default_rate` float NOT NULL DEFAULT '0',
  `cfg_language_module` varchar(10) DEFAULT 'en_US',
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700',
  `cfg_sms_max_count` tinyint(4) NOT NULL DEFAULT '3',
  `cfg_default_credit` float NOT NULL DEFAULT '0',
  `cfg_enable_register` tinyint(4) NOT NULL DEFAULT '0',
  `cfg_enable_forgot` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblConfig_main`
--

LOCK TABLES `playsms_tblConfig_main` WRITE;
/*!40000 ALTER TABLE `playsms_tblConfig_main` DISABLE KEYS */;
INSERT INTO `playsms_tblConfig_main` VALUES (1332916845,'playSMS','noreply@playsms.org','powered by playSMS','smstools','000','default',0,'en_US','+0700',3,0,0,1);
/*!40000 ALTER TABLE `playsms_tblConfig_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSIncoming`
--

DROP TABLE IF EXISTS `playsms_tblSMSIncoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSIncoming` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `in_id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `in_uid` int(11) NOT NULL DEFAULT '0',
  `in_feature` varchar(250) NOT NULL DEFAULT '',
  `in_gateway` varchar(100) NOT NULL DEFAULT '',
  `in_sender` varchar(100) NOT NULL DEFAULT '',
  `in_receiver` varchar(20) NOT NULL DEFAULT '',
  `in_keyword` varchar(100) NOT NULL DEFAULT '',
  `in_message` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblSMSIncoming`
--

LOCK TABLES `playsms_tblSMSIncoming` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSIncoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSIncoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSOutgoing`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSOutgoing` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `smslog_id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `p_gateway` varchar(100) NOT NULL DEFAULT '',
  `p_src` varchar(100) NOT NULL DEFAULT '',
  `p_dst` varchar(100) NOT NULL DEFAULT '',
  `p_footer` varchar(30) NOT NULL DEFAULT '',
  `p_msg` text NOT NULL,
  `p_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `p_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `p_status` tinyint(4) NOT NULL DEFAULT '0',
  `p_gpid` tinyint(4) NOT NULL DEFAULT '0',
  `p_credit` tinyint(4) NOT NULL DEFAULT '0',
  `p_sms_type` varchar(100) NOT NULL DEFAULT '',
  `unicode` tinyint(4) NOT NULL DEFAULT '0',
  `queue_code` varchar(40) NOT NULL,
  PRIMARY KEY (`smslog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblSMSOutgoing`
--

LOCK TABLES `playsms_tblSMSOutgoing` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSOutgoing_queue`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSOutgoing_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `queue_code` varchar(40) NOT NULL,
  `datetime_entry` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_scheduled` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_update` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `flag` tinyint(4) NOT NULL,
  `uid` int(11) NOT NULL,
  `gpid` int(11) NOT NULL,
  `sender_id` varchar(100) NOT NULL,
  `footer` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `sms_type` varchar(100) NOT NULL,
  `unicode` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_code` (`queue_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblSMSOutgoing_queue`
--

LOCK TABLES `playsms_tblSMSOutgoing_queue` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSOutgoing_queue_dst`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue_dst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSOutgoing_queue_dst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `smslog_id` bigint(20) NOT NULL,
  `flag` tinyint(4) NOT NULL,
  `dst` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblSMSOutgoing_queue_dst`
--

LOCK TABLES `playsms_tblSMSOutgoing_queue_dst` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUser`
--

DROP TABLE IF EXISTS `playsms_tblUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblUser` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `ticket` varchar(100) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL,
  `token` varchar(32) NOT NULL DEFAULT '',
  `enable_webservices` tinyint(4) NOT NULL DEFAULT '0',
  `webservices_ip` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `mobile` varchar(16) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `sender` varchar(16) NOT NULL DEFAULT '',
  `footer` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(250) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(100) NOT NULL DEFAULT '',
  `country` int(11) NOT NULL DEFAULT '0',
  `zipcode` varchar(10) NOT NULL DEFAULT '',
  `credit` double NOT NULL DEFAULT '0',
  `datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700',
  `language_module` varchar(10) NOT NULL DEFAULT 'en_US',
  `fwd_to_mobile` tinyint(4) NOT NULL DEFAULT '0',
  `fwd_to_email` tinyint(4) NOT NULL DEFAULT '1',
  `fwd_to_inbox` tinyint(4) NOT NULL DEFAULT '1',
  `replace_zero` varchar(5) NOT NULL DEFAULT '',
  `plus_sign_remove` tinyint(4) NOT NULL DEFAULT '1',
  `plus_sign_add` tinyint(4) NOT NULL DEFAULT '0',
  `local_length` tinyint(4) NOT NULL DEFAULT '9',
  `register_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdate_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblUser`
--

LOCK TABLES `playsms_tblUser` WRITE;
/*!40000 ALTER TABLE `playsms_tblUser` DISABLE KEYS */;
INSERT INTO `playsms_tblUser` VALUES (1332916885,1,2,'dd4aa3580e3de07858d812dcc094c32f','admin','21232f297a57a5a743894a0e4a801fc3','',0,'127.0.0.1, 192.168.*.*','Administrator','+62000000000','noreply@playsms.org','playSMS','@admin','','','',132,'',0,'+0700','en_US',0,1,1,'',1,0,0,'','');
/*!40000 ALTER TABLE `playsms_tblUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUserInbox`
--

DROP TABLE IF EXISTS `playsms_tblUserInbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblUserInbox` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `in_id` int(11) NOT NULL AUTO_INCREMENT,
  `in_sender` varchar(20) NOT NULL DEFAULT '',
  `in_receiver` varchar(20) NOT NULL DEFAULT '',
  `in_uid` int(11) NOT NULL DEFAULT '0',
  `in_msg` varchar(200) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_hidden` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblUserInbox`
--

LOCK TABLES `playsms_tblUserInbox` WRITE;
/*!40000 ALTER TABLE `playsms_tblUserInbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblUserInbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUser_country`
--

DROP TABLE IF EXISTS `playsms_tblUser_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblUser_country` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(200) NOT NULL DEFAULT '',
  `country_code` varchar(10) NOT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=335 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_tblUser_country`
--

LOCK TABLES `playsms_tblUser_country` WRITE;
/*!40000 ALTER TABLE `playsms_tblUser_country` DISABLE KEYS */;
INSERT INTO `playsms_tblUser_country` VALUES (0,1,'Afghanistan',''),(0,2,'Albania',''),(0,3,'Algeria',''),(0,5,'Andorra',''),(0,10,'Argentina',''),(0,11,'Armenia',''),(0,14,'Australia',''),(0,16,'Austria',''),(0,18,'Azerbaijan',''),(0,19,'Bahamas',''),(0,20,'Bahrain',''),(0,21,'Bangladesh',''),(0,24,'Belarus',''),(0,25,'Belgium',''),(0,29,'Bermuda',''),(0,30,'Bhutan',''),(0,31,'Bolivia',''),(0,32,'Bosnia-Herzegovina',''),(0,33,'Botswana',''),(0,35,'Brazil',''),(0,38,'Brunei',''),(0,39,'Bulgaria',''),(0,41,'Burundi',''),(0,42,'Cambodia',''),(0,44,'Cameroon',''),(0,45,'Canada',''),(0,51,'Chile',''),(0,52,'China',''),(0,55,'Columbia',''),(0,58,'Congo',''),(0,60,'Costa Rica',''),(0,61,'Croatia',''),(0,62,'Cuba',''),(0,66,'Czech Republic',''),(0,67,'Denmark',''),(0,74,'East Timor',''),(0,76,'Ecuador',''),(0,77,'Egypt',''),(0,78,'El Salvador',''),(0,81,'Estonia',''),(0,84,'Fiji Islands',''),(0,85,'Finland',''),(0,86,'France',''),(0,93,'Gabon',''),(0,94,'Gambia',''),(0,96,'Germany',''),(0,98,'Ghana',''),(0,100,'Greece',''),(0,105,'Guam',''),(0,107,'Guatemala',''),(0,108,'UK',''),(0,111,'Guyana',''),(0,112,'Haiti',''),(0,113,'Honduras',''),(0,114,'HongKong',''),(0,118,'Hungary',''),(0,120,'Iceland',''),(0,121,'India',''),(0,132,'Indonesia',''),(0,139,'Iran',''),(0,140,'Iraq',''),(0,141,'Ireland',''),(0,143,'Israel',''),(0,144,'Italy',''),(0,146,'Ivory Coast',''),(0,147,'Jamaica',''),(0,148,'Japan',''),(0,150,'Jordan',''),(0,151,'Kazakhstan',''),(0,153,'Kenya',''),(0,155,'Korea (South)',''),(0,156,'Korea (North)',''),(0,157,'Kuwait',''),(0,158,'Kyrgyzstan',''),(0,160,'Latvia',''),(0,161,'Lebanon',''),(0,163,'Liberia',''),(0,164,'Libya',''),(0,166,'Lithuania',''),(0,167,'Luxembourg',''),(0,170,'Macedonia',''),(0,171,'Malawi',''),(0,173,'Malaysia',''),(0,175,'Maldives',''),(0,177,'Mali Republic',''),(0,178,'Malta',''),(0,181,'Mauritania',''),(0,184,'Mexico',''),(0,186,'Moldova',''),(0,188,'Mongolia',''),(0,189,'Montserrat',''),(0,190,'Morocco',''),(0,192,'Mozambique',''),(0,193,'Myanmar',''),(0,194,'Namibia',''),(0,196,'Nepal',''),(0,197,'Netherlands',''),(0,200,'New Caledonia',''),(0,201,'New Zealand',''),(0,202,'Nicaragua',''),(0,203,'Niger',''),(0,204,'Nigeria',''),(0,208,'Norway',''),(0,209,'Oman',''),(0,210,'Pakistan',''),(0,211,'Palau',''),(0,212,'Palestine',''),(0,213,'Panama',''),(0,214,'Papua New Guinea',''),(0,215,'Paraguay',''),(0,216,'Peru',''),(0,217,'Philippines',''),(0,220,'Poland',''),(0,223,'Portugal',''),(0,225,'Puerto Rico',''),(0,226,'Qatar',''),(0,228,'Romania',''),(0,229,'Russia',''),(0,232,'Rwanda',''),(0,238,'Samoa',''),(0,241,'Saudi Arabia',''),(0,242,'Senegal',''),(0,244,'Sierra Leone',''),(0,245,'Singapore',''),(0,248,'Slovakia',''),(0,249,'Slovenia',''),(0,251,'Somalia',''),(0,252,'South Africa',''),(0,253,'Spain',''),(0,256,'Sri Lanka',''),(0,257,'Sudan',''),(0,258,'Suriname',''),(0,259,'Swaziland',''),(0,260,'Sweden',''),(0,262,'Switzerland',''),(0,263,'Syria',''),(0,264,'Taiwan',''),(0,267,'Tajikistan',''),(0,268,'Tanzania',''),(0,269,'Thailand',''),(0,274,'Trinidad and Tobago',''),(0,275,'Tunisia',''),(0,276,'Turkey',''),(0,277,'Turkmenistan',''),(0,279,'Tuvalu',''),(0,280,'Uganda',''),(0,281,'Ukraine',''),(0,284,'USA',''),(0,289,'United Arab Emirates',''),(0,290,'Uruguay',''),(0,291,'Uzbekistan',''),(0,293,'Vatican City State',''),(0,294,'Venezuela',''),(0,295,'Vietnam',''),(0,299,'Yemen',''),(0,300,'Yugoslavia',''),(0,303,'Zambia',''),(0,305,'Zimbabwe',''),(0,312,'Ethiopia',''),(0,314,'South Korea',''),(0,318,'Angola',''),(0,319,'Aruba',''),(0,320,'Laos',''),(0,325,'Serbia & Montenegro (Yugoslavia)',''),(0,332,'Jersey',''),(0,334,'OTHER (unlisted)','');
/*!40000 ALTER TABLE `playsms_tblUser_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsMsgtemplate`
--

DROP TABLE IF EXISTS `playsms_toolsMsgtemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_toolsMsgtemplate` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `t_title` varchar(100) NOT NULL DEFAULT '',
  `t_text` text NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsMsgtemplate`
--

LOCK TABLES `playsms_toolsMsgtemplate` WRITE;
/*!40000 ALTER TABLE `playsms_toolsMsgtemplate` DISABLE KEYS */;
INSERT INTO `playsms_toolsMsgtemplate` VALUES (0,1,1,'Good morning','Hi u there, good morning!!'),(0,2,1,'Good night have a sweet dream','Hi sweetheart, good night and have a sweet dream :*'),(0,3,1,'Meeting Now','Hello #NAME#, please hurry up, boss summons us !');
/*!40000 ALTER TABLE `playsms_toolsMsgtemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsPhonebook`
--

DROP TABLE IF EXISTS `playsms_toolsPhonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_toolsPhonebook` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gpid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsPhonebook`
--

LOCK TABLES `playsms_toolsPhonebook` WRITE;
/*!40000 ALTER TABLE `playsms_toolsPhonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsPhonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsPhonebook_group`
--

DROP TABLE IF EXISTS `playsms_toolsPhonebook_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_toolsPhonebook_group` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsPhonebook_group`
--

LOCK TABLES `playsms_toolsPhonebook_group` WRITE;
/*!40000 ALTER TABLE `playsms_toolsPhonebook_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsPhonebook_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSendfromfile`
--

DROP TABLE IF EXISTS `playsms_toolsSendfromfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_toolsSendfromfile` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_to` varchar(50) NOT NULL,
  `sms_msg` text NOT NULL,
  `sms_username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsSendfromfile`
--

LOCK TABLES `playsms_toolsSendfromfile` WRITE;
/*!40000 ALTER TABLE `playsms_toolsSendfromfile` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsSendfromfile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSimplerate`
--

DROP TABLE IF EXISTS `playsms_toolsSimplerate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_toolsSimplerate` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dst` varchar(100) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `rate` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsSimplerate`
--

LOCK TABLES `playsms_toolsSimplerate` WRITE;
/*!40000 ALTER TABLE `playsms_toolsSimplerate` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsSimplerate` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-07 10:58:26
