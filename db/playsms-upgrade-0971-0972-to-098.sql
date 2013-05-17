DROP TABLE `playsms_tblErrorString` ;

ALTER TABLE `playsms_tblSMSTemplate` RENAME TO `playsms_toolsMsgtemplate` ;

ALTER TABLE `playsms_tblUser` ADD `register_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `plus_sign_add` ;
ALTER TABLE `playsms_tblUser` ADD `lastupdate_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `register_datetime` ;
ALTER TABLE `playsms_tblUser` ADD `token` VARCHAR(32) NOT NULL DEFAULT '' AFTER `password`;
ALTER TABLE `playsms_tblUser` ADD `enable_webservices` TINYINT(4) NOT NULL DEFAULT '0' AFTER `token`;
ALTER TABLE `playsms_tblUser` ADD `webservices_ip` varchar(100) NOT NULL DEFAULT '127.0.0.1, 192.168.*.*' AFTER `enable_webservices` ;
ALTER TABLE `playsms_tblUser` ADD `local_length` TINYINT NOT NULL DEFAULT '9' AFTER `plus_sign_add`;

ALTER TABLE `playsms_tblUser` MODIFY `password` varchar(32) NOT NULL ;

ALTER TABLE `playsms_tblSMSOutgoing` ADD `queue_code` varchar(40) NOT NULL ;

ALTER TABLE `playsms_featurePoll` ADD `poll_message_valid` varchar(100) NOT NULL ;
ALTER TABLE `playsms_featurePoll` ADD `poll_message_invalid` varchar(100) NOT NULL ;

ALTER TABLE `playsms_featureSubscribe` ADD `subscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `unsubscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `forward_param` varchar(20) NOT NULL ;

ALTER TABLE `playsms_featureSubscribe_msg` ADD `create_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `msg` ;
ALTER TABLE `playsms_featureSubscribe_msg` ADD `update_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `create_datetime` ;
ALTER TABLE `playsms_featureSubscribe_msg` ADD `counter` tinyint(4) NOT NULL DEFAULT '0' AFTER `update_datetime` ;

ALTER TABLE `playsms_gatewayUplink_config` ADD `cfg_token` varchar(32) NULL AFTER `cfg_password` ;

ALTER TABLE `playsms_gatewayUplink` ADD `up_remote_queue_code` varchar(32) NOT NULL, ADD `up_dst` varchar(100) NOT NULL ;

ALTER TABLE  `playsms_toolsSimplephonebook` CHANGE  `c_timestamp`  `c_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `pid`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `gpid`  `gpid` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `uid`  `uid` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `p_num`  `mobile` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `p_desc`  `name` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `p_email`  `email` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '' ;

RENAME TABLE  `playsms_toolsSimplephonebook` TO  `playsms_toolsPhonebook` ;

ALTER TABLE  `playsms_toolsSimplephonebook_group` CHANGE  `c_timestamp`  `c_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `gpid`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `uid`  `uid` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `gp_name`  `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `gp_code`  `code` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '' ;

RENAME TABLE  `playsms_toolsSimplephonebook_group` TO  `playsms_toolsPhonebook_group` ;

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
  `remote_slid` varchar(40) NOT NULL DEFAULT '',
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

DROP TABLE IF EXISTS `playsms_featureAutoreply_log` ;
DROP TABLE IF EXISTS `playsms_featureCommand_log` ;
DROP TABLE IF EXISTS `playsms_featureCustom_log` ;


