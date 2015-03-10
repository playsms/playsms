-- 1.0-master
---------------------------------------------------------------------------------------

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

RENAME TABLE `playsms`.`playsms_tblUser_inbox` TO `playsms`.`playsms_tblSMSInbox` ;
