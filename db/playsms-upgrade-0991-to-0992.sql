ALTER TABLE  `playsms_tblSMSOutgoing` CHANGE  `smslog_id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE  `playsms_tblSMSOutgoing` ADD  `smslog_id` INT( 11 ) NOT NULL AFTER  `id` ;
UPDATE `playsms_tblSMSOutgoing` SET `smslog_id` = `id` ;
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` AUTO_INCREMENT = 1000000 ;
ALTER TABLE  `playsms_tblSMSOutgoing` ADD UNIQUE (`smslog_id`) ;
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` DROP `smslog_id` ;

--
-- Table structure for table `playsms_tblSMSOutgoing_queue`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSOutgoing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_code` varchar(40) NOT NULL DEFAULT '',
  `datetime_entry` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_scheduled` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_update` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `flag` int(11) NOT NULL DEFAULT '0',
  `sms_count` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `gpid` int(11) NOT NULL DEFAULT '0',
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `footer` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL DEFAULT '',
  `sms_type` varchar(100) NOT NULL DEFAULT '',
  `unicode` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_code` (`queue_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playsms_tblRecvSMS`
--

DROP TABLE IF EXISTS `playsms_tblRecvSMS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblRecvSMS` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_processed` tinyint(4) NOT NULL DEFAULT '0',
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `message` text NOT NULL DEFAULT '',
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playsms_tblDLR`
--

DROP TABLE IF EXISTS `playsms_tblDLR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblDLR` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_processed` tinyint(4) NOT NULL DEFAULT '0',
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `p_status` tinyint(4) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

