-- 1.4.7

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.7' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `salt` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password` ;

-- tblBilling
ALTER TABLE `playsms_tblBilling`  ADD `parent_uid` INT(11) NOT NULL DEFAULT '0'  AFTER `c_timestamp`,  ADD `uid` INT(11) NOT NULL DEFAULT '0'  AFTER `parent_uid` ;
ALTER TABLE `playsms_tblBilling` ADD KEY `smslog_id` (`smslog_id`);

-- tblSMSOutgoing_queue_dst
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` DROP `smslog_id`;
