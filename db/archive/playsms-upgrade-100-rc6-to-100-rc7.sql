-- 1.0-rc7


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

RENAME TABLE `playsms_tblUser_inbox` TO `playsms_tblSMSInbox` ;

DROP TABLE `playsms_tblUser_country`;

ALTER TABLE `playsms_tblUser` ADD COLUMN `flag_deleted` INT(11) NOT NULL AFTER `lastupdate_datetime`;

ALTER TABLE `playsms_featurePhonebook` ADD `username` VARCHAR( 100 ) NOT NULL DEFAULT '';

ALTER TABLE `playsms_featurePhonebook` CHANGE `username` `tags` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

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

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc7' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;
