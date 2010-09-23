DROP TABLE IF EXISTS `playsms_toolsSimplerate`;
CREATE TABLE `playsms_toolsSimplerate` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dst` varchar(100) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `rate` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `playsms_tblConfig_main` ADD `cfg_default_rate` FLOAT NOT NULL ;
ALTER TABLE `playsms_tblConfig_main` ADD `cfg_language_module` VARCHAR(10) DEFAULT 'en_US' ;

ALTER TABLE `playsms_tblUser` ADD `credit` DOUBLE NOT NULL ;

DROP TABLE IF EXISTS `playsms_tblErrorString`;
CREATE TABLE `playsms_tblErrorString` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `error_string` TEXT NOT NULL ,
  PRIMARY KEY ( `id` )
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_tblBilling`;
CREATE TABLE `playsms_tblBilling` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `c_timestamp` INT NOT NULL ,
  `post_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `smslog_id` INT NOT NULL ,
  `rate` FLOAT NOT NULL ,
  `credit` DOUBLE NOT NULL ,
  `status` TINYINT NOT NULL ,
  PRIMARY KEY ( `id` )
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

