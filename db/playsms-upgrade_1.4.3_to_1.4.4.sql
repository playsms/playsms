-- 1.4.4


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.4-beta' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `salt` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password` ;

-- tblBilling
ALTER TABLE `playsms_tblBilling`  ADD `parent_uid` INT(11) NOT NULL DEFAULT '0'  AFTER `c_timestamp`,  ADD `uid` INT(11) NOT NULL DEFAULT '0'  AFTER `parent_uid` ;
