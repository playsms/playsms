ALTER TABLE `playsms_tblUserInbox` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;
ALTER TABLE `playsms_tblSMSIncoming` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;

ALTER TABLE `playsms_tblUser` ADD `fwd_to_mobile` TINYINT( 4 ) NOT NULL DEFAULT '0',
ADD `fwd_to_email` TINYINT( 4 ) NOT NULL DEFAULT '1',
ADD `fwd_to_inbox` TINYINT( 4 ) NOT NULL DEFAULT '1' ;

UPDATE `playsms_tblUser` SET `fwd_to_mobile`='0',`fwd_to_email`='1',`fwd_to_inbox`='1' ;

DROP TABLE IF EXISTS `playsms_featureSurvey` ;
CREATE TABLE `playsms_featureSurvey` (
`c_timestamp` INT NOT NULL ,
`id` INT NOT NULL AUTO_INCREMENT ,
`uid` INT NOT NULL ,
`creation_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`keyword` VARCHAR( 20 ) NOT NULL ,
`title` VARCHAR( 100 ) NOT NULL ,
`status` TINYINT NOT NULL,
`deleted` TINYINT NOT NULL,
`started` TINYINT NOT NULL,
`running` TINYINT NOT NULL,
`session` VARCHAR( 50 ) NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `playsms_featureSurvey_members` ;
CREATE TABLE `playsms_featureSurvey_members` (
`id` INT NOT NULL AUTO_INCREMENT ,
`sid` INT NOT NULL ,
`mobile` VARCHAR( 20 ) NOT NULL ,
`name` VARCHAR( 100 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `playsms_featureSurvey_questions` ;
CREATE TABLE `playsms_featureSurvey_questions` (
`id` INT NOT NULL AUTO_INCREMENT ,
`sid` INT NOT NULL ,
`question` VARCHAR( 140 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureSurvey_log` ;
CREATE TABLE `playsms_featureSurvey_log` (
`c_timestamp` INT NOT NULL ,
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`survey_id` INT NOT NULL ,
`question_id` INT NOT NULL ,
`member_id` INT NOT NULL ,
`link_id` VARCHAR( 50 ) NOT NULL ,
`smslog_id` BIGINT NOT NULL ,
`session` VARCHAR( 50 ) NOT NULL,
`creation_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`name` VARCHAR( 100 ) NOT NULL ,
`mobile` VARCHAR( 20 ) NOT NULL ,
`question_number` INT NOT NULL ,
`question` VARCHAR( 140 ) NOT NULL ,
`incoming` TINYINT NOT NULL ,
`in_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`in_sender` VARCHAR( 20 ) NOT NULL ,
`in_receiver` VARCHAR( 20 ) NOT NULL ,
`answer` text NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_gatewayMsgtoolbox_config`;
CREATE TABLE `playsms_gatewayMsgtoolbox_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) NOT NULL default 'msgtoolbox',
  `cfg_url` varchar(250) default NULL,
  `cfg_route` varchar(5) default NULL,
  `cfg_username` varchar(100) default NULL,
  `cfg_password` varchar(100) default NULL,
  `cfg_global_sender` varchar(20) default NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL default '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `playsms_gatewayMsgtoolbox_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayMsgtoolbox_config` VALUES (0,'msgtoolbox','http://serverX.msgtoolbox.com/api/current/send/message.php','1','playsms','password','playSMS','+0700');
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `playsms_gatewayMsgtoolbox` ;
CREATE TABLE `playsms_gatewayMsgtoolbox` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;



