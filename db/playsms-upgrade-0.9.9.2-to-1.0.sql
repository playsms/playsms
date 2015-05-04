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

INSERT INTO `playsms_toolsPhonebook_group_contacts` (`id`,`gpid`,`pid`)
  SELECT '',gpid,id  
  FROM playsms_toolsPhonebook;

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


-- 1.0-beta5


-- core config
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-beta5' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

ALTER TABLE `playsms_tblUser` ADD `parent_uid` INT(11) NOT NULL DEFAULT '0' AFTER `c_timestamp`;

--
-- Table structure for table `playsms_featureCredit`
--

DROP TABLE IF EXISTS `playsms_featureCredit`;
CREATE TABLE `playsms_featureCredit` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_uid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(100) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `create_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `delete_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flag_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `playsms_tblUser_country`;

--
-- Table structure for table `playsms_tblCountry`
--

DROP TABLE IF EXISTS `playsms_tblCountry`;
CREATE TABLE `playsms_tblCountry` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(200) NOT NULL DEFAULT '',
  `country_code` varchar(10) NOT NULL DEFAULT '',
  `country_prefix` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=206 ;

--
-- Dumping data for table `playsms_tblCountry`
--

INSERT INTO `playsms_tblCountry` (`c_timestamp`, `country_id`, `country_name`, `country_code`, `country_prefix`) VALUES
(0, 1, 'Afghanistan', 'af', '93'),
(0, 2, 'Albania', 'al', '355'),
(0, 3, 'Algeria', 'dz', '213'),
(0, 4, 'Andorra', 'ad', '376'),
(0, 5, 'Angola', 'ao', '244'),
(0, 6, 'Antarctica', 'aq', '672'),
(0, 7, 'Argentina', 'ar', '54'),
(0, 8, 'Armenia', 'am', '374'),
(0, 9, 'Aruba', 'aw', '297'),
(0, 10, 'Australia', 'au', '61'),
(0, 11, 'Austria', 'at', '43'),
(0, 12, 'Azerbaijan', 'az', '994'),
(0, 13, 'Bahrain', 'bh', '973'),
(0, 14, 'Bangladesh', 'bd', '880'),
(0, 15, 'Belarus', 'by', '375'),
(0, 16, 'Belgium', 'be', '32'),
(0, 17, 'Belize', 'bz', '501'),
(0, 18, 'Benin', 'bj', '229'),
(0, 19, 'Bhutan', 'bt', '975'),
(0, 20, 'Bolivia, Plurinational State Of', 'bo', '591'),
(0, 21, 'Bosnia And Herzegovina', 'ba', '387'),
(0, 22, 'Botswana', 'bw', '267'),
(0, 23, 'Brazil', 'br', '55'),
(0, 24, 'Brunei Darussalam', 'bn', '673'),
(0, 25, 'Bulgaria', 'bg', '359'),
(0, 26, 'Burkina Faso', 'bf', '226'),
(0, 27, 'Myanmar', 'mm', '95'),
(0, 28, 'Burundi', 'bi', '257'),
(0, 29, 'Cambodia', 'kh', '855'),
(0, 30, 'Cameroon', 'cm', '237'),
(0, 31, 'Canada', 'ca', '1'),
(0, 32, 'Cape Verde', 'cv', '238'),
(0, 33, 'Central African Republic', 'cf', '236'),
(0, 34, 'Chad', 'td', '235'),
(0, 35, 'Chile', 'cl', '56'),
(0, 36, 'China', 'cn', '86'),
(0, 37, 'Christmas Island', 'cx', '61'),
(0, 38, 'Cocos (keeling) Islands', 'cc', '61'),
(0, 39, 'Colombia', 'co', '57'),
(0, 40, 'Comoros', 'km', '269'),
(0, 41, 'Congo', 'cg', '242'),
(0, 42, 'Congo, The Democratic Republic Of The', 'cd', '243'),
(0, 43, 'Cook Islands', 'ck', '682'),
(0, 44, 'Costa Rica', 'cr', '506'),
(0, 45, 'Croatia', 'hr', '385'),
(0, 46, 'Cuba', 'cu', '53'),
(0, 47, 'Cyprus', 'cy', '357'),
(0, 48, 'Czech Republic', 'cz', '420'),
(0, 49, 'Denmark', 'dk', '45'),
(0, 50, 'Djibouti', 'dj', '253'),
(0, 51, 'Timor-leste', 'tl', '670'),
(0, 52, 'Ecuador', 'ec', '593'),
(0, 53, 'Egypt', 'eg', '20'),
(0, 54, 'El Salvador', 'sv', '503'),
(0, 55, 'Equatorial Guinea', 'gq', '240'),
(0, 56, 'Eritrea', 'er', '291'),
(0, 57, 'Estonia', 'ee', '372'),
(0, 58, 'Ethiopia', 'et', '251'),
(0, 59, 'Falkland Islands (malvinas)', 'fk', '500'),
(0, 60, 'Faroe Islands', 'fo', '298'),
(0, 61, 'Fiji', 'fj', '679'),
(0, 62, 'Finland', 'fi', '358'),
(0, 63, 'France', 'fr', '33'),
(0, 64, 'French Polynesia', 'pf', '689'),
(0, 65, 'Gabon', 'ga', '241'),
(0, 66, 'Gambia', 'gm', '220'),
(0, 67, 'Georgia', 'ge', '995'),
(0, 68, 'Germany', 'de', '49'),
(0, 69, 'Ghana', 'gh', '233'),
(0, 70, 'Gibraltar', 'gi', '350'),
(0, 71, 'Greece', 'gr', '30'),
(0, 72, 'Greenland', 'gl', '299'),
(0, 73, 'Guatemala', 'gt', '502'),
(0, 74, 'Guinea', 'gn', '224'),
(0, 75, 'Guinea-bissau', 'gw', '245'),
(0, 76, 'Guyana', 'gy', '592'),
(0, 77, 'Haiti', 'ht', '509'),
(0, 78, 'Honduras', 'hn', '504'),
(0, 79, 'Hong Kong', 'hk', '852'),
(0, 80, 'Hungary', 'hu', '36'),
(0, 81, 'India', 'in', '91'),
(0, 82, 'Indonesia', 'id', '62'),
(0, 83, 'Iran, Islamic Republic Of', 'ir', '98'),
(0, 84, 'Iraq', 'iq', '964'),
(0, 85, 'Ireland', 'ie', '353'),
(0, 86, 'Isle Of Man', 'im', '44'),
(0, 87, 'Israel', 'il', '972'),
(0, 88, 'Italy', 'it', '39'),
(0, 89, 'Côte D''ivoire', 'ci', '225'),
(0, 90, 'Japan', 'jp', '81'),
(0, 91, 'Jordan', 'jo', '962'),
(0, 92, 'Kazakhstan', 'kz', '7'),
(0, 93, 'Kenya', 'ke', '254'),
(0, 94, 'Kiribati', 'ki', '686'),
(0, 95, 'Kuwait', 'kw', '965'),
(0, 96, 'Kyrgyzstan', 'kg', '996'),
(0, 97, 'Lao People''s Democratic Republic', 'la', '856'),
(0, 98, 'Latvia', 'lv', '371'),
(0, 99, 'Lebanon', 'lb', '961'),
(0, 100, 'Lesotho', 'ls', '266'),
(0, 101, 'Liberia', 'lr', '231'),
(0, 102, 'Libya', 'ly', '218'),
(0, 103, 'Liechtenstein', 'li', '423'),
(0, 104, 'Lithuania', 'lt', '370'),
(0, 105, 'Luxembourg', 'lu', '352'),
(0, 106, 'Macao', 'mo', '853'),
(0, 107, 'Macedonia, The Former Yugoslav Republic Of', 'mk', '389'),
(0, 108, 'Madagascar', 'mg', '261'),
(0, 109, 'Malawi', 'mw', '265'),
(0, 110, 'Malaysia', 'my', '60'),
(0, 111, 'Maldives', 'mv', '960'),
(0, 112, 'Mali', 'ml', '223'),
(0, 113, 'Malta', 'mt', '356'),
(0, 114, 'Marshall Islands', 'mh', '692'),
(0, 115, 'Mauritania', 'mr', '222'),
(0, 116, 'Mauritius', 'mu', '230'),
(0, 117, 'Mayotte', 'yt', '262'),
(0, 118, 'Mexico', 'mx', '52'),
(0, 119, 'Micronesia, Federated States Of', 'fm', '691'),
(0, 120, 'Moldova, Republic Of', 'md', '373'),
(0, 121, 'Monaco', 'mc', '377'),
(0, 122, 'Mongolia', 'mn', '976'),
(0, 123, 'Montenegro', 'me', '382'),
(0, 124, 'Morocco', 'ma', '212'),
(0, 125, 'Mozambique', 'mz', '258'),
(0, 126, 'Namibia', 'na', '264'),
(0, 127, 'Nauru', 'nr', '674'),
(0, 128, 'Nepal', 'np', '977'),
(0, 129, 'Netherlands', 'nl', '31'),
(0, 130, 'New Caledonia', 'nc', '687'),
(0, 131, 'New Zealand', 'nz', '64'),
(0, 132, 'Nicaragua', 'ni', '505'),
(0, 133, 'Niger', 'ne', '227'),
(0, 134, 'Nigeria', 'ng', '234'),
(0, 135, 'Niue', 'nu', '683'),
(0, 136, 'Korea, Democratic People''s Republic Of', 'kp', '850'),
(0, 137, 'Norway', 'no', '47'),
(0, 138, 'Oman', 'om', '968'),
(0, 139, 'Pakistan', 'pk', '92'),
(0, 140, 'Palau', 'pw', '680'),
(0, 141, 'Panama', 'pa', '507'),
(0, 142, 'Papua New Guinea', 'pg', '675'),
(0, 143, 'Paraguay', 'py', '595'),
(0, 144, 'Peru', 'pe', '51'),
(0, 145, 'Philippines', 'ph', '63'),
(0, 146, 'Pitcairn', 'pn', '870'),
(0, 147, 'Poland', 'pl', '48'),
(0, 148, 'Portugal', 'pt', '351'),
(0, 149, 'Puerto Rico', 'pr', '1'),
(0, 150, 'Qatar', 'qa', '974'),
(0, 151, 'Romania', 'ro', '40'),
(0, 152, 'Russian Federation', 'ru', '7'),
(0, 153, 'Rwanda', 'rw', '250'),
(0, 154, 'Saint Barthélemy', 'bl', '590'),
(0, 155, 'Samoa', 'ws', '685'),
(0, 156, 'San Marino', 'sm', '378'),
(0, 157, 'Sao Tome And Principe', 'st', '239'),
(0, 158, 'Saudi Arabia', 'sa', '966'),
(0, 159, 'Senegal', 'sn', '221'),
(0, 160, 'Serbia', 'rs', '381'),
(0, 161, 'Seychelles', 'sc', '248'),
(0, 162, 'Sierra Leone', 'sl', '232'),
(0, 163, 'Singapore', 'sg', '65'),
(0, 164, 'Slovakia', 'sk', '421'),
(0, 165, 'Slovenia', 'si', '386'),
(0, 166, 'Solomon Islands', 'sb', '677'),
(0, 167, 'Somalia', 'so', '252'),
(0, 168, 'South Africa', 'za', '27'),
(0, 169, 'Korea, Republic Of', 'kr', '82'),
(0, 170, 'Spain', 'es', '34'),
(0, 171, 'Sri Lanka', 'lk', '94'),
(0, 172, 'Saint Helena, Ascension And Tristan Da Cunha', 'sh', '290'),
(0, 173, 'Saint Pierre And Miquelon', 'pm', '508'),
(0, 174, 'Sudan', 'sd', '249'),
(0, 175, 'Suriname', 'sr', '597'),
(0, 176, 'Swaziland', 'sz', '268'),
(0, 177, 'Sweden', 'se', '46'),
(0, 178, 'Switzerland', 'ch', '41'),
(0, 179, 'Syrian Arab Republic', 'sy', '963'),
(0, 180, 'Taiwan, Province Of China', 'tw', '886'),
(0, 181, 'Tajikistan', 'tj', '992'),
(0, 182, 'Tanzania, United Republic Of', 'tz', '255'),
(0, 183, 'Thailand', 'th', '66'),
(0, 184, 'Togo', 'tg', '228'),
(0, 185, 'Tokelau', 'tk', '690'),
(0, 186, 'Tonga', 'to', '676'),
(0, 187, 'Tunisia', 'tn', '216'),
(0, 188, 'Turkey', 'tr', '90'),
(0, 189, 'Turkmenistan', 'tm', '993'),
(0, 190, 'Tuvalu', 'tv', '688'),
(0, 191, 'United Arab Emirates', 'ae', '971'),
(0, 192, 'Uganda', 'ug', '256'),
(0, 193, 'United Kingdom', 'gb', '44'),
(0, 194, 'Ukraine', 'ua', '380'),
(0, 195, 'Uruguay', 'uy', '598'),
(0, 196, 'United States', 'us', '1'),
(0, 197, 'Uzbekistan', 'uz', '998'),
(0, 198, 'Vanuatu', 'vu', '678'),
(0, 199, 'Holy See (vatican City State)', 'va', '39'),
(0, 200, 'Venezuela, Bolivarian Republic Of', 've', '58'),
(0, 201, 'Viet Nam', 'vn', '84'),
(0, 202, 'Wallis And Futuna', 'wf', '681'),
(0, 203, 'Yemen', 'ye', '967'),
(0, 204, 'Zambia', 'zm', '260'),
(0, 205, 'Zimbabwe', 'zw', '263');


-- 1.0-rc1


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc1' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- saving money/credit in database
ALTER TABLE `playsms_featureSimplerate` CHANGE `rate` `rate` DECIMAL(13,3) NOT NULL DEFAULT '0.000';

ALTER TABLE `playsms_tblUser` CHANGE `credit` `credit` DECIMAL(13,3) NOT NULL DEFAULT '0.000';

ALTER TABLE `playsms_tblBilling` CHANGE `rate` `rate` DECIMAL(13,3) NOT NULL DEFAULT '0.000', 
CHANGE `credit` `credit` DECIMAL(13,3) NOT NULL DEFAULT '0.000', 
CHANGE `charge` `charge` DECIMAL(13,3) NOT NULL DEFAULT '0.000';

ALTER TABLE `playsms_featureCredit` CHANGE `amount` `amount` DECIMAL(13,3) NOT NULL DEFAULT '0.000', 
CHANGE `balance` `balance` DECIMAL(13,3) NOT NULL DEFAULT '0.000';

ALTER TABLE `playsms_tblSMSOutgoing` CHANGE `p_credit` `p_credit` DECIMAL(13,3) NOT NULL DEFAULT '0.000';


-- 1.0-rc2


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc2' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc3


-- SMS poll
ALTER TABLE `playsms_featurePoll`
  DROP `poll_msg_valid`,
  DROP `poll_msg_invalid`;

ALTER TABLE `playsms_featurePoll` 
  CHANGE `poll_message_valid` `poll_message_valid` TEXT NOT NULL DEFAULT '', 
  CHANGE `poll_message_invalid` `poll_message_invalid` TEXT NOT NULL DEFAULT '';

ALTER TABLE `playsms_featurePoll` ADD `poll_option_vote` int(11) NOT NULL DEFAULT '0' AFTER `poll_enable`;

ALTER TABLE `playsms_featurePoll` ADD `poll_message_option` TEXT NOT NULL DEFAULT '';

ALTER TABLE `playsms_featurePoll_log` CHANGE `result_id` `log_id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePoll_log` ADD `status` int(11) NOT NULL DEFAULT '0';
UPDATE `playsms_featurePoll_log` SET `status` = '1';

ALTER TABLE `playsms_featurePoll` ADD `poll_access_code` VARCHAR(40) NOT NULL DEFAULT '';

ALTER TABLE `playsms_tblUser_inbox` ADD `reference_id` VARCHAR(40) NOT NULL DEFAULT '' ;

DROP TABLE IF EXISTS `playsms_featureOutgoing` ;
CREATE TABLE `playsms_featureOutgoing` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dst` varchar(100) NOT NULL DEFAULT '',
  `prefix` varchar(10) NOT NULL DEFAULT '',
  `gateway` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `playsms_tblRecvSMS` ADD `gw` VARCHAR(100) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_tblSMSOutgoing_queue` ADD `gw` VARCHAR(100) NOT NULL DEFAULT '' ;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc4


--
-- Table structure for table `playsms_tblGateway`
--

DROP TABLE IF EXISTS `playsms_tblGateway`;
CREATE TABLE `playsms_tblGateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL DEFAULT '',
  `gateway` varchar(100) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `playsms_tblGateway`
--

INSERT INTO `playsms_tblGateway` (`id`, `created`, `last_update`, `name`, `gateway`, `data`) VALUES
(1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'blocked', 'blocked', '[]'),
(2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'dev', 'dev', '[]');

ALTER TABLE `playsms_tblRecvSMS` CHANGE `gw` `smsc` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `playsms_tblSMSOutgoing_queue` CHANGE `gw` `smsc` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `playsms_featureOutgoing` CHANGE `gateway` `smsc` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `playsms_gatewayMsgtoolbox_config` CHANGE `cfg_global_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `playsms_gatewayNexmo_config` CHANGE `cfg_global_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `playsms_gatewayTwilio_config` CHANGE `cfg_global_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `playsms_gatewayUplink_config` CHANGE `cfg_global_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `playsms_gatewayClickatell_config` CHANGE `cfg_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `playsms_gatewayInfobip_config` CHANGE `cfg_sender` `cfg_module_sender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `playsms_featureOutgoing` DROP INDEX `prefix`;

ALTER TABLE `playsms_featureOutgoing` ADD `uid` INT(11) NOT NULL DEFAULT '0' AFTER `id`;

ALTER TABLE `playsms_featurePoll` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureAutoreply` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureSubscribe` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureQuiz` ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureSubscribe` ADD `duration` INT(11) NOT NULL DEFAULT '0' ;

ALTER TABLE `playsms_featureSubscribe` ADD `expire_msg` VARCHAR(140) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureBoard` 
ADD `board_access_code` VARCHAR(40) NOT NULL DEFAULT '' , 
ADD `board_reply_msg` VARCHAR(140) NOT NULL DEFAULT '' , 
ADD `smsc` VARCHAR(100) NOT NULL DEFAULT '' ;
-- 1.0-rc5


--
-- Table structure for table `playsms_featureFirewall`
--

DROP TABLE IF EXISTS `playsms_featureFirewall`;
CREATE TABLE `playsms_featureFirewall` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `ip_address` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

ALTER TABLE `playsms_featurePhonebook` ADD INDEX ( `uid` ) ;
ALTER TABLE `playsms_featurePhonebook` ADD INDEX ( `mobile` ) ;

ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `uid` ) ;
ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `flag_sender` ) ;
ALTER TABLE `playsms_featurePhonebook_group` ADD INDEX ( `code` ) ;

ALTER TABLE `playsms_featurePhonebook_group_contacts` ADD INDEX ( `gpid` ) ;
ALTER TABLE `playsms_featurePhonebook_group_contacts` ADD INDEX ( `pid` ) ;

--
-- Table structure for table `playsms_featureSchedule`
--

DROP TABLE IF EXISTS `playsms_featureSchedule`;
CREATE TABLE `playsms_featureSchedule` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `schedule_rule` int(11) NOT NULL DEFAULT '0',
  `flag_active` int(11) NOT NULL DEFAULT '0',
  `flag_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `playsms_featureSchedule_dst`
--

DROP TABLE IF EXISTS `playsms_featureSchedule_dst`;
CREATE TABLE `playsms_featureSchedule_dst` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL DEFAULT '0',
  `schedule` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL DEFAULT '',
  `destination` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `playsms_tblACL`
--

DROP TABLE IF EXISTS `playsms_tblACL`;
CREATE TABLE `playsms_tblACL` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `acl_subuser` varchar(250) NOT NULL DEFAULT '',
  `url` text NOT NULL,
  `flag_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `playsms_tblACL`
--

INSERT INTO `playsms_tblACL` (`c_timestamp`, `id`, `name`, `acl_subuser`, `url`, `flag_deleted`) VALUES
(0, 1, 'BROADCAST', '', 'inc=core_sendsms,\r\ninc=core_user,\r\ninc=feature_report,\r\ninc=feature_schedule,\r\ninc=feature_msgtemplate,\r\ninc=feature_queuelog,\r\ninc=feature_credit,\r\ninc=feature_report&route=user\r\n', 0);

ALTER TABLE `playsms_tblUser` ADD `acl_id` INT NOT NULL DEFAULT '0' AFTER `status`;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc5' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc6

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc6' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


--
-- Table structure for table `playsms_gatewayTelerivet`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` varchar(40) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `phone_id` varchar(40) NOT NULL DEFAULT '',
  `message_type` varchar(20) NOT NULL DEFAULT '',
  `source` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet`
--

LOCK TABLES `playsms_gatewayTelerivet` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTelerivet_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'telerivet',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_api_key` varchar(250) DEFAULT NULL,
  `cfg_project_id` varchar(250) DEFAULT NULL,
  `cfg_status_url` varchar(250) DEFAULT NULL,
  `cfg_status_secret` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet_config`
--

LOCK TABLES `playsms_gatewayTelerivet_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTelerivet_config` VALUES (0,'telerivet','https://api.telerivet.com/','12345678','abc123cde456','https://localhost/plugin/gateway/telerivet/callback.php','myS3cr3t');
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` ENABLE KEYS */;
UNLOCK TABLES;


-- 1.0-rc7


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

RENAME TABLE `playsms_tblUser_inbox` TO `playsms_tblSMSInbox` ;

ALTER TABLE `playsms_tblUser` ADD COLUMN `flag_deleted` INT(11) NOT NULL AFTER `lastupdate_datetime`;

ALTER TABLE `playsms_featurePhonebook` ADD `username` VARCHAR( 100 ) NOT NULL DEFAULT '';

ALTER TABLE `playsms_featurePhonebook` CHANGE `username` `tags` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

--
-- Table structure for table `playsms_gatewayBulksms_apidata`
--
DROP TABLE IF EXISTS `playsms_gatewayBulksms_apidata`;

CREATE TABLE `playsms_gatewayBulksms_apidata` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`apidata_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `playsms_gatewayBulksms_apidata` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_apidata` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_apidata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayBulksms_config`
--
DROP TABLE IF EXISTS `playsms_gatewayBulksms_config`;

CREATE TABLE `playsms_gatewayBulksms_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'bulksms',
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_module_sender` varchar(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayBulksms_config`
--
LOCK TABLES `playsms_gatewayBulksms_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayBulksms_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayBulksms_config` VALUES (0,'bulksms','playsms','playsms','PlaySMS','http://bulksms.vsms.net:5567/eapi','/var/spool/playsms',5,'','');
/*!40000 ALTER TABLE `playsms_gatewayBulksms_config` ENABLE KEYS */;
UNLOCK TABLES;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc7' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc8


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-master' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

ALTER TABLE `playsms_tblSMSOutgoing` CHANGE `p_gateway` `p_smsc` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `playsms_tblSMSOutgoing` ADD `p_gateway` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `uid`;

--
-- Table structure for table `playsms_featureStoplist`
--

DROP TABLE IF EXISTS `playsms_featureStoplist`;
CREATE TABLE `playsms_featureStoplist` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc8' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0-rc9


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc9' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- 1.0


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


-- phonebook
DROP INDEX `uid` ON `playsms_featurePhonebook` ;
DROP INDEX `mobile` ON `playsms_featurePhonebook` ;
DROP INDEX `uid_mobile` ON `playsms_featurePhonebook` ;

DROP INDEX `uid` ON `playsms_featurePhonebook_group` ;
DROP INDEX `flag_sender` ON `playsms_featurePhonebook_group` ;
DROP INDEX `code` ON `playsms_featurePhonebook_group` ;

DROP INDEX `pid` ON `playsms_featurePhonebook_group_contacts` ;
DROP INDEX `gpid` ON `playsms_featurePhonebook_group_contacts` ;

CREATE INDEX `pid` ON `playsms_featurePhonebook_group_contacts` (`pid`) ;
CREATE INDEX `gpid` ON `playsms_featurePhonebook_group_contacts` (`gpid`) ;

CREATE INDEX `uid` on `playsms_tblSMSOutgoing` (`uid`);
CREATE INDEX `in_uid` on `playsms_tblSMSIncoming` (`in_uid`);
CREATE INDEX `in_uid` on `playsms_tblSMSInbox` (`in_uid`);
