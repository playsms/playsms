-- 1.4.8

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.8' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- hash
ALTER TABLE `playsms_featureBoard` CHANGE `board_access_code` `board_access_code` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_featurePoll` CHANGE `poll_access_code` `poll_access_code` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_featureSendfromfile` CHANGE `sid` `sid` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_gatewayUplink` CHANGE `up_remote_queue_code` `up_remote_queue_code` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_gatewayUplink_config` CHANGE `cfg_token` `cfg_token` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblSMSInbox` CHANGE `reference_id` `reference_id` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblSMSOutgoing` CHANGE `queue_code` `queue_code` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblSMSOutgoing_queue` CHANGE `queue_code` `queue_code` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblUser` CHANGE `token` `token` VARCHAR(255) NOT NULL DEFAULT '';
