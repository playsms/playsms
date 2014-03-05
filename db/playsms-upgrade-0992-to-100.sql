-- 1.0-beta1

-- remove default timezone
ALTER TABLE  `playsms_tblUser` CHANGE  `datetime_timezone`  `datetime_timezone` VARCHAR( 30 ) NOT NULL DEFAULT  '';

-- remove default language
ALTER TABLE  `playsms_tblUser` CHANGE  `language_module`  `language_module` VARCHAR( 10 ) NOT NULL DEFAULT  '';

-- phonebook
DROP TABLE IF EXISTS `playsms_toolsPhonebook_group_contacts`;
CREATE TABLE IF NOT EXISTS `playsms_toolsPhonebook_group_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gpid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `playsms_toolsPhonebook` DROP `gpid` ;

-- core config
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES ('1', 'core', 'config', 'playsms_version', '1.0-beta1');


-- 1.0-beta2

-- core config
DELETE FROM `playsms_tblRegistry` WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES ('1', 'core', 'config', 'playsms_version', '1.0.0') ;


-- 1.0-beta3


-- 1.0.0

UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0.0' WHERE `id` = 1 ;
ALTER TABLE  `playsms_toolsPhonebook_group` ADD  `flag_sender` INT NOT NULL DEFAULT  '0' ;
DROP TABLE IF EXISTS `playsms_gatewayKannel_config` ;
ALTER TABLE  `playsms_tblUserInbox` CHANGE  `in_hidden`  `flag_deleted` INT( 11 ) NOT NULL DEFAULT  '0' ;
RENAME TABLE  `playsms_tblUserInbox` TO `playsms_tblUser_inbox` ;

DROP TABLE IF EXISTS `playsms_tblNotif`;
CREATE TABLE IF NOT EXISTS `playsms_tblNotif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `label` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` varchar(255) DEFAULT NULL,
  `flag_unread` int(11) NOT NULL DEFAULT '0',
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `playsms_tblConfig_main`;

DROP TABLE IF EXISTS `playsms_tblRegistry`;
CREATE TABLE IF NOT EXISTS `playsms_tblRegistry` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `registry_group` varchar(255) NOT NULL DEFAULT '',
  `registry_family` varchar(255) NOT NULL DEFAULT '',
  `registry_key` varchar(255) NOT NULL DEFAULT '',
  `registry_value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

INSERT INTO `playsms_tblRegistry` (`c_timestamp`, `id`, `uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES
(0, 1, 1, 'core', 'config', 'playsms_version', '1.0.0'),
(0, 2, 1, 'core', 'main_config', 'web_title', 'playSMS'),
(0, 3, 1, 'core', 'main_config', 'email_service', 'noreply@playsms.org'),
(0, 4, 1, 'core', 'main_config', 'email_footer', 'Powered by playSMS'),
(0, 5, 1, 'core', 'main_config', 'main_website_name', 'playSMS'),
(0, 6, 1, 'core', 'main_config', 'main_website_url', 'http://www.playsms.org'),
(0, 7, 1, 'core', 'main_config', 'gateway_number', '1234'),
(0, 8, 1, 'core', 'main_config', 'gateway_timezone', '+0700'),
(0, 9, 1, 'core', 'main_config', 'default_rate', '0'),
(0, 10, 1, 'core', 'main_config', 'gateway_module', 'dev'),
(0, 11, 1, 'core', 'main_config', 'themes_module', 'default'),
(0, 12, 1, 'core', 'main_config', 'language_module', 'en_US'),
(0, 13, 1, 'core', 'main_config', 'sms_max_count', '3'),
(0, 14, 1, 'core', 'main_config', 'default_credit', '0'),
(0, 15, 1, 'core', 'main_config', 'enable_register', '0'),
(0, 16, 1, 'core', 'main_config', 'enable_forgot', '1'),
(0, 17, 1, 'core', 'main_config', 'allow_custom_sender', '0'),
(0, 18, 1, 'core', 'main_config', 'allow_custom_footer', '0');
