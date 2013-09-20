DROP TABLE IF EXISTS `playsms_gatewayInfobip_config`;
CREATE TABLE `playsms_gatewayInfobip_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'clickatell',
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_sender` varchar(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700',
  `cfg_dlr_nopush` varchar(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_gatewayInfobip_apidata`;
CREATE TABLE `playsms_gatewayInfobip_apidata` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL,
  PRIMARY KEY (`apidata_id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
