ALTER TABLE `playsms_tblUser` ADD `datetime_timezone` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_datetime_timezone` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `playsms_gatewayClickatell_config` ADD `cfg_datetime_timezone` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_datetime_timezone` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `playsms_gatewayUplink_config` ADD `cfg_datetime_timezone` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_sms_max_count` TINYINT( 4 ) NOT NULL DEFAULT '3' ;
