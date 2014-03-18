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

INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES
(1, 'core', 'main_config', 'web_title', 'playSMS'),
(1, 'core', 'main_config', 'email_service', 'noreply@playsms.org'),
(1, 'core', 'main_config', 'email_footer', 'Powered by playSMS'),
(1, 'core', 'main_config', 'main_website_name', 'playSMS'),
(1, 'core', 'main_config', 'main_website_url', 'http://www.playsms.org'),
(1, 'core', 'main_config', 'gateway_number', '1234'),
(1, 'core', 'main_config', 'gateway_timezone', '+0700'),
(1, 'core', 'main_config', 'default_rate', '0'),
(1, 'core', 'main_config', 'gateway_module', 'dev'),
(1, 'core', 'main_config', 'themes_module', 'default'),
(1, 'core', 'main_config', 'language_module', 'en_US'),
(1, 'core', 'main_config', 'sms_max_count', '3'),
(1, 'core', 'main_config', 'default_credit', '0'),
(1, 'core', 'main_config', 'enable_register', '0'),
(1, 'core', 'main_config', 'enable_forgot', '1'),
(1, 'core', 'main_config', 'allow_custom_sender', '0'),
(1, 'core', 'main_config', 'allow_custom_footer', '0');

DROP TABLE `playsms_featureSurvey`, `playsms_featureSurvey_log`, `playsms_featureSurvey_members`, `playsms_featureSurvey_questions`;

ALTER TABLE `playsms_tblUser` DROP `ticket` ;
