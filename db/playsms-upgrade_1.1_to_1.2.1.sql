-- 1.2.1-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.2.1' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- feature outgoing
ALTER TABLE `playsms_featureOutgoing` CHANGE `prefix` `prefix` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

--
-- Table structure for table `playsms_gatewayPlaynet_outgoing`
--

DROP TABLE IF EXISTS `playsms_gatewayPlaynet_outgoing`;
CREATE TABLE `playsms_gatewayPlaynet_outgoing` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flag` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `smsc` varchar(100) NOT NULL DEFAULT '',
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `sms_to` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_type` int(11) NOT NULL DEFAULT '0',
  `unicode` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core ACL
ALTER TABLE `playsms_tblACL` ADD `flag_disallowed` INT(11) NOT NULL DEFAULT '0' AFTER `url`;

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

