ALTER TABLE `playsms_tblUserInbox` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;
ALTER TABLE `playsms_tblSMSIncoming` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;

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
`answer` text NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

