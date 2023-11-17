-- 1.3.1


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.3.1' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- Inbox
ALTER TABLE `playsms_tblSMSInbox` MODIFY COLUMN `in_msg` text NOT NULL ;
