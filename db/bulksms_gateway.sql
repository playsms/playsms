--
-- Table structure for table `playsms_gatewayBulksms_apidata`
--
DROP TABLE IF EXISTS `playsms_gatewayBulksms_apidata`;

CREATE TABLE `playsms_gatewayBulksms_apidata` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`apidata_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `playsms_gatewayBulksms_apidata` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_apidata` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_apidata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayBulksms_config`
--
DROP TABLE IF EXISTS `playsms_gatewayBulksms_config`;

CREATE TABLE `playsms_gatewayBulksms_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'bulksms',
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_module_sender` varchar(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayBulksms_config`
--
LOCK TABLES `playsms_gatewayBulksms_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayBulksms_config` VALUES (0,'bulksms','playsms','playsms','PlaySMS','http://bulksms.vsms.net:5567/eapi','/var/spool/playsms',5,'','');
/*!40000 ALTER TABLE `playsms_gatewayBulksms_config` ENABLE KEYS */;
UNLOCK TABLES;

