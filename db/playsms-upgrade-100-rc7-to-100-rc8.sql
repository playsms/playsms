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
