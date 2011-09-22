/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `playsms_featureAutoreply`
--

DROP TABLE IF EXISTS `playsms_featureAutoreply`;
CREATE TABLE `playsms_featureAutoreply` (
  `c_timestamp` int(11) NOT NULL default '0',
  `autoreply_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `autoreply_keyword` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`autoreply_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureAutoreply`
--

LOCK TABLES `playsms_featureAutoreply` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutoreply` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutoreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureAutoreply_log`
--

DROP TABLE IF EXISTS `playsms_featureAutoreply_log`;
CREATE TABLE `playsms_featureAutoreply_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `autoreply_log_id` int(11) NOT NULL auto_increment,
  `sms_sender` varchar(20) NOT NULL default '',
  `autoreply_log_datetime` varchar(20) NOT NULL default '',
  `autoreply_log_keyword` varchar(10) NOT NULL default '',
  `autoreply_log_request` text NOT NULL,
  PRIMARY KEY  (`autoreply_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureAutoreply_log`
--

LOCK TABLES `playsms_featureAutoreply_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutoreply_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutoreply_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureAutoreply_scenario`
--

DROP TABLE IF EXISTS `playsms_featureAutoreply_scenario`;
CREATE TABLE `playsms_featureAutoreply_scenario` (
  `c_timestamp` int(11) NOT NULL default '0',
  `autoreply_scenario_id` int(11) NOT NULL auto_increment,
  `autoreply_id` int(11) NOT NULL default '0',
  `autoreply_scenario_param1` varchar(20) NOT NULL default '',
  `autoreply_scenario_param2` varchar(20) NOT NULL default '',
  `autoreply_scenario_param3` varchar(20) NOT NULL default '',
  `autoreply_scenario_param4` varchar(20) NOT NULL default '',
  `autoreply_scenario_param5` varchar(20) NOT NULL default '',
  `autoreply_scenario_param6` varchar(20) NOT NULL default '',
  `autoreply_scenario_param7` varchar(20) NOT NULL default '',
  `autoreply_scenario_result` text NOT NULL,
  PRIMARY KEY  (`autoreply_scenario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureAutoreply_scenario`
--

LOCK TABLES `playsms_featureAutoreply_scenario` WRITE;
/*!40000 ALTER TABLE `playsms_featureAutoreply_scenario` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureAutoreply_scenario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureBoard`
--

DROP TABLE IF EXISTS `playsms_featureBoard`;
CREATE TABLE `playsms_featureBoard` (
  `c_timestamp` int(11) NOT NULL default '0',
  `board_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `board_keyword` varchar(100) NOT NULL default '',
  `board_forward_email` varchar(250) NOT NULL default '',
  `board_pref_template` text NOT NULL,
  PRIMARY KEY  (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureBoard`
--

LOCK TABLES `playsms_featureBoard` WRITE;
/*!40000 ALTER TABLE `playsms_featureBoard` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureBoard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureBoard_log`
--

DROP TABLE IF EXISTS `playsms_featureBoard_log`;
CREATE TABLE `playsms_featureBoard_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `in_id` int(11) NOT NULL auto_increment,
  `in_gateway` varchar(100) NOT NULL default '',
  `in_sender` varchar(20) NOT NULL default '',
  `in_masked` varchar(20) NOT NULL default '',
  `in_keyword` varchar(20) NOT NULL default '',
  `in_msg` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureBoard_log`
--

LOCK TABLES `playsms_featureBoard_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureBoard_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureBoard_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCommand`
--

DROP TABLE IF EXISTS `playsms_featureCommand`;
CREATE TABLE `playsms_featureCommand` (
  `c_timestamp` int(11) NOT NULL default '0',
  `command_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `command_keyword` varchar(10) NOT NULL default '',
  `command_exec` text NOT NULL,
  PRIMARY KEY  (`command_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureCommand`
--

LOCK TABLES `playsms_featureCommand` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCommand_log`
--

DROP TABLE IF EXISTS `playsms_featureCommand_log`;
CREATE TABLE `playsms_featureCommand_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `command_log_id` int(11) NOT NULL auto_increment,
  `sms_sender` varchar(20) NOT NULL default '',
  `command_log_datetime` varchar(20) NOT NULL default '',
  `command_log_keyword` varchar(10) NOT NULL default '',
  `command_log_exec` text NOT NULL,
  PRIMARY KEY  (`command_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureCommand_log`
--

LOCK TABLES `playsms_featureCommand_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureCommand_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCommand_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCustom`
--

DROP TABLE IF EXISTS `playsms_featureCustom`;
CREATE TABLE `playsms_featureCustom` (
  `c_timestamp` int(11) NOT NULL default '0',
  `custom_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `custom_keyword` varchar(10) NOT NULL default '',
  `custom_url` text NOT NULL,
  PRIMARY KEY  (`custom_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureCustom`
--

LOCK TABLES `playsms_featureCustom` WRITE;
/*!40000 ALTER TABLE `playsms_featureCustom` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCustom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featureCustom_log`
--

DROP TABLE IF EXISTS `playsms_featureCustom_log`;
CREATE TABLE `playsms_featureCustom_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `custom_log_id` int(11) NOT NULL auto_increment,
  `sms_sender` varchar(20) NOT NULL default '',
  `custom_log_datetime` varchar(20) NOT NULL default '',
  `custom_log_keyword` varchar(10) NOT NULL default '',
  `custom_log_url` text NOT NULL,
  PRIMARY KEY  (`custom_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featureCustom_log`
--

LOCK TABLES `playsms_featureCustom_log` WRITE;
/*!40000 ALTER TABLE `playsms_featureCustom_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCustom_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll`
--

DROP TABLE IF EXISTS `playsms_featurePoll`;
CREATE TABLE `playsms_featurePoll` (
  `c_timestamp` int(11) NOT NULL default '0',
  `poll_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `poll_title` varchar(250) NOT NULL default '',
  `poll_keyword` varchar(10) NOT NULL default '',
  `poll_enable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`poll_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featurePoll`
--

LOCK TABLES `playsms_featurePoll` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll_choice`
--

DROP TABLE IF EXISTS `playsms_featurePoll_choice`;
CREATE TABLE `playsms_featurePoll_choice` (
  `c_timestamp` int(11) NOT NULL default '0',
  `choice_id` int(11) NOT NULL auto_increment,
  `poll_id` int(11) NOT NULL default '0',
  `choice_title` varchar(250) NOT NULL default '',
  `choice_keyword` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`choice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featurePoll_choice`
--

LOCK TABLES `playsms_featurePoll_choice` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_featurePoll_log`
--

DROP TABLE IF EXISTS `playsms_featurePoll_log`;
CREATE TABLE `playsms_featurePoll_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `result_id` int(11) NOT NULL auto_increment,
  `poll_id` int(11) NOT NULL default '0',
  `choice_id` int(11) NOT NULL default '0',
  `poll_sender` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`result_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_featurePoll_log`
--

LOCK TABLES `playsms_featurePoll_log` WRITE;
/*!40000 ALTER TABLE `playsms_featurePoll_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featurePoll_log` ENABLE KEYS */;
UNLOCK TABLES;


-- Table structure for table `playsms_featureQuiz`
-- 

DROP TABLE IF EXISTS `playsms_featureQuiz`;
CREATE TABLE IF NOT EXISTS `playsms_featureQuiz` (
  `c_timestamp` int(11) NOT NULL default '0',
  `quiz_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `quiz_keyword` varchar(20) NOT NULL,
  `quiz_question` varchar(100) NOT NULL,
  `quiz_answer` varchar(20) NOT NULL,
  `quiz_msg_correct` varchar(100) NOT NULL,
  `quiz_msg_incorrect` varchar(100) NOT NULL,
  `quiz_enable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`quiz_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;


-- Table structure for table `playsms_featureQuiz_log`
-- 

DROP TABLE IF EXISTS `playsms_featureQuiz_log`;
CREATE TABLE IF NOT EXISTS `playsms_featureQuiz_log` (
  `c_timestamp` int(11) NOT NULL default '0',
  `answer_id` int(4) NOT NULL auto_increment,
  `quiz_id` int(4) NOT NULL default '0',
  `quiz_answer` varchar(100) NOT NULL default '',
  `quiz_sender` varchar(20) NOT NULL default '',
  `in_datetime` varchar(20) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

-- Table structure for table `playsms_featureSubscribe`
--

DROP TABLE IF EXISTS `playsms_featureSubscribe`;
CREATE TABLE IF NOT EXISTS `playsms_featureSubscribe` (
  `c_timestamp` int(11) NOT NULL default '0',
  `subscribe_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `subscribe_keyword` varchar(20) NOT NULL,
  `subscribe_msg` varchar(200) NOT NULL,
  `unsubscribe_msg` varchar(200) NOT NULL,
  `subscribe_enable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`subscribe_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;


-- Table structure for table `playsms_featureSubscribe_member`
-- 

DROP TABLE IF EXISTS `playsms_featureSubscribe_member`;
CREATE TABLE IF NOT EXISTS `playsms_featureSubscribe_member` (
  `c_timestamp` int(11) NOT NULL default '0',
  `member_id` int(11) NOT NULL auto_increment,
  `subscribe_id` int(11) NOT NULL default '0',
  `member_number` varchar(20) NOT NULL,
  `member_since` varchar(20) NOT NULL,
  PRIMARY KEY  (`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

-- Table structure for table `playsms_featureSubscribe_msg`

DROP TABLE IF EXISTS `playsms_featureSubscribe_msg`;
CREATE TABLE IF NOT EXISTS `playsms_featureSubscribe_msg` (
  `c_timestamp` int(11) NOT NULL default '0',
  `msg_id` int(11) NOT NULL auto_increment,
  `subscribe_id` int(11) NOT NULL,
  `msg` varchar(200) NOT NULL,
  PRIMARY KEY  (`msg_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;  


-- 
-- Table structure for table `playsms_featureAutosend`
-- 

DROP TABLE IF EXISTS `playsms_featureAutosend`;
CREATE TABLE IF NOT EXISTS `playsms_featureAutosend` (
  `c_timestamp` int(11) NOT NULL default '0',
  `autosend_id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `autosend_message` varchar(200) NOT NULL,
  `autosend_number` varchar(20) NOT NULL,
  `autosend_enable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`autosend_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;
   
   
-- 
-- Table structure for table `playsms_featureAutosend_time`
-- 

DROP TABLE IF EXISTS `playsms_featureAutosend_time`;
CREATE TABLE IF NOT EXISTS `playsms_featureAutosend_time` (
  `c_timestamp` int(11) NOT NULL default '0',
  `time_id` int(11) NOT NULL auto_increment,
  `autosend_id` int(11) NOT NULL,
  `autosend_time` varchar(20) NOT NULL,
  `sent` enum('1','0') NOT NULL default '0',
  PRIMARY KEY  (`time_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

  
-- Table Structure for table 'playsms_gatewayClicatel_apidata'--

DROP TABLE IF EXISTS `playsms_gatewayClickatell_apidata`;
CREATE TABLE `playsms_gatewayClickatell_apidata` (
  `c_timestamp` int(11) NOT NULL default '0',
  `apidata_id` int(11) NOT NULL auto_increment,
  `smslog_id` int(11) NOT NULL default '0',
  `apimsgid` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`apidata_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayClickatell_apidata`
--

LOCK TABLES `playsms_gatewayClickatell_apidata` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_apidata` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_apidata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayClickatell_config`
--

DROP TABLE IF EXISTS `playsms_gatewayClickatell_config`;
CREATE TABLE `playsms_gatewayClickatell_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) default 'clickatell',
  `cfg_api_id` varchar(20) default NULL,
  `cfg_username` varchar(100) default NULL,
  `cfg_password` varchar(100) default NULL,
  `cfg_sender` varchar(20) default NULL,
  `cfg_send_url` varchar(250) default NULL,
  `cfg_incoming_path` varchar(250) default NULL,
  `cfg_credit` int(11) NOT NULL default '0',
  `cfg_additional_param` varchar(250) default NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL default '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayClickatell_config`
--

LOCK TABLES `playsms_gatewayClickatell_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayClickatell_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayClickatell_config` VALUES (0,'clickatell','123456','playsms','playsms','PlaySMS','http://api.clickatell.com/http','/var/spool/playsms',10,'','+0700');
/*!40000 ALTER TABLE `playsms_gatewayClickatell_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayGnokii_config`
--

DROP TABLE IF EXISTS `playsms_gatewayGnokii_config`;
CREATE TABLE `playsms_gatewayGnokii_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) NOT NULL default 'gnokii',
  `cfg_path` varchar(250) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayGnokii_config`
--

LOCK TABLES `playsms_gatewayGnokii_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayGnokii_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayGnokii_config` VALUES (0,'gnokii','/var/spool/playsms');
/*!40000 ALTER TABLE `playsms_gatewayGnokii_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayKannel_config`
--

DROP TABLE IF EXISTS `playsms_gatewayKannel_config`;
CREATE TABLE `playsms_gatewayKannel_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) NOT NULL default 'kannel',
  `cfg_incoming_path` varchar(250) default NULL,
  `cfg_username` varchar(100) default NULL,
  `cfg_password` varchar(100) default NULL,
  `cfg_global_sender` varchar(20) default NULL,
  `cfg_bearerbox_host` varchar(250) default NULL,
  `cfg_sendsms_port` varchar(10) default NULL,
  `cfg_playsms_web` varchar(250) default NULL,
  `cfg_additional_param` varchar(250) default NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL default '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayKannel_config`
--

LOCK TABLES `playsms_gatewayKannel_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayKannel_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayKannel_config` VALUES (0,'kannel','/var/spool/playsms','playsms','playsms','','127.0.0.1','13131','http://localhost/playsms','','+0700');
/*!40000 ALTER TABLE `playsms_gatewayKannel_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayKannel_dlr`
--

DROP TABLE IF EXISTS `playsms_gatewayKannel_dlr`;
CREATE TABLE `playsms_gatewayKannel_dlr` (
  `c_timestamp` int(11) NOT NULL default '0',
  `kannel_dlr_id` int(11) NOT NULL auto_increment,
  `smslog_id` int(11) NOT NULL default '0',
  `kannel_dlr_type` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`kannel_dlr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayKannel_dlr`
--

LOCK TABLES `playsms_gatewayKannel_dlr` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayKannel_dlr` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayKannel_dlr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTemplate_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTemplate_config`;
CREATE TABLE `playsms_gatewayTemplate_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) NOT NULL default 'template',
  `cfg_path` varchar(250) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayTemplate_config`
--

LOCK TABLES `playsms_gatewayTemplate_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTemplate_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTemplate_config` VALUES (0,'template','/usr/local');
/*!40000 ALTER TABLE `playsms_gatewayTemplate_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayUplink`
--

DROP TABLE IF EXISTS `playsms_gatewayUplink`;
CREATE TABLE `playsms_gatewayUplink` (
  `c_timestamp` int(11) NOT NULL default '0',
  `up_id` int(11) NOT NULL auto_increment,
  `up_local_slid` int(11) NOT NULL default '0',
  `up_remote_slid` int(11) NOT NULL default '0',
  `up_status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`up_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayUplink`
--

LOCK TABLES `playsms_gatewayUplink` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayUplink` DISABLE KEYS */;
INSERT INTO `playsms_gatewayUplink` VALUES (0,1,3,259,1);
/*!40000 ALTER TABLE `playsms_gatewayUplink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayUplink_config`
--

DROP TABLE IF EXISTS `playsms_gatewayUplink_config`;
CREATE TABLE `playsms_gatewayUplink_config` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_name` varchar(20) NOT NULL default 'uplink',
  `cfg_master` varchar(250) default NULL,
  `cfg_username` varchar(100) default NULL,
  `cfg_password` varchar(100) default NULL,
  `cfg_global_sender` varchar(20) default NULL,
  `cfg_incoming_path` varchar(250) default NULL,
  `cfg_additional_param` varchar(250) default NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL default '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_gatewayUplink_config`
--

LOCK TABLES `playsms_gatewayUplink_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayUplink_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayUplink_config` VALUES (0,'uplink','http://playsms.master.url','playsms','playsms','','/var/spool/playsms','','+0700');
/*!40000 ALTER TABLE `playsms_gatewayUplink_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayMsgtoolbox`
--

DROP TABLE IF EXISTS `playsms_gatewayMsgtoolbox` ;
CREATE TABLE `playsms_gatewayMsgtoolbox` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `playsms_gatewayMsgtoolbox_config`
--

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

--
-- Dumping data for table `playsms_gatewayMsgtoolbox_config`
--

LOCK TABLES `playsms_gatewayMsgtoolbox_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayMsgtoolbox_config` VALUES (0,'msgtoolbox','http://serverX.msgtoolbox.com/api/current/send/message.php','1','playsms','password','playSMS','+0700');
/*!40000 ALTER TABLE `playsms_gatewayMsgtoolbox_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblConfig_main`
--

DROP TABLE IF EXISTS `playsms_tblConfig_main`;
CREATE TABLE `playsms_tblConfig_main` (
  `c_timestamp` int(11) NOT NULL default '0',
  `cfg_web_title` varchar(250) default NULL,
  `cfg_email_service` varchar(250) default NULL,
  `cfg_email_footer` varchar(250) default NULL,
  `cfg_gateway_module` varchar(20) default NULL,
  `cfg_gateway_number` varchar(100) default NULL,
  `cfg_themes_module` varchar(100) default NULL,
  `cfg_default_rate` FLOAT NOT NULL DEFAULT '0',
  `cfg_language_module` varchar(10) default 'en_US',
  `cfg_datetime_timezone` varchar(30) NOT NULL default '+0700',
  `cfg_sms_max_count` tinyint(4) NOT NULL default '3',
  `cfg_default_credit` FLOAT NOT NULL DEFAULT '0',
  `cfg_enable_register` tinyint(4) NOT NULL default '0',
  `cfg_enable_forgot` tinyint(4) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblConfig_main`
--

LOCK TABLES `playsms_tblConfig_main` WRITE;
/*!40000 ALTER TABLE `playsms_tblConfig_main` DISABLE KEYS */;
INSERT INTO `playsms_tblConfig_main` VALUES (0,'playSMS','noreply@playsms.org','powered by playSMS','smstools','000','default',0,'en_US','+0700',3,0,0,1);
/*!40000 ALTER TABLE `playsms_tblConfig_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSimplerate`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_toolsSimplerate`
--

--
-- Table structure for table `playsms_tblSMSOutgoing`
--

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing`;
CREATE TABLE `playsms_tblSMSOutgoing` (
  `c_timestamp` int(11) NOT NULL default '0',
  `smslog_id` int(11) NOT NULL auto_increment,
  `flag_deleted` tinyint(4) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `p_gateway` varchar(100) NOT NULL default '',
  `p_src` varchar(100) NOT NULL default '',
  `p_dst` varchar(100) NOT NULL default '',
  `p_footer` varchar(30) NOT NULL default '',
  `p_msg` text NOT NULL,
  `p_datetime` varchar(20) NOT NULL default '0000-00-00 00:00:00',
  `p_update` varchar(20) NOT NULL default '0000-00-00 00:00:00',
  `p_status` tinyint(4) NOT NULL default '0',
  `p_gpid` tinyint(4) NOT NULL default '0',
  `p_credit` tinyint(4) NOT NULL default '0',
  `p_sms_type` varchar(100) NOT NULL default '',
  `unicode` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`smslog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblSMSOutgoing`
--

LOCK TABLES `playsms_tblSMSOutgoing` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSOutgoing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSIncoming`
--

DROP TABLE IF EXISTS `playsms_tblSMSIncoming`;
CREATE TABLE `playsms_tblSMSIncoming` (
  `c_timestamp` int(11) NOT NULL default '0',
  `in_id` int(11) NOT NULL auto_increment,
  `flag_deleted` tinyint(4) NOT NULL default '0',
  `in_uid` int(11) NOT NULL default '0',
  `in_feature` varchar(250) NOT NULL default '',
  `in_gateway` varchar(100) NOT NULL default '',
  `in_sender` varchar(100) NOT NULL default '',
  `in_receiver` varchar(20) NOT NULL default '',
  `in_keyword` varchar(100) NOT NULL default '',
  `in_message` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL default '0000-00-00 00:00:00',
  `in_status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblSMSIncoming`
--

LOCK TABLES `playsms_tblSMSIncoming` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSIncoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblSMSIncoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblSMSTemplate`
--

DROP TABLE IF EXISTS `playsms_tblSMSTemplate`;
CREATE TABLE `playsms_tblSMSTemplate` (
  `c_timestamp` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `t_title` varchar(100) NOT NULL default '',
  `t_text` text NOT NULL,
  PRIMARY KEY  (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblSMSTemplate`
--

LOCK TABLES `playsms_tblSMSTemplate` WRITE;
/*!40000 ALTER TABLE `playsms_tblSMSTemplate` DISABLE KEYS */;
INSERT INTO `playsms_tblSMSTemplate` VALUES (0,1,1,'Good morning','Hi u there, good morning!!'),(0,2,1,'Good night have a sweet dream','Hi sweetheart, good night and have a sweet dream :*');
/*!40000 ALTER TABLE `playsms_tblSMSTemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUser`
--

DROP TABLE IF EXISTS `playsms_tblUser`;
CREATE TABLE `playsms_tblUser` (
  `c_timestamp` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL auto_increment,
  `status` tinyint(4) NOT NULL default '0',
  `ticket` varchar(100) NOT NULL default '',
  `username` varchar(100) NOT NULL default '',
  `password` varchar(100) character set utf8 collate utf8_bin NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `mobile` varchar(100) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `sender` varchar(30) NOT NULL default '',
  `dailysms` int(11) NOT NULL default '0',
  `gender` tinyint(4) NOT NULL default '0',
  `age` tinyint(4) NOT NULL default '0',
  `address` varchar(250) NOT NULL default '',
  `city` varchar(100) NOT NULL default '',
  `state` varchar(100) NOT NULL default '',
  `country` int(11) NOT NULL default '0',
  `birthday` varchar(10) NOT NULL default '0000-00-00',
  `marital` tinyint(4) NOT NULL default '0',
  `education` tinyint(4) NOT NULL default '0',
  `zipcode` varchar(10) NOT NULL default '',
  `junktimestamp` varchar(30) NOT NULL default '',
  `credit` DOUBLE NOT NULL default '0',
  `datetime_timezone` varchar(30) NOT NULL default '+0700',
  `fwd_to_mobile` TINYINT( 4 ) NOT NULL DEFAULT '0',
  `fwd_to_email` TINYINT( 4 ) NOT NULL DEFAULT '1',
  `fwd_to_inbox` TINYINT( 4 ) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblUser`
--

LOCK TABLES `playsms_tblUser` WRITE;
/*!40000 ALTER TABLE `playsms_tblUser` DISABLE KEYS */;
INSERT INTO `playsms_tblUser` VALUES (0,1,2,'bbde889a4de8ad9a9f1b1853a301486a','admin','admin','Administrator','+62000000000','noreply@playsms.org',' - playSMS',0,0,38,'','','',132,'',0,0,'','',0,'+0700',0,1,1);
/*!40000 ALTER TABLE `playsms_tblUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSimplephonebook_group`
--

DROP TABLE IF EXISTS `playsms_toolsSimplephonebook_group`;
CREATE TABLE `playsms_toolsSimplephonebook_group` (
  `c_timestamp` int(11) NOT NULL default '0',
  `gpid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `gp_name` varchar(100) NOT NULL default '',
  `gp_code` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`gpid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_toolsSimplephonebook_group`
--

LOCK TABLES `playsms_toolsSimplephonebook_group` WRITE;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSimplephonebook_group_public`
--

DROP TABLE IF EXISTS `playsms_toolsSimplephonebook_group_public`;
CREATE TABLE `playsms_toolsSimplephonebook_group_public` (
  `c_timestamp` int(11) NOT NULL default '0',
  `gpidpublic` int(11) NOT NULL auto_increment,
  `gpid` int(11) NOT NULL default '0',
  `uid` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`gpidpublic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_toolsSimplephonebook_group_public`
--

LOCK TABLES `playsms_toolsSimplephonebook_group_public` WRITE;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook_group_public` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook_group_public` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUserInbox`
--

DROP TABLE IF EXISTS `playsms_tblUserInbox`;
CREATE TABLE `playsms_tblUserInbox` (
  `c_timestamp` int(11) NOT NULL default '0',
  `in_id` int(11) NOT NULL auto_increment,
  `in_sender` varchar(20) NOT NULL default '',
  `in_receiver` varchar(20) NOT NULL default '',
  `in_uid` int(11) NOT NULL default '0',
  `in_msg` varchar(200) NOT NULL default '',
  `in_datetime` varchar(20) NOT NULL default '0000-00-00 00:00:00',
  `in_hidden` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`in_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblUserInbox`
--

LOCK TABLES `playsms_tblUserInbox` WRITE;
/*!40000 ALTER TABLE `playsms_tblUserInbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_tblUserInbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_toolsSimplephonebook`
--

DROP TABLE IF EXISTS `playsms_toolsSimplephonebook`;
CREATE TABLE `playsms_toolsSimplephonebook` (
  `c_timestamp` int(11) NOT NULL default '0',
  `pid` int(11) NOT NULL auto_increment,
  `gpid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `p_num` varchar(100) NOT NULL default '',
  `p_desc` varchar(250) NOT NULL default '',
  `p_email` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_toolsSimplephonebook`
--

LOCK TABLES `playsms_toolsSimplephonebook` WRITE;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_toolsSimplephonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_tblUser_country`
--

DROP TABLE IF EXISTS `playsms_tblUser_country`;
CREATE TABLE `playsms_tblUser_country` (
  `c_timestamp` int(11) NOT NULL default '0',
  `country_id` int(11) NOT NULL auto_increment,
  `country_name` varchar(200) NOT NULL default '',
  `country_code` varchar(10) NOT NULL,
  PRIMARY KEY  (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=335 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblUser_country`
--

LOCK TABLES `playsms_tblUser_country` WRITE;
/*!40000 ALTER TABLE `playsms_tblUser_country` DISABLE KEYS */;
INSERT INTO `playsms_tblUser_country` VALUES (0,1,'Afghanistan',''),(0,2,'Albania',''),(0,3,'Algeria',''),(0,5,'Andorra',''),(0,10,'Argentina',''),(0,11,'Armenia',''),(0,14,'Australia',''),(0,16,'Austria',''),(0,18,'Azerbaijan',''),(0,19,'Bahamas',''),(0,20,'Bahrain',''),(0,21,'Bangladesh',''),(0,24,'Belarus',''),(0,25,'Belgium',''),(0,29,'Bermuda',''),(0,30,'Bhutan',''),(0,31,'Bolivia',''),(0,32,'Bosnia-Herzegovina',''),(0,33,'Botswana',''),(0,35,'Brazil',''),(0,38,'Brunei',''),(0,39,'Bulgaria',''),(0,41,'Burundi',''),(0,42,'Cambodia',''),(0,44,'Cameroon',''),(0,45,'Canada',''),(0,51,'Chile',''),(0,52,'China',''),(0,55,'Columbia',''),(0,58,'Congo',''),(0,60,'Costa Rica',''),(0,61,'Croatia',''),(0,62,'Cuba',''),(0,66,'Czech Republic',''),(0,67,'Denmark',''),(0,74,'East Timor',''),(0,76,'Ecuador',''),(0,77,'Egypt',''),(0,78,'El Salvador',''),(0,81,'Estonia',''),(0,84,'Fiji Islands',''),(0,85,'Finland',''),(0,86,'France',''),(0,93,'Gabon',''),(0,94,'Gambia',''),(0,96,'Germany',''),(0,98,'Ghana',''),(0,100,'Greece',''),(0,105,'Guam',''),(0,107,'Guatemala',''),(0,108,'UK',''),(0,111,'Guyana',''),(0,112,'Haiti',''),(0,113,'Honduras',''),(0,114,'HongKong',''),(0,118,'Hungary',''),(0,120,'Iceland',''),(0,121,'India',''),(0,132,'Indonesia',''),(0,139,'Iran',''),(0,140,'Iraq',''),(0,141,'Ireland',''),(0,143,'Israel',''),(0,144,'Italy',''),(0,146,'Ivory Coast',''),(0,147,'Jamaica',''),(0,148,'Japan',''),(0,150,'Jordan',''),(0,151,'Kazakhstan',''),(0,153,'Kenya',''),(0,155,'Korea (South)',''),(0,156,'Korea (North)',''),(0,157,'Kuwait',''),(0,158,'Kyrgyzstan',''),(0,160,'Latvia',''),(0,161,'Lebanon',''),(0,163,'Liberia',''),(0,164,'Libya',''),(0,166,'Lithuania',''),(0,167,'Luxembourg',''),(0,170,'Macedonia',''),(0,171,'Malawi',''),(0,173,'Malaysia',''),(0,175,'Maldives',''),(0,177,'Mali Republic',''),(0,178,'Malta',''),(0,181,'Mauritania',''),(0,184,'Mexico',''),(0,186,'Moldova',''),(0,188,'Mongolia',''),(0,189,'Montserrat',''),(0,190,'Morocco',''),(0,192,'Mozambique',''),(0,193,'Myanmar',''),(0,194,'Namibia',''),(0,196,'Nepal',''),(0,197,'Netherlands',''),(0,200,'New Caledonia',''),(0,201,'New Zealand',''),(0,202,'Nicaragua',''),(0,203,'Niger',''),(0,204,'Nigeria',''),(0,208,'Norway',''),(0,209,'Oman',''),(0,210,'Pakistan',''),(0,211,'Palau',''),(0,212,'Palestine',''),(0,213,'Panama',''),(0,214,'Papua New Guinea',''),(0,215,'Paraguay',''),(0,216,'Peru',''),(0,217,'Philippines',''),(0,220,'Poland',''),(0,223,'Portugal',''),(0,225,'Puerto Rico',''),(0,226,'Qatar',''),(0,228,'Romania',''),(0,229,'Russia',''),(0,232,'Rwanda',''),(0,238,'Samoa',''),(0,241,'Saudi Arabia',''),(0,242,'Senegal',''),(0,244,'Sierra Leone',''),(0,245,'Singapore',''),(0,248,'Slovakia',''),(0,249,'Slovenia',''),(0,251,'Somalia',''),(0,252,'South Africa',''),(0,253,'Spain',''),(0,256,'Sri Lanka',''),(0,257,'Sudan',''),(0,258,'Suriname',''),(0,259,'Swaziland',''),(0,260,'Sweden',''),(0,262,'Switzerland',''),(0,263,'Syria',''),(0,264,'Taiwan',''),(0,267,'Tajikistan',''),(0,268,'Tanzania',''),(0,269,'Thailand',''),(0,274,'Trinidad and Tobago',''),(0,275,'Tunisia',''),(0,276,'Turkey',''),(0,277,'Turkmenistan',''),(0,279,'Tuvalu',''),(0,280,'Uganda',''),(0,281,'Ukraine',''),(0,284,'USA',''),(0,289,'United Arab Emirates',''),(0,290,'Uruguay',''),(0,291,'Uzbekistan',''),(0,293,'Vatican City State',''),(0,294,'Venezuela',''),(0,295,'Vietnam',''),(0,299,'Yemen',''),(0,300,'Yugoslavia',''),(0,303,'Zambia',''),(0,305,'Zimbabwe',''),(0,312,'Ethiopia',''),(0,314,'South Korea',''),(0,318,'Angola',''),(0,319,'Aruba',''),(0,320,'Laos',''),(0,325,'Serbia & Montenegro (Yugoslavia)',''),(0,332,'Jersey',''),(0,334,'OTHER (unlisted)','');
/*!40000 ALTER TABLE `playsms_tblUser_country` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Table structure for table `playsms_tblErrorString`
--

DROP TABLE IF EXISTS `playsms_tblErrorString`;
CREATE TABLE `playsms_tblErrorString` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `error_string` TEXT NOT NULL ,
  PRIMARY KEY ( `id` )
) ENGINE=MYISAM DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `playsms_tblErrorString`
--

--
-- Table structure for table `playsms_tblBilling`
--

DROP TABLE IF EXISTS `playsms_tblBilling`;
CREATE TABLE `playsms_tblBilling` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `c_timestamp` INT NOT NULL ,
  `post_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `smslog_id` INT NOT NULL ,
  `rate` FLOAT NOT NULL ,
  `credit` DOUBLE NOT NULL default '0',
  `status` TINYINT NOT NULL ,
  PRIMARY KEY ( `id` )
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_tblBilling`
--

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

-- plugin: sendfromfile

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

-- plugin: inboxgroup

DROP TABLE IF EXISTS `playsms_featureInboxgroup` ;
CREATE TABLE `playsms_featureInboxgroup` (
`c_timestamp` INT NOT NULL ,
`id` INT NOT NULL AUTO_INCREMENT ,
`uid` BIGINT NOT NULL ,
`in_receiver` VARCHAR( 20 ) NOT NULL ,
`keywords` VARCHAR( 100 ) NOT NULL ,
`description` VARCHAR( 250 ) NOT NULL ,
`creation_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00' ,
`exclusive` TINYINT NOT NULL DEFAULT '0' ,
`deleted` TINYINT NOT NULL DEFAULT '0' ,
`status` TINYINT NOT NULL DEFAULT '0' ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_members` ;
CREATE TABLE `playsms_featureInboxgroup_members` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_catchall` ;
CREATE TABLE `playsms_featureInboxgroup_catchall` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_in` ;
CREATE TABLE `playsms_featureInboxgroup_log_in` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`rid` INT NOT NULL ,
`sms_datetime` VARCHAR( 20 ) NOT NULL DEFAULT '0000-00-00 00:00:00',
`sms_sender` VARCHAR( 20 ) NOT NULL ,
`keyword` VARCHAR( 100 ) NOT NULL ,
`message` TEXT NOT NULL ,
`sms_receiver` VARCHAR( 20 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_out` ;
CREATE TABLE `playsms_featureInboxgroup_log_out` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`log_in_id` BIGINT NOT NULL ,
`smslog_id` BIGINT NOT NULL ,
`catchall` TINYINT NOT NULL DEFAULT '0',
`uid` BIGINT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

