-- 1.4-master


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblSMSOutgoing
ALTER TABLE `playsms_tblSMSOutgoing` ADD `parent_uid` INT(11) NOT NULL DEFAULT '0' AFTER `uid`;

-- update tblSMSOutgoing.parent_uid with tblUser.parent_uid
UPDATE `playsms_tblSMSOutgoing` A INNER JOIN `playsms_tblUser` B ON A.uid=B.uid SET A.parent_uid=B.parent_uid;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;
