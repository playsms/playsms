-- 1.3-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- gateway plugin Generic

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


-- playsmsd multi submission
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` ADD `chunk` INT(11) NOT NULL DEFAULT '0' AFTER `queue_id`;
