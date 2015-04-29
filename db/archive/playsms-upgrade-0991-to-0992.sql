ALTER TABLE  `playsms_tblSMSOutgoing` CHANGE  `smslog_id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE  `playsms_tblSMSOutgoing` ADD  `smslog_id` INT( 11 ) DEFAULT NULL AFTER  `id` ;
UPDATE `playsms_tblSMSOutgoing` SET `smslog_id` = `id` ;
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` AUTO_INCREMENT = 1000000 ;
ALTER TABLE  `playsms_tblSMSOutgoing` ADD UNIQUE (`smslog_id`) ;
ALTER TABLE `playsms_tblSMSOutgoing_queue_dst` DROP `smslog_id` ;

ALTER TABLE  `playsms_tblSMSOutgoing` CHANGE  `p_credit`  `p_credit` DECIMAL( 10,2 ) NOT NULL DEFAULT '0.0' ;
ALTER TABLE  `playsms_tblUser` CHANGE  `credit`  `credit` DECIMAL( 10,2 ) NOT NULL DEFAULT '0.0' ;
ALTER TABLE  `playsms_tblBilling` CHANGE  `credit`  `credit` DECIMAL( 10,2 ) NOT NULL DEFAULT '0.0' ;
ALTER TABLE  `playsms_tblBilling` CHANGE  `charge`  `charge` DECIMAL( 10,2 ) NOT NULL DEFAULT '0.0' ;

ALTER TABLE `playsms_gatewayMsgtoolbox` CHANGE `local_slid`  `local_smslog_id` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `playsms_gatewayMsgtoolbox` CHANGE `remote_slid`  `remote_smslog_id` VARCHAR( 40 ) NOT NULL DEFAULT '';
ALTER TABLE `playsms_gatewayNexmo` CHANGE `local_slid`  `local_smslog_id` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `playsms_gatewayNexmo` CHANGE `remote_slid`  `remote_smslog_id` VARCHAR( 40 ) NOT NULL DEFAULT '';
ALTER TABLE `playsms_gatewayTwilio` CHANGE `local_slid`  `local_smslog_id` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `playsms_gatewayTwilio` CHANGE `remote_slid`  `remote_smslog_id` VARCHAR( 40 ) NOT NULL DEFAULT '';
ALTER TABLE `playsms_gatewayUplink` CHANGE `up_local_slid`  `up_local_smslog_id` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `playsms_gatewayUplink` CHANGE `up_remote_slid`  `up_remote_smslog_id` VARCHAR( 40 ) NOT NULL DEFAULT '';

--
-- Table structure for table `playsms_tblSMSOutgoing_queue`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblSMSOutgoing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_code` varchar(40) NOT NULL DEFAULT '',
  `datetime_entry` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_scheduled` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_update` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `flag` int(11) NOT NULL DEFAULT '0',
  `sms_count` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `gpid` int(11) NOT NULL DEFAULT '0',
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `footer` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_type` varchar(100) NOT NULL DEFAULT '',
  `unicode` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_code` (`queue_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playsms_tblRecvSMS`
--

DROP TABLE IF EXISTS `playsms_tblRecvSMS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblRecvSMS` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_processed` tinyint(4) NOT NULL DEFAULT '0',
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playsms_tblDLR`
--

DROP TABLE IF EXISTS `playsms_tblDLR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_tblDLR` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flag_processed` tinyint(4) NOT NULL DEFAULT '0',
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `p_status` tinyint(4) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `playsms_gatewayInfobip_config`;
CREATE TABLE `playsms_gatewayInfobip_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'infobip',
  `cfg_username` varchar(100) NOT NULL DEFAULT '',
  `cfg_password` varchar(100) NOT NULL DEFAULT '',
  `cfg_sender` varchar(20) NOT NULL DEFAULT '',
  `cfg_send_url` varchar(250) NOT NULL DEFAULT '',
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) NOT NULL DEFAULT '',
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700',
  `cfg_dlr_nopush` varchar(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `playsms_gatewayInfobip_config` (`cfg_name`,`cfg_send_url`,`cfg_datetime_timezone`,`cfg_dlr_nopush`) VALUES ('infobip','http://api.infobip.com/api/v3','+0700','1') ;


DROP TABLE IF EXISTS `playsms_gatewayInfobip_apidata`;
CREATE TABLE `playsms_gatewayInfobip_apidata` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL DEFAULT '0',
  PRIMARY KEY (`apidata_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- beta2

ALTER TABLE  `playsms_tblConfig_main`
ADD  `cfg_allow_custom_sender` INT( 11 ) NOT NULL DEFAULT  '0',
ADD  `cfg_allow_custom_footer` INT( 11 ) NOT NULL DEFAULT  '0';


-- beta3

ALTER TABLE  `playsms_gatewayUplink_config` ADD  `cfg_try_disable_footer` INT(11) NOT NULL DEFAULT '0';


-- beta 4

--
-- Table structure for table `playsms_tblRegistry`
--

DROP TABLE IF EXISTS `playsms_tblRegistry`;
CREATE TABLE IF NOT EXISTS `playsms_tblRegistry` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `group` varchar(250) NOT NULL DEFAULT '',
  `family` varchar(250) NOT NULL DEFAULT '',
  `key` varchar(250) NOT NULL DEFAULT '',
  `value` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `playsms_featureSmssysnc`
--

DROP TABLE IF EXISTS `playsms_featureSmssysnc`;
CREATE TABLE IF NOT EXISTS `playsms_featureSmssysnc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `message_id` varchar(250) DEFAULT '',
  `recvsms_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- beta 5

ALTER TABLE  `playsms_tblConfig_main` ADD  `cfg_main_website_name` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE  `playsms_tblConfig_main` ADD  `cfg_main_website_url` varchar(250) NOT NULL DEFAULT '';


-- beta 6

ALTER TABLE  `playsms_tblRegistry`
CHANGE  `group`  `registry_group` VARCHAR( 250 ) NOT NULL DEFAULT  '',
CHANGE  `family`  `registry_family` VARCHAR( 250 ) NOT NULL DEFAULT  '',
CHANGE  `key`  `registry_key` VARCHAR( 250 ) NOT NULL DEFAULT  '',
CHANGE  `value`  `registry_value` VARCHAR( 250 ) NOT NULL DEFAULT  '' ;

