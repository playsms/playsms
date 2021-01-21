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
