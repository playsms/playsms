-- 1.0-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

RENAME TABLE `playsms_tblUser_inbox` TO `playsms_tblSMSInbox` ;

DROP TABLE `playsms_tblUser_country`;

ALTER TABLE `playsms_tblUser` ADD COLUMN `flag_deleted` INT(11) NOT NULL AFTER `lastupdate_datetime`;

ALTER TABLE `playsms_featurePhonebook` ADD `username` VARCHAR( 100 ) NOT NULL DEFAULT '';

ALTER TABLE `playsms_featurePhonebook` CHANGE `username` `tags` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
