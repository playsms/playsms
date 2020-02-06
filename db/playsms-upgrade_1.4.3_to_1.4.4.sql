-- 1.4.4


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.4-test' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `salt` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password` ;
