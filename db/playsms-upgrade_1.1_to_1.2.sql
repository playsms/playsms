-- 1.2-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.2' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- feature outgoing
ALTER TABLE `playsms_featureOutgoing` CHANGE `prefix` `prefix` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
