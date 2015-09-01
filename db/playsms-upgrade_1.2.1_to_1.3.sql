-- 1.3-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- playsmsd multi submission
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` ADD `chunk` INT(11) NOT NULL DEFAULT '0' AFTER `queue_id`;
