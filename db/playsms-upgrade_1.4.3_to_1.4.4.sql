-- 1.4.4

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.4-beta3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- tblUser
ALTER TABLE `playsms_tblUser` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `salt` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password` ;

-- tblBilling
ALTER TABLE `playsms_tblBilling`  ADD `parent_uid` INT(11) NOT NULL DEFAULT '0'  AFTER `c_timestamp`,  ADD `uid` INT(11) NOT NULL DEFAULT '0'  AFTER `parent_uid` ;

-- featureSendfromfile
ALTER TABLE `playsms_featureSendfromfile` 
	ADD `sms_uid` INT(11) NOT NULL DEFAULT '0' AFTER `sms_username`, 
	ADD `hash` VARCHAR(40) NOT NULL DEFAULT '' AFTER `sms_uid`, 
	ADD `unicode` INT(11) NOT NULL DEFAULT '0' AFTER `hash`, 
	ADD `charge` FLOAT NOT NULL DEFAULT '0' AFTER `unicode`, 
	ADD `smslog_id` INT(11) NOT NULL DEFAULT '0' AFTER `charge`, 
	ADD `queue_code` VARCHAR(40) NOT NULL DEFAULT '' AFTER `smslog_id`, 
	ADD `status` INT(11) NOT NULL DEFAULT '0' AFTER `queue_code`, 
	ADD `flag_processed` INT(11) NOT NULL DEFAULT '0' AFTER `status`; 

-- tblPlaysmsd
DROP TABLE IF EXISTS `playsms_tblPlaysmsd`;
CREATE TABLE `playsms_tblPlaysmsd` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `run_type` varchar(255) NOT NULL,
  `command` varchar(255) NOT NULL,
  `param` varchar(255) NOT NULL,
  `created` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finish` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pid` int(11) NOT NULL DEFAULT 0,
  `flag_run` int(11) NOT NULL DEFAULT 0,
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `playsms_tblPlaysmsd`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblPlaysmsd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- featureSimplerate
ALTER TABLE `playsms_featureSimplerate` DROP INDEX `prefix`;

-- featureSimplerate_card
DROP TABLE IF EXISTS `playsms_featureSimplerate_card`;
CREATE TABLE `playsms_featureSimplerate_card` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `created` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `playsms_featureSimplerate_card`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSimplerate_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- featureSimplerate_card_rate
DROP TABLE IF EXISTS `playsms_featureSimplerate_card_rate`;
CREATE TABLE `playsms_featureSimplerate_card_rate` (
  `id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `rate_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `playsms_featureSimplerate_card_rate`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSimplerate_card_rate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- featureSimplerate_card_user
DROP TABLE IF EXISTS `playsms_featureSimplerate_card_user`;
CREATE TABLE `playsms_featureSimplerate_card_user` (
  `id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `playsms_featureSimplerate_card_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSimplerate_card_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

