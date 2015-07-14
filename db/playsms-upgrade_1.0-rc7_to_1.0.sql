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


-- 1.0-rc9


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc9' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

DROP INDEX `uid` ON `playsms_featurePhonebook` ;
DROP INDEX `mobile` ON `playsms_featurePhonebook` ;

DROP INDEX `uid` ON `playsms_featurePhonebook_group` ;
DROP INDEX `flag_sender` ON `playsms_featurePhonebook_group` ;
DROP INDEX `code` ON `playsms_featurePhonebook_group` ;

DROP INDEX `pid` ON `playsms_featurePhonebook_group_contacts` ;
DROP INDEX `gpid` ON `playsms_featurePhonebook_group_contacts` ;

CREATE INDEX `pid` ON `playsms_featurePhonebook_group_contacts` (`pid`) ;
CREATE INDEX `gpid` ON `playsms_featurePhonebook_group_contacts` (`gpid`) ;

CREATE INDEX `uid` on `playsms_tblSMSOutgoing` (`uid`);
CREATE INDEX `in_uid` on `playsms_tblSMSIncoming` (`in_uid`);
CREATE INDEX `in_uid` on `playsms_tblSMSInbox` (`in_uid`);

ALTER TABLE `playsms_tblSMSOutgoing_queue` ADD `queue_count` INT(11) NOT NULL DEFAULT '0' AFTER `flag`;
