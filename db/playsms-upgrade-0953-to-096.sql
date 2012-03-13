-- core

ALTER TABLE `playsms_tblUser` CHANGE `sender` `footer` VARCHAR( 30 ) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` CHANGE `mobile` `sender` VARCHAR( 16 ) NOT NULL DEFAULT '' ;
ALTER TABLE `playsms_tblUser` ADD `mobile` VARCHAR( 16 ) NOT NULL DEFAULT '' AFTER `email` ;
ALTER TABLE `playsms_tblUser` ADD `language_module` VARCHAR( 10 ) NOT NULL DEFAULT 'en_US' AFTER `datetime_timezone` ;

-- plugin: sms_poll

ALTER TABLE `playsms_featurePoll_log` ADD `in_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `poll_sender` ;

-- plugin: sendfromfile

DROP TABLE IF EXISTS `playsms_toolsSendfromfile` ;
CREATE TABLE `playsms_toolsSendfromfile` (
`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`sid` VARCHAR( 50 ) NOT NULL ,
`sms_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00',
`sms_to` VARCHAR( 50 ) NOT NULL ,
`sms_msg` TEXT NOT NULL ,
`sms_username` VARCHAR( 50 ) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- plugin: inboxgroup

DROP TABLE IF EXISTS `playsms_featureInboxgroup` ;
CREATE TABLE `playsms_featureInboxgroup` (
`c_timestamp` INT NOT NULL ,
`id` INT NOT NULL AUTO_INCREMENT ,
`uid` BIGINT NOT NULL ,
`in_receiver` VARCHAR( 20 ) NOT NULL ,
`keywords` VARCHAR( 100 ) NOT NULL ,
`description` VARCHAR( 250 ) NOT NULL ,
`creation_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`exclusive` TINYINT NOT NULL DEFAULT '0' ,
`deleted` TINYINT NOT NULL DEFAULT '0' ,
`status` TINYINT NOT NULL DEFAULT '0' ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_members` ;
CREATE TABLE `playsms_featureInboxgroup_members` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_catchall` ;
CREATE TABLE `playsms_featureInboxgroup_catchall` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_in` ;
CREATE TABLE `playsms_featureInboxgroup_log_in` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`sms_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00',
`sms_sender` VARCHAR( 20 ) NOT NULL ,
`keyword` VARCHAR( 100 ) NOT NULL ,
`message` TEXT NOT NULL ,
`sms_receiver` VARCHAR( 20 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_out` ;
CREATE TABLE `playsms_featureInboxgroup_log_out` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`log_in_id` BIGINT NOT NULL ,
`smslog_id` BIGINT NOT NULL ,
`catchall` TINYINT NOT NULL DEFAULT '0',
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- plugin: clickatell

UPDATE `playsms_gatewayClickatell_config` SET `cfg_send_url`='https://api.clickatell.com/http' ;

