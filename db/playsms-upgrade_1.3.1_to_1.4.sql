-- 1.4


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblSMSOutgoing
ALTER TABLE `playsms_tblSMSOutgoing` ADD `parent_uid` INT(11) NOT NULL DEFAULT '0' AFTER `uid`;

-- update tblSMSOutgoing.parent_uid with tblUser.parent_uid
UPDATE `playsms_tblSMSOutgoing` A INNER JOIN `playsms_tblUser` B ON A.uid=B.uid SET A.parent_uid=B.parent_uid;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` ADD `adhoc_credit` DECIMAL(13,3) NOT NULL DEFAULT '0.000' AFTER `credit`;

-- featureCustom
ALTER TABLE `playsms_featureCustom` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' AFTER `custom_return_as_reply`;

-- featureCommand
ALTER TABLE `playsms_featureCommand` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' AFTER `command_return_as_reply`;

-- featureBoard
ALTER TABLE `playsms_featureBoard` ADD `board_reply` VARCHAR(100) NOT NULL DEFAULT '' AFTER `board_keyword`;

-- featureBoard_log
ALTER TABLE `playsms_featureBoard_log` ADD `in_reply` VARCHAR(100) NOT NULL DEFAULT '' AFTER `in_msg`;
ALTER TABLE `playsms_featureBoard_log` ADD `board_id` INT NOT NULL DEFAULT '0' AFTER `in_id`;
