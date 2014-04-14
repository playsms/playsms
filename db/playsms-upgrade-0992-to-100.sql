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


-- 1.0-beta4

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

INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','web_title',cfg_web_title FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','email_service',cfg_email_service FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','email_footer',cfg_email_footer FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','gateway_module',cfg_gateway_module FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','gateway_number',cfg_gateway_number FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','themes_module',cfg_themes_module FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','default_rate',cfg_default_rate FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','language_module',cfg_language_module FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','datetime_timezone',cfg_datetime_timezone FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','sms_max_count',cfg_sms_max_count FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','default_credit',cfg_default_credit FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','enable_register',cfg_enable_register FROM `playsms_tblConfig_main`;
INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) SELECT 1,'core','main_config','enable_forgot',cfg_enable_forgot FROM `playsms_tblConfig_main`;

INSERT INTO `playsms_tblRegistry` (`uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES
(1, 'core', 'main_config', 'main_website_name', 'playSMS'),
(1, 'core', 'main_config', 'main_website_url', 'http://www.playsms.org'),
(1, 'core', 'main_config', 'gateway_timezone', '+0700'),
(1, 'core', 'main_config', 'allow_custom_sender', '0'),
(1, 'core', 'main_config', 'allow_custom_footer', '0');

DROP TABLE IF EXISTS `playsms_tblConfig_main`;

DROP TABLE `playsms_featureSurvey`, `playsms_featureSurvey_log`, `playsms_featureSurvey_members`, `playsms_featureSurvey_questions`;

ALTER TABLE `playsms_tblUser` DROP `ticket` ;

ALTER TABLE `playsms_tblRegistry` CHANGE `registry_value` `registry_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

RENAME TABLE  `playsms_toolsSimplerate` TO `playsms_featureSimplerate` ;
RENAME TABLE  `playsms_toolsMsgtemplate` TO `playsms_featureMsgtemplate` ;
RENAME TABLE  `playsms_toolsSendfromfile` TO `playsms_featureSendfromfile` ;
RENAME TABLE  `playsms_toolsPhonebook` TO `playsms_featurePhonebook` ;
RENAME TABLE  `playsms_toolsPhonebook_group` TO `playsms_featurePhonebook_group` ;
RENAME TABLE  `playsms_toolsPhonebook_group_contacts` TO `playsms_featurePhonebook_group_contacts` ;

-- core config
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-beta4' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0.0
