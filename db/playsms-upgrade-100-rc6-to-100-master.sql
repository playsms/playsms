-- 1.0-master
---------------------------------------------------------------------------------------

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- user soft delete
ALTER TABLE `playsms`.`playsms_tblUser` 
ADD COLUMN `flag_deleted` INT(11) NOT NULL AFTER `lastupdate_datetime`;
