--
-- Table structure for table `playsms_gatewayJasmin`
--

DROP TABLE IF EXISTS `playsms_gatewayJasmin_log`;
CREATE TABLE `playsms_gatewayJasmin_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_smslog_id` int(11) NOT NULL DEFAULT '0',
  `remote_smslog_id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `playsms_gatewayJasmin_config`
--

DROP TABLE IF EXISTS `playsms_gatewayJasmin_config`;
CREATE TABLE `playsms_gatewayJasmin_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT 'jasmin',
  `url` varchar(250) NOT NULL DEFAULT '',
  `callback_url` varchar(250) NOT NULL DEFAULT '',
  `api_username` varchar(100) NOT NULL DEFAULT '',
  `api_password` varchar(100) NOT NULL DEFAULT '',
  `module_sender` varchar(20) NOT NULL DEFAULT '',
  `datetime_timezone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayJasmin_config`
--

INSERT INTO `playsms_gatewayJasmin_config` VALUES (0,'jasmin','http://127.0.0.1:1401/send','','admin','','','');
