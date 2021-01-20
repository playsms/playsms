-- 1.4.4


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.4-beta3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `salt` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password` ;

-- tblBilling
ALTER TABLE `playsms_tblBilling`  ADD `parent_uid` INT(11) NOT NULL DEFAULT '0'  AFTER `c_timestamp`,  ADD `uid` INT(11) NOT NULL DEFAULT '0'  AFTER `parent_uid` ;

-- featureSendfromfile
ALTER TABLE `playsms_featureSendfromfile` 
	ADD `sms_uid` INT(11) NOT NULL DEFAULT '11' AFTER `sms_username`, 
	ADD `hash` VARCHAR(40) NOT NULL DEFAULT '' AFTER `sms_uid`, 
	ADD `unicode` INT(11) NOT NULL DEFAULT '0' AFTER `hash`, 
	ADD `charge` FLOAT NOT NULL DEFAULT '0' AFTER `unicode`, 
	ADD `smslog_id` INT(11) NOT NULL DEFAULT '0' AFTER `charge`, 
	ADD `queue_code` VARCHAR(40) NOT NULL DEFAULT '' AFTER `smslog_id`, 
	ADD `status` INT(11) NOT NULL DEFAULT '0' AFTER `queue_code`, 
	ADD `flag_processed` INT(11) NOT NULL DEFAULT '0' AFTER `status`; 
