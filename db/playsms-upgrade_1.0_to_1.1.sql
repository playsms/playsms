-- 1.1-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.1' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- sms custom
ALTER TABLE `playsms_featureCustom` CHANGE `custom_keyword` `custom_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `playsms_featureCustom` ADD `service_name` varchar(255) NOT NULL DEFAULT '' AFTER `uid` ;
ALTER TABLE `playsms_featureCustom` ADD `sms_receiver` varchar(20) NOT NULL DEFAULT '' AFTER `custom_keyword` ;
