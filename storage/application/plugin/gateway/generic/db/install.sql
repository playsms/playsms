--
-- Table structure for table `playsms_gatewayGeneric`
--

DROP TABLE IF EXISTS `playsms_gatewayGeneric_log`;
CREATE TABLE `playsms_gatewayGeneric_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_smslog_id` int(11) NOT NULL DEFAULT '0',
  `remote_smslog_id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

