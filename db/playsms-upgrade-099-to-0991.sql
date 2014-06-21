ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_sendsms_host` varchar(250) DEFAULT NULL AFTER `cfg_bearerbox_host`;

ALTER TABLE `playsms_featureBoard` ADD `board_css` varchar(250) NOT NULL DEFAULT '' AFTER `board_forward_email`;

--
-- Table structure for table `playsms_gatewayTwilio`
--

DROP TABLE IF EXISTS `playsms_gatewayTwilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTwilio` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` varchar(40) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `error_text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTwilio`
--

LOCK TABLES `playsms_gatewayTwilio` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTwilio` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayTwilio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTwilio_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTwilio_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTwilio_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'twilio',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_callback_url` varchar(250) DEFAULT NULL,
  `cfg_account_sid` varchar(100) DEFAULT NULL,
  `cfg_auth_token` varchar(100) DEFAULT NULL,
  `cfg_global_sender` varchar(20) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTwilio_config`
--

LOCK TABLES `playsms_gatewayTwilio_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTwilio_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTwilio_config` VALUES (0,'twilio','https://api.twilio.com','http://localhost/playsms/plugin/gateway/twilio/callback.php','12345678','87654321','+10000000000','+0700');
/*!40000 ALTER TABLE `playsms_gatewayTwilio_config` ENABLE KEYS */;
UNLOCK TABLES;

