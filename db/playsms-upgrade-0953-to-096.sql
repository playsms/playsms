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

