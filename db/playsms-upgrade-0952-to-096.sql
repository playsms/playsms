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
`completed` TINYINT NOT NULL,
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
