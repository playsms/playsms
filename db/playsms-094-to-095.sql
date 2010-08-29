DROP TABLE IF EXISTS `playsms_tblRate`;
CREATE TABLE `playsms_tblRate` (
  `c_timestamp` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dst` varchar(100) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `rate` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `playsms_tblConfig_main` ADD `cfg_default_rate` FLOAT NOT NULL ;

