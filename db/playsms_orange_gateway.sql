--
-- Table structure for table `playsms_gatewayOrange_apidata`
--
DROP TABLE IF EXISTS `playsms_gatewayOrange_apidata`;

CREATE TABLE `playsms_gatewayOrange_apidata` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`apidata_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `playsms_gatewayOrange_apidata` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayOrange_apidata` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayOrange_apidata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayOrange_config`
--
DROP TABLE IF EXISTS `playsms_gatewayOrange_config`;

CREATE TABLE `playsms_gatewayOrange_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'orange',
  `cfg_country_code` varchar(20) NOT NULL DEFAULT '',
  `cfg_client_id` varchar(100) NOT NULL DEFAULT '',
  `cfg_client_secret` varchar(250) NOT NULL DEFAULT '',
  `cfg_sender_address` varchar(20) DEFAULT NULL,
  `cfg_sender_name` varchar(20) NOT NULL DEFAULT '',
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_token` varchar(100) NOT NULL DEFAULT '',
  `cfg_token_updated_at` bigint(20) NOT NULL DEFAULT '0',
  `cfg_token_expirate_at` bigint(20) NOT NULL DEFAULT '0',
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayOrange_config`
--
LOCK TABLES `playsms_gatewayOrange_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayOrange_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayOrange_config` VALUES (0,'orange','','','','','','https://api.orange.com','/var/spool/playsms','',0,0,0,'+0000');
/*!40000 ALTER TABLE `playsms_gatewayOrange_config` ENABLE KEYS */;
UNLOCK TABLES;