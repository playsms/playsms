DROP TABLE `playsms_tblErrorString` ;

ALTER TABLE `playsms_tblSMSTemplate` RENAME TO `playsms_toolsMsgtemplate` ;

ALTER TABLE `playsms_tblUser` ADD `register_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `plus_sign_add` ;
ALTER TABLE `playsms_tblUser` ADD `lastupdate_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `register_datetime` ;

ALTER TABLE `playsms_tblUser` MODIFY `password` varchar(32) NOT NULL ;

ALTER TABLE `playsms_featurePoll` ADD `poll_message_valid` varchar(100) NOT NULL ;
ALTER TABLE `playsms_featurePoll` ADD `poll_message_invalid` varchar(100) NOT NULL ;

ALTER TABLE `playsms_featureSubscribe` ADD `subscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `unsubscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `forward_param` varchar(20) NOT NULL ;

ALTER TABLE `playsms_featureCommand_log` ADD `command_log_output` text NOT NULL ;
ALTER TABLE `playsms_featureCommand` ADD `with_alarm` BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE `playsms_featureCommand` ADD `with_answer` BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE `playsms_featureCommand` ADD `command_msg` text;

--
-- Table structure for table `playsms_featureCommand_Alarm`
--

DROP TABLE IF EXISTS `playsms_featureCommand_Alarm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCommand_Alarm` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `alarm_id` int(11) NOT NULL AUTO_INCREMENT,
  `command_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `alarm_name` varchar(100) NOT NULL,
  `alarm_msg` text NOT NULL,
  `alarm_min_value` int(11) NOT NULL,
  `alarm_max_value` int(11) NOT NULL,
  PRIMARY KEY (`alarm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCommand_Alarm`
--

LOCK TABLES `playsms_featureCommand_Alarm` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCommand_Alarm_contacts`
--

DROP TABLE IF EXISTS `playsms_featureCommand_Alarm_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCommand_Alarm_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		
  `alarm_id` int(11) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCommand_Alarm_contacts`
--

LOCK TABLES `playsms_featureCommand_Alarm_contacts` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCommand_Alarm_group_id`
--

DROP TABLE IF EXISTS `playsms_featureCommand_Alarm_group_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCommand_Alarm_group_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		
  `alarm_id` int(11) NOT NULL,
  `gpid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCommand_Alarm_contacts`
--

LOCK TABLES `playsms_featureCommand_Alarm_contacts` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand_Alarm_contacts` ENABLE KEYS */;
UNLOCK TABLES;

