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

ALTER TABLE `playsms_tblConfig_main` DROP `cfg_gateway_module` ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_receiver_gateway_module` varchar(20) NOT NULL ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_sender_gateway_withrules` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_sender_gateway_module` varchar(20) NOT NULL ;

UPDATE `playsms_tblConfig_main` SET `cfg_receiver_gateway_module` = `smstools` ;
UPDATE `playsms_tblConfig_main` SET `cfg_sender_gateway_module` = `smstools` ;
UPDATE `playsms_tblConfig_main` SET `cfg_sender_gateway_withrules` = FALSE ;

UPDATE `playsms_gatewayClickatell_config` SET `ready` = FALSE ;
UPDATE `playsms_gatewayGnokii_config` SET `ready` = FALSE ;
UPDATE `playsms_gatewayKannel_config` SET `ready` = FALSE ;
UPDATE `playsms_gatewayMsgtoolbox_config` SET `ready` = FALSE ;
UPDATE `playsms_gatewayTemplate_config` SET `ready` = FALSE ;
UPDATE `playsms_gatewayUplink_config` SET `ready` = FALSE ;

--
-- Table structure for table `playsms_gateway_rules`
--

DROP TABLE IF EXISTS `playsms_gateway_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gateway_rules` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`gateway` varchar(20) NOT NULL,
	`rules` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gateway_rules`
--

LOCK TABLES `playsms_gateway_rules` WRITE;
/*!40000 ALTER TABLE `playsms_gateway_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gateway_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewaySMSTools_config`
--

DROP TABLE IF EXISTS `playsms_gatewaySMSTools_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewaySMSTools_config` (
	`ready` BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewaySMSTools_config`
--

LOCK TABLES `playsms_gatewaySMSTools_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewaySMSTools_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewaySMSTools_config` VALUES (FALSE);
/*!40000 ALTER TABLE `playsms_gatewaySMSTools_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayGammu_config`
--

DROP TABLE IF EXISTS `playsms_gatewayGammu_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayGammu_config` (
	`ready` BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayGammu_config`
--

LOCK TABLES `playsms_gatewayGammu_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayGammu_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayGammu_config` VALUES (FALSE);
/*!40000 ALTER TABLE `playsms_gatewayGammu_config` ENABLE KEYS */;
UNLOCK TABLES;
