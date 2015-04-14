-- 1.0-rc5


--
-- Table structure for table `playsms_featureFirewall`
--

DROP TABLE IF EXISTS `playsms_featureFirewall`;
CREATE TABLE `playsms_featureFirewall` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `ip_address` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

ALTER TABLE `playsms_featurePhonebook` ADD INDEX ( `uid` ) ;
ALTER TABLE `playsms_featurePhonebook` ADD INDEX ( `mobile` ) ;

ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `uid` ) ;
ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `flag_sender` ) ;
ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `code` ) ;

ALTER TABLE `playsms_featurePhonebook_group_contacts` ADD INDEX ( `gpid` ) ;
ALTER TABLE `playsms_featurePhonebook_group_contacts` ADD INDEX ( `pid` ) ;

--
-- Table structure for table `playsms_featureSchedule`
--

DROP TABLE IF EXISTS `playsms_featureSchedule`;
CREATE TABLE `playsms_featureSchedule` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `schedule_rule` int(11) NOT NULL DEFAULT '0',
  `flag_active` int(11) NOT NULL DEFAULT '0',
  `flag_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `playsms_featureSchedule_dst`
--

DROP TABLE IF EXISTS `playsms_featureSchedule_dst`;
CREATE TABLE `playsms_featureSchedule_dst` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL DEFAULT '0',
  `schedule` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL DEFAULT '',
  `destination` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `playsms_tblACL`
--

DROP TABLE IF EXISTS `playsms_tblACL`;
CREATE TABLE `playsms_tblACL` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `acl_subuser` varchar(250) NOT NULL DEFAULT '',
  `url` text NOT NULL,
  `flag_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `playsms_tblACL`
--

INSERT INTO `playsms_tblACL` (`c_timestamp`, `id`, `name`, `acl_subuser`, `url`, `flag_deleted`) VALUES
(0, 1, 'BROADCAST', '', 'inc=core_sendsms,\r\ninc=core_user,\r\ninc=feature_report,\r\ninc=feature_schedule,\r\ninc=feature_msgtemplate,\r\ninc=feature_queuelog,\r\ninc=feature_credit,\r\ninc=feature_report&route=user\r\n', 0);

ALTER TABLE `playsms_tblUser` ADD `acl_id` INT NOT NULL DEFAULT '0' AFTER `status`;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc5' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc6

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc6' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


--
-- Table structure for table `playsms_gatewayTelerivet`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` varchar(40) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `phone_id` varchar(40) NOT NULL DEFAULT '',
  `message_type` varchar(20) NOT NULL DEFAULT '',
  `source` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet`
--

LOCK TABLES `playsms_gatewayTelerivet` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTelerivet_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'telerivet',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_api_key` varchar(250) DEFAULT NULL,
  `cfg_project_id` varchar(250) DEFAULT NULL,
  `cfg_status_url` varchar(250) DEFAULT NULL,
  `cfg_status_secret` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet_config`
--

LOCK TABLES `playsms_gatewayTelerivet_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTelerivet_config` VALUES (0,'telerivet','https://api.telerivet.com/','12345678','abc123cde456','https://localhost/plugin/gateway/telerivet/callback.php','myS3cr3t');
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` ENABLE KEYS */;
UNLOCK TABLES;


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


-- 1.0-rc8


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

ALTER TABLE `playsms_tblSMSOutgoing` CHANGE `p_gateway` `p_smsc` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblSMSOutgoing` ADD `p_gateway` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `uid`;

--
-- Table structure for table `playsms_featureStoplist`
--

DROP TABLE IF EXISTS `playsms_featureStoplist`;
CREATE TABLE `playsms_featureStoplist` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc8' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;
