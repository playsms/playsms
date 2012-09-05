ALTER TABLE `playsms_featureCommand` ADD `command_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `command_exec` ;

ALTER TABLE `playsms_featureCustom` ADD `custom_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `custom_url` ;

ALTER TABLE `playsms_tblUser` ADD `replace_zero` varchar(5) NOT NULL DEFAULT '' AFTER `fwd_to_inbox` ;
ALTER TABLE `playsms_tblUser` ADD `plus_sign_remove` tinyint(4) NOT NULL DEFAULT '1' AFTER `replace_zero` ;
ALTER TABLE `playsms_tblUser` ADD `plus_sign_add` tinyint(4) NOT NULL DEFAULT '0' AFTER `plus_sign_remove` ;

ALTER TABLE `playsms_tblUser` DROP `dailysms` , DROP `gender` , DROP `age` , DROP `birthday` , DROP `marital` , DROP `education`, DROP `junktimestamp` ;

ALTER TABLE `playsms_tblBilling` ADD `count` int(11) NOT NULL DEFAULT '0' AFTER `credit` ;
ALTER TABLE `playsms_tblBilling` ADD `charge` double NOT NULL DEFAULT '0' AFTER `count` ;

ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_admin_url` varchar(250) NOT NULL ;
ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_admin_password` varchar(50) NOT NULL ;
ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_admin_port` int(11) NOT NULL ;
ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_dlr` int(11) NOT NULL DEFAULT '31' AFTER `cfg_playsms_web` ;

CREATE TABLE `playsms_tblSMSOutgoing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_code` varchar(40) NOT NULL,
  `datetime_entry` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_scheduled` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_update` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `flag` tinyint(4) NOT NULL,
  `uid` int(11) NOT NULL,
  `gpid` int(11) NOT NULL,
  `sender_id` varchar(100) NOT NULL,
  `footer` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `sms_type` varchar(100) NOT NULL,
  `unicode` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_code` (`queue_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `playsms_tblSMSOutgoing_queue_dst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `smslog_id` bigint(20) NOT NULL,
  `flag` tinyint(4) NOT NULL,
  `dst` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `playsms_gatewaySmstools_dlr` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `smslog_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `smslog_id` (`smslog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

