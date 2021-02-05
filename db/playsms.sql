SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `playsms_featureAutoreply`;
CREATE TABLE `playsms_featureAutoreply` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `autoreply_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `autoreply_keyword` varchar(10) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureAutoreply_scenario`;
CREATE TABLE `playsms_featureAutoreply_scenario` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `autoreply_scenario_id` int(11) NOT NULL,
  `autoreply_id` int(11) NOT NULL DEFAULT 0,
  `autoreply_scenario_param1` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param2` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param3` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param4` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param5` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param6` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_param7` varchar(20) NOT NULL DEFAULT '',
  `autoreply_scenario_result` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureBoard`;
CREATE TABLE `playsms_featureBoard` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `board_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `board_keyword` varchar(100) NOT NULL DEFAULT '',
  `board_reply` varchar(100) NOT NULL DEFAULT '',
  `board_forward_email` varchar(250) NOT NULL DEFAULT '',
  `board_css` varchar(250) NOT NULL DEFAULT '',
  `board_pref_template` text NOT NULL,
  `board_access_code` varchar(40) NOT NULL DEFAULT '',
  `board_reply_msg` varchar(140) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureBoard_log`;
CREATE TABLE `playsms_featureBoard_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `in_id` int(11) NOT NULL,
  `board_id` int(11) NOT NULL DEFAULT 0,
  `in_gateway` varchar(100) NOT NULL DEFAULT '',
  `in_sender` varchar(20) NOT NULL DEFAULT '',
  `in_masked` varchar(20) NOT NULL DEFAULT '',
  `in_keyword` varchar(20) NOT NULL DEFAULT '',
  `in_msg` text NOT NULL,
  `in_reply` varchar(100) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureCommand`;
CREATE TABLE `playsms_featureCommand` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `command_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `command_keyword` varchar(10) NOT NULL DEFAULT '',
  `command_exec` text NOT NULL,
  `command_return_as_reply` int(11) NOT NULL DEFAULT 0,
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureCredit`;
CREATE TABLE `playsms_featureCredit` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `parent_uid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `username` varchar(100) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(13,3) NOT NULL DEFAULT 0.000,
  `balance` decimal(13,3) NOT NULL DEFAULT 0.000,
  `create_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `delete_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureCustom`;
CREATE TABLE `playsms_featureCustom` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `custom_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `service_name` varchar(255) NOT NULL DEFAULT '',
  `custom_keyword` varchar(255) NOT NULL DEFAULT '',
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  `custom_url` text NOT NULL,
  `custom_return_as_reply` int(11) NOT NULL DEFAULT 0,
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureFirewall`;
CREATE TABLE `playsms_featureFirewall` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureInboxgroup`;
CREATE TABLE `playsms_featureInboxgroup` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `in_receiver` varchar(20) NOT NULL DEFAULT '',
  `keywords` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  `creation_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `exclusive` int(11) NOT NULL DEFAULT 0,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_catchall`;
CREATE TABLE `playsms_featureInboxgroup_catchall` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_in`;
CREATE TABLE `playsms_featureInboxgroup_log_in` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL DEFAULT 0,
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_receiver` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_log_out`;
CREATE TABLE `playsms_featureInboxgroup_log_out` (
  `id` int(11) NOT NULL,
  `log_in_id` int(11) NOT NULL DEFAULT 0,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `catchall` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureInboxgroup_members`;
CREATE TABLE `playsms_featureInboxgroup_members` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureMsgtemplate`;
CREATE TABLE `playsms_featureMsgtemplate` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `tid` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `t_title` varchar(100) NOT NULL DEFAULT '',
  `t_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_featureMsgtemplate` (`c_timestamp`, `tid`, `uid`, `t_title`, `t_text`) VALUES
(0, 1, 1, 'Good morning', 'Hi u there, good morning!!'),
(0, 2, 1, 'Good night have a sweet dream', 'Hi sweetheart, good night and have a sweet dream :*'),
(0, 3, 1, 'Meeting Now', 'Hello #NAME#, please hurry up, boss summons us !');

DROP TABLE IF EXISTS `playsms_featureOutgoing`;
CREATE TABLE `playsms_featureOutgoing` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `dst` varchar(100) NOT NULL DEFAULT '',
  `prefix` text NOT NULL,
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePhonebook`;
CREATE TABLE `playsms_featurePhonebook` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `mobile` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(250) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `tags` varchar(250) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePhonebook_group`;
CREATE TABLE `playsms_featurePhonebook_group` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `code` varchar(20) NOT NULL DEFAULT '',
  `flag_sender` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePhonebook_group_contacts`;
CREATE TABLE `playsms_featurePhonebook_group_contacts` (
  `id` int(11) NOT NULL,
  `gpid` int(11) NOT NULL,
  `pid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePoll`;
CREATE TABLE `playsms_featurePoll` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `poll_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `poll_title` varchar(250) NOT NULL DEFAULT '',
  `poll_keyword` varchar(10) NOT NULL DEFAULT '',
  `poll_enable` int(11) NOT NULL DEFAULT 0,
  `poll_option_vote` int(11) NOT NULL DEFAULT 0,
  `poll_message_valid` text NOT NULL,
  `poll_message_invalid` text NOT NULL,
  `poll_message_option` text NOT NULL,
  `poll_access_code` varchar(40) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePoll_choice`;
CREATE TABLE `playsms_featurePoll_choice` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `choice_id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL DEFAULT 0,
  `choice_title` varchar(250) NOT NULL DEFAULT '',
  `choice_keyword` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featurePoll_log`;
CREATE TABLE `playsms_featurePoll_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `log_id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL DEFAULT 0,
  `choice_id` int(11) NOT NULL DEFAULT 0,
  `poll_sender` varchar(20) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureQuiz`;
CREATE TABLE `playsms_featureQuiz` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `quiz_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `quiz_keyword` varchar(20) NOT NULL DEFAULT '',
  `quiz_question` varchar(100) NOT NULL DEFAULT '',
  `quiz_answer` varchar(20) NOT NULL DEFAULT '',
  `quiz_msg_correct` varchar(100) NOT NULL DEFAULT '',
  `quiz_msg_incorrect` varchar(100) NOT NULL DEFAULT '',
  `quiz_enable` int(11) NOT NULL DEFAULT 0,
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureQuiz_log`;
CREATE TABLE `playsms_featureQuiz_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `answer_id` int(4) NOT NULL,
  `quiz_id` int(4) NOT NULL DEFAULT 0,
  `quiz_answer` varchar(100) NOT NULL DEFAULT '',
  `quiz_sender` varchar(20) NOT NULL DEFAULT '',
  `in_datetime` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSchedule`;
CREATE TABLE `playsms_featureSchedule` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `schedule_rule` int(11) NOT NULL DEFAULT 0,
  `flag_active` int(11) NOT NULL DEFAULT 0,
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSchedule_dst`;
CREATE TABLE `playsms_featureSchedule_dst` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `schedule` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL DEFAULT '',
  `destination` varchar(250) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSendfromfile`;
CREATE TABLE `playsms_featureSendfromfile` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `sid` varchar(50) NOT NULL DEFAULT '',
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_to` varchar(50) NOT NULL DEFAULT '',
  `sms_msg` text NOT NULL,
  `sms_username` varchar(50) NOT NULL DEFAULT '',
  `sms_uid` int(11) NOT NULL DEFAULT 0,
  `hash` varchar(40) NOT NULL DEFAULT '',
  `unicode` int(11) NOT NULL DEFAULT 0,
  `charge` float NOT NULL DEFAULT 0,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `queue_code` varchar(40) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT 0,
  `flag_processed` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSimplerate`;
CREATE TABLE `playsms_featureSimplerate` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `dst` varchar(100) NOT NULL DEFAULT '',
  `prefix` varchar(10) NOT NULL DEFAULT '',
  `rate` decimal(13,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSmssysnc`;
CREATE TABLE `playsms_featureSmssysnc` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `message_id` varchar(250) DEFAULT '',
  `recvsms_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureStoplist`;
CREATE TABLE `playsms_featureStoplist` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `mobile` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSubscribe`;
CREATE TABLE `playsms_featureSubscribe` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `subscribe_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `subscribe_keyword` varchar(20) NOT NULL DEFAULT '',
  `subscribe_msg` varchar(140) NOT NULL DEFAULT '',
  `unsubscribe_msg` varchar(140) NOT NULL DEFAULT '',
  `subscribe_enable` int(11) NOT NULL DEFAULT 0,
  `subscribe_param` varchar(20) NOT NULL DEFAULT '',
  `unsubscribe_param` varchar(20) NOT NULL DEFAULT '',
  `forward_param` varchar(20) NOT NULL DEFAULT '',
  `unknown_format_msg` varchar(140) NOT NULL DEFAULT '',
  `already_member_msg` varchar(140) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT '',
  `duration` int(11) NOT NULL DEFAULT 0,
  `expire_msg` varchar(140) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSubscribe_member`;
CREATE TABLE `playsms_featureSubscribe_member` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `member_id` int(11) NOT NULL,
  `subscribe_id` int(11) NOT NULL DEFAULT 0,
  `member_number` varchar(20) NOT NULL DEFAULT '',
  `member_since` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_featureSubscribe_msg`;
CREATE TABLE `playsms_featureSubscribe_msg` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `msg_id` int(11) NOT NULL,
  `subscribe_id` int(11) NOT NULL DEFAULT 0,
  `msg` varchar(200) NOT NULL DEFAULT '',
  `create_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `counter` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayGeneric_log`;
CREATE TABLE `playsms_gatewayGeneric_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `local_smslog_id` int(11) NOT NULL DEFAULT 0,
  `remote_smslog_id` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayInfobip_apidata`;
CREATE TABLE `playsms_gatewayInfobip_apidata` (
  `c_timestamp` int(11) NOT NULL DEFAULT 0,
  `apidata_id` int(11) NOT NULL,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayInfobip_config`;
CREATE TABLE `playsms_gatewayInfobip_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT 0,
  `cfg_name` varchar(20) NOT NULL DEFAULT 'infobip',
  `cfg_username` varchar(100) NOT NULL DEFAULT '',
  `cfg_password` varchar(100) NOT NULL DEFAULT '',
  `cfg_module_sender` varchar(20) NOT NULL DEFAULT '',
  `cfg_send_url` varchar(250) NOT NULL DEFAULT '',
  `cfg_credit` int(11) NOT NULL DEFAULT 0,
  `cfg_additional_param` varchar(250) NOT NULL DEFAULT '',
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '',
  `cfg_dlr_nopush` varchar(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_gatewayInfobip_config` (`c_timestamp`, `cfg_name`, `cfg_username`, `cfg_password`, `cfg_module_sender`, `cfg_send_url`, `cfg_credit`, `cfg_additional_param`, `cfg_datetime_timezone`, `cfg_dlr_nopush`) VALUES
(0, 'infobip', '', '', '', 'http://api.infobip.com/api/v3', 0, '', '', '1');

DROP TABLE IF EXISTS `playsms_gatewayJasmin_log`;
CREATE TABLE `playsms_gatewayJasmin_log` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `local_smslog_id` int(11) NOT NULL DEFAULT 0,
  `remote_smslog_id` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayNexmo`;
CREATE TABLE `playsms_gatewayNexmo` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `local_smslog_id` int(11) NOT NULL DEFAULT 0,
  `remote_smslog_id` varchar(40) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT 0,
  `network` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayNexmo_config`;
CREATE TABLE `playsms_gatewayNexmo_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `cfg_name` varchar(20) NOT NULL DEFAULT 'nexmo',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_api_key` varchar(100) DEFAULT NULL,
  `cfg_api_secret` varchar(100) DEFAULT NULL,
  `cfg_module_sender` varchar(20) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_gatewayNexmo_config` (`c_timestamp`, `cfg_name`, `cfg_url`, `cfg_api_key`, `cfg_api_secret`, `cfg_module_sender`, `cfg_datetime_timezone`) VALUES
(0, 'nexmo', 'https://rest.nexmo.com/sms/json', '12345678', '87654321', 'playSMS', '');

DROP TABLE IF EXISTS `playsms_gatewayPlaynet_outgoing`;
CREATE TABLE `playsms_gatewayPlaynet_outgoing` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `created` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flag` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `smsc` varchar(100) NOT NULL DEFAULT '',
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `sms_to` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_type` int(11) NOT NULL DEFAULT 0,
  `unicode` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewaySmstools_dlr`;
CREATE TABLE `playsms_gatewaySmstools_dlr` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `message_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayTemplate_config`;
CREATE TABLE `playsms_gatewayTemplate_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `cfg_name` varchar(20) NOT NULL DEFAULT 'template',
  `cfg_path` varchar(250) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_gatewayTemplate_config` (`c_timestamp`, `cfg_name`, `cfg_path`) VALUES
(0, 'template', '/usr/local');

DROP TABLE IF EXISTS `playsms_gatewayTwilio`;
CREATE TABLE `playsms_gatewayTwilio` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `local_smslog_id` int(11) NOT NULL DEFAULT 0,
  `remote_smslog_id` varchar(40) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayTwilio_config`;
CREATE TABLE `playsms_gatewayTwilio_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `cfg_name` varchar(20) NOT NULL DEFAULT 'twilio',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_callback_url` varchar(250) DEFAULT NULL,
  `cfg_account_sid` varchar(100) DEFAULT NULL,
  `cfg_auth_token` varchar(100) DEFAULT NULL,
  `cfg_module_sender` varchar(20) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_gatewayTwilio_config` (`c_timestamp`, `cfg_name`, `cfg_url`, `cfg_callback_url`, `cfg_account_sid`, `cfg_auth_token`, `cfg_module_sender`, `cfg_datetime_timezone`) VALUES
(0, 'twilio', 'https://api.twilio.com', 'http://localhost/playsms/plugin/gateway/twilio/callback.php', '12345678', '87654321', '+10000000000', '');

DROP TABLE IF EXISTS `playsms_gatewayUplink`;
CREATE TABLE `playsms_gatewayUplink` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `up_id` int(11) NOT NULL,
  `up_local_smslog_id` int(11) NOT NULL DEFAULT 0,
  `up_remote_smslog_id` int(11) NOT NULL DEFAULT 0,
  `up_status` int(11) NOT NULL DEFAULT 0,
  `up_remote_queue_code` varchar(32) NOT NULL DEFAULT '',
  `up_dst` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_gatewayUplink_config`;
CREATE TABLE `playsms_gatewayUplink_config` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `cfg_name` varchar(20) NOT NULL DEFAULT 'uplink',
  `cfg_master` varchar(250) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_token` varchar(32) DEFAULT NULL,
  `cfg_module_sender` varchar(20) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '',
  `cfg_try_disable_footer` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_gatewayUplink_config` (`c_timestamp`, `cfg_name`, `cfg_master`, `cfg_username`, `cfg_password`, `cfg_token`, `cfg_module_sender`, `cfg_incoming_path`, `cfg_additional_param`, `cfg_datetime_timezone`, `cfg_try_disable_footer`) VALUES
(0, 'uplink', 'http://playsms.master.url', '', '', '', '', '/var/spool/playsms', '', '', 0);

DROP TABLE IF EXISTS `playsms_tblACL`;
CREATE TABLE `playsms_tblACL` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `acl_subuser` varchar(250) NOT NULL DEFAULT '',
  `url` text NOT NULL,
  `flag_disallowed` int(11) NOT NULL DEFAULT 0,
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_tblACL` (`c_timestamp`, `id`, `name`, `acl_subuser`, `url`, `flag_disallowed`, `flag_deleted`) VALUES
(0, 1, 'NEW', '', 'inc=core_sendsms,\r\ninc=core_user,\r\n!inc=core_user&route=subuser_mgmnt,\r\ninc=feature_report,', 0, 0),
(0, 2, 'BROADCAST', '', 'inc=core_sendsms,\r\ninc=core_user,\r\ninc=feature_report,\r\ninc=feature_schedule,\r\ninc=feature_msgtemplate,\r\ninc=feature_queuelog,\r\ninc=feature_credit,\r\ninc=feature_report&route=user\r\n', 0, 0),
(0, 3, 'MEMBER', '', '', 1, 0);

DROP TABLE IF EXISTS `playsms_tblBilling`;
CREATE TABLE `playsms_tblBilling` (
  `id` int(11) NOT NULL,
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `parent_uid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `post_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `rate` decimal(13,3) NOT NULL DEFAULT 0.000,
  `credit` decimal(13,3) NOT NULL DEFAULT 0.000,
  `count` int(11) NOT NULL DEFAULT 0,
  `charge` decimal(13,3) NOT NULL DEFAULT 0.000,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblCountry`;
CREATE TABLE `playsms_tblCountry` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `country_id` int(11) NOT NULL,
  `country_name` varchar(200) NOT NULL DEFAULT '',
  `country_code` varchar(10) NOT NULL DEFAULT '',
  `country_prefix` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(0, 89, 'Côte D\'ivoire', 'ci', '225'),
(0, 90, 'Japan', 'jp', '81'),
(0, 91, 'Jordan', 'jo', '962'),
(0, 92, 'Kazakhstan', 'kz', '7'),
(0, 93, 'Kenya', 'ke', '254'),
(0, 94, 'Kiribati', 'ki', '686'),
(0, 95, 'Kuwait', 'kw', '965'),
(0, 96, 'Kyrgyzstan', 'kg', '996'),
(0, 97, 'Lao People\'s Democratic Republic', 'la', '856'),
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
(0, 136, 'Korea, Democratic People\'s Republic Of', 'kp', '850'),
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

DROP TABLE IF EXISTS `playsms_tblDLR`;
CREATE TABLE `playsms_tblDLR` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `flag_processed` int(11) NOT NULL DEFAULT 0,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `p_status` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblGateway`;
CREATE TABLE `playsms_tblGateway` (
  `id` int(11) NOT NULL,
  `created` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL DEFAULT '',
  `gateway` varchar(100) NOT NULL DEFAULT '',
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_tblGateway` (`id`, `created`, `last_update`, `name`, `gateway`, `data`) VALUES
(1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'blocked', 'blocked', '[]'),
(2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'dev', 'dev', '[]');

DROP TABLE IF EXISTS `playsms_tblNotif`;
CREATE TABLE `playsms_tblNotif` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `label` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` varchar(255) DEFAULT NULL,
  `flag_unread` int(11) NOT NULL DEFAULT 0,
  `data` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblPlaysmsd`;
CREATE TABLE `playsms_tblPlaysmsd` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `run_type` varchar(255) NOT NULL,
  `command` varchar(255) NOT NULL,
  `param` varchar(255) NOT NULL,
  `created` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finish` varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pid` int(11) NOT NULL DEFAULT 0,
  `flag_run` int(11) NOT NULL DEFAULT 0,
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblRecvSMS`;
CREATE TABLE `playsms_tblRecvSMS` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `flag_processed` int(11) NOT NULL DEFAULT 0,
  `sms_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblRegistry`;
CREATE TABLE `playsms_tblRegistry` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT 0,
  `registry_group` varchar(250) NOT NULL DEFAULT '',
  `registry_family` varchar(250) NOT NULL DEFAULT '',
  `registry_key` varchar(250) NOT NULL DEFAULT '',
  `registry_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_tblRegistry` (`c_timestamp`, `id`, `uid`, `registry_group`, `registry_family`, `registry_key`, `registry_value`) VALUES
(0, 1, 1, 'core', 'config', 'playsms_version', '1.4.4-beta4'),
(1404003471, 2, 1, 'core', 'main_config', 'web_title', 'playSMS'),
(1404003471, 3, 1, 'core', 'main_config', 'email_service', 'noreply@example.com'),
(1404003471, 4, 1, 'core', 'main_config', 'email_footer', 'Powered by playSMS'),
(1404003471, 5, 1, 'core', 'main_config', 'main_website_name', 'playSMS'),
(1404003471, 6, 1, 'core', 'main_config', 'main_website_url', '#'),
(1404003471, 7, 1, 'core', 'main_config', 'gateway_number', '1234'),
(1404003471, 8, 1, 'core', 'main_config', 'gateway_timezone', '+0700'),
(1404003471, 9, 1, 'core', 'main_config', 'default_rate', '0'),
(1404003471, 10, 1, 'core', 'main_config', 'gateway_module', 'dev'),
(1404003471, 11, 1, 'core', 'main_config', 'themes_module', 'default'),
(1404003471, 12, 1, 'core', 'main_config', 'language_module', 'en_US'),
(1404003471, 13, 1, 'core', 'main_config', 'sms_max_count', '3'),
(1404003471, 14, 1, 'core', 'main_config', 'default_credit', '0'),
(1404003471, 15, 1, 'core', 'main_config', 'enable_register', '0'),
(1404003471, 16, 1, 'core', 'main_config', 'enable_forgot', '1'),
(0, 17, 1, 'core', 'main_config', 'allow_custom_sender', '0'),
(0, 18, 1, 'core', 'main_config', 'allow_custom_footer', '0'),
(1404003471, 20, 1, 'core', 'main_config', 'default_user_status', '3'),
(1404003471, 21, 1, 'core', 'main_config', 'enable_logo', '1'),
(1404003472, 22, 1, 'core', 'main_config', 'logo_url', 'plugin/themes/common/images/playSMS_logo_full.png'),
(1404003472, 23, 1, 'core', 'main_config', 'logo_replace_title', '1'),
(1404003472, 24, 1, 'core', 'main_config', 'layout_footer', 'Application footer here. Go to main configuration or manage site to edit this footer.'),
(1404003472, 25, 1, 'core', 'main_config', 'buy_credit_page_title', 'Buy credit'),
(1404003472, 26, 1, 'core', 'main_config', 'buy_credit_page_content', 'Go to main configuration or manage site to edit this page'),
(1404003472, 27, 1, 'core', 'main_config', 'information_title', 'Information'),
(1404003472, 28, 1, 'core', 'main_config', 'information_content', 'Go to main configuration or manage site to edit this page'),
(1404003471, 29, 1, 'core', 'main_config', 'default_acl', '1');

DROP TABLE IF EXISTS `playsms_tblSMSInbox`;
CREATE TABLE `playsms_tblSMSInbox` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `in_id` int(11) NOT NULL,
  `flag_deleted` int(11) NOT NULL DEFAULT 0,
  `in_sender` varchar(20) NOT NULL DEFAULT '',
  `in_receiver` varchar(20) NOT NULL DEFAULT '',
  `in_uid` int(11) NOT NULL DEFAULT 0,
  `in_msg` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reference_id` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblSMSIncoming`;
CREATE TABLE `playsms_tblSMSIncoming` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `in_id` int(11) NOT NULL,
  `flag_deleted` int(11) NOT NULL DEFAULT 0,
  `in_uid` int(11) NOT NULL DEFAULT 0,
  `in_feature` varchar(250) NOT NULL DEFAULT '',
  `in_gateway` varchar(100) NOT NULL DEFAULT '',
  `in_sender` varchar(100) NOT NULL DEFAULT '',
  `in_receiver` varchar(20) NOT NULL DEFAULT '',
  `in_keyword` varchar(100) NOT NULL DEFAULT '',
  `in_message` text NOT NULL,
  `in_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing`;
CREATE TABLE `playsms_tblSMSOutgoing` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
  `smslog_id` int(11) DEFAULT NULL,
  `flag_deleted` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `parent_uid` int(11) NOT NULL DEFAULT 0,
  `p_gateway` varchar(250) NOT NULL DEFAULT '',
  `p_smsc` varchar(250) NOT NULL DEFAULT '',
  `p_src` varchar(100) NOT NULL DEFAULT '',
  `p_dst` varchar(100) NOT NULL DEFAULT '',
  `p_footer` varchar(30) NOT NULL DEFAULT '',
  `p_msg` text NOT NULL,
  `p_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `p_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `p_status` int(11) NOT NULL DEFAULT 0,
  `p_gpid` int(11) NOT NULL DEFAULT 0,
  `p_credit` decimal(13,3) NOT NULL DEFAULT 0.000,
  `p_sms_type` varchar(100) NOT NULL DEFAULT '',
  `unicode` int(11) NOT NULL DEFAULT 0,
  `queue_code` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue`;
CREATE TABLE `playsms_tblSMSOutgoing_queue` (
  `id` int(11) NOT NULL,
  `queue_code` varchar(40) NOT NULL DEFAULT '',
  `datetime_entry` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_scheduled` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `datetime_update` varchar(20) NOT NULL DEFAULT '000-00-00 00:00:00',
  `flag` int(11) NOT NULL DEFAULT 0,
  `queue_count` int(11) NOT NULL DEFAULT 0,
  `sms_count` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `gpid` int(11) NOT NULL DEFAULT 0,
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `footer` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_type` varchar(100) NOT NULL DEFAULT '',
  `unicode` int(11) NOT NULL DEFAULT 0,
  `smsc` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblSMSOutgoing_queue_dst`;
CREATE TABLE `playsms_tblSMSOutgoing_queue_dst` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL DEFAULT 0,
  `chunk` int(11) NOT NULL DEFAULT 0,
  `smslog_id` int(11) NOT NULL DEFAULT 0,
  `flag` int(11) NOT NULL DEFAULT 0,
  `dst` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `playsms_tblUser`;
CREATE TABLE `playsms_tblUser` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT 0,
  `parent_uid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `salt` varchar(255) NOT NULL DEFAULT '',
  `token` varchar(32) NOT NULL DEFAULT '',
  `enable_webservices` int(11) NOT NULL DEFAULT 0,
  `webservices_ip` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `mobile` varchar(16) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `sender` varchar(16) NOT NULL DEFAULT '',
  `footer` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(250) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(100) NOT NULL DEFAULT '',
  `country` int(11) NOT NULL DEFAULT 0,
  `zipcode` varchar(10) NOT NULL DEFAULT '',
  `credit` decimal(13,3) NOT NULL DEFAULT 0.000,
  `adhoc_credit` decimal(13,3) NOT NULL DEFAULT 0.000,
  `datetime_timezone` varchar(30) NOT NULL DEFAULT '',
  `language_module` varchar(10) NOT NULL DEFAULT '',
  `fwd_to_mobile` int(11) NOT NULL DEFAULT 0,
  `fwd_to_email` int(11) NOT NULL DEFAULT 1,
  `fwd_to_inbox` int(11) NOT NULL DEFAULT 1,
  `replace_zero` varchar(5) NOT NULL DEFAULT '',
  `plus_sign_remove` int(11) NOT NULL DEFAULT 1,
  `plus_sign_add` int(11) NOT NULL DEFAULT 0,
  `send_as_unicode` int(11) NOT NULL DEFAULT 0,
  `local_length` int(11) NOT NULL DEFAULT 9,
  `register_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdate_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flag_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `playsms_tblUser` (`c_timestamp`, `parent_uid`, `uid`, `status`, `acl_id`, `username`, `password`, `salt`, `token`, `enable_webservices`, `webservices_ip`, `name`, `mobile`, `email`, `sender`, `footer`, `address`, `city`, `state`, `country`, `zipcode`, `credit`, `adhoc_credit`, `datetime_timezone`, `language_module`, `fwd_to_mobile`, `fwd_to_email`, `fwd_to_inbox`, `replace_zero`, `plus_sign_remove`, `plus_sign_add`, `send_as_unicode`, `local_length`, `register_datetime`, `lastupdate_datetime`, `flag_deleted`) VALUES
(1611603929, 0, 1, 2, 0, 'admin', 'd97a6933803258561344122cc031ac37', 'ANzpSkIpknEUoG4h', '', 0, '127.0.0.1', 'Administrator', '+62000000000', 'admin@example.com', '', '@admin', '', '', '', 82, '', '0.000', '0.000', '', 'en_US', 0, 1, 1, '', 1, 0, 0, 0, '', '', 0);


ALTER TABLE `playsms_featureAutoreply`
  ADD PRIMARY KEY (`autoreply_id`);

ALTER TABLE `playsms_featureAutoreply_scenario`
  ADD PRIMARY KEY (`autoreply_scenario_id`);

ALTER TABLE `playsms_featureBoard`
  ADD PRIMARY KEY (`board_id`);

ALTER TABLE `playsms_featureBoard_log`
  ADD PRIMARY KEY (`in_id`);

ALTER TABLE `playsms_featureCommand`
  ADD PRIMARY KEY (`command_id`);

ALTER TABLE `playsms_featureCredit`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureCustom`
  ADD PRIMARY KEY (`custom_id`);

ALTER TABLE `playsms_featureFirewall`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureInboxgroup`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureInboxgroup_catchall`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureInboxgroup_log_in`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureInboxgroup_log_out`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureInboxgroup_members`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureMsgtemplate`
  ADD PRIMARY KEY (`tid`);

ALTER TABLE `playsms_featureOutgoing`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featurePhonebook`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featurePhonebook_group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featurePhonebook_group_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `gpid` (`gpid`);

ALTER TABLE `playsms_featurePoll`
  ADD PRIMARY KEY (`poll_id`);

ALTER TABLE `playsms_featurePoll_choice`
  ADD PRIMARY KEY (`choice_id`);

ALTER TABLE `playsms_featurePoll_log`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `playsms_featureQuiz`
  ADD PRIMARY KEY (`quiz_id`);

ALTER TABLE `playsms_featureQuiz_log`
  ADD PRIMARY KEY (`answer_id`);

ALTER TABLE `playsms_featureSchedule`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSchedule_dst`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSendfromfile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSimplerate`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`);

ALTER TABLE `playsms_featureSmssysnc`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureStoplist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_featureSubscribe`
  ADD PRIMARY KEY (`subscribe_id`);

ALTER TABLE `playsms_featureSubscribe_member`
  ADD PRIMARY KEY (`member_id`);

ALTER TABLE `playsms_featureSubscribe_msg`
  ADD PRIMARY KEY (`msg_id`);

ALTER TABLE `playsms_gatewayGeneric_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_gatewayInfobip_apidata`
  ADD PRIMARY KEY (`apidata_id`);

ALTER TABLE `playsms_gatewayJasmin_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_gatewayNexmo`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_gatewayPlaynet_outgoing`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_gatewaySmstools_dlr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `smslog_id` (`smslog_id`);

ALTER TABLE `playsms_gatewayTwilio`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_gatewayUplink`
  ADD PRIMARY KEY (`up_id`);

ALTER TABLE `playsms_tblACL`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblBilling`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblCountry`
  ADD PRIMARY KEY (`country_id`);

ALTER TABLE `playsms_tblDLR`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblGateway`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `playsms_tblNotif`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblPlaysmsd`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblRecvSMS`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblRegistry`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblSMSInbox`
  ADD PRIMARY KEY (`in_id`),
  ADD KEY `in_uid` (`in_uid`);

ALTER TABLE `playsms_tblSMSIncoming`
  ADD PRIMARY KEY (`in_id`),
  ADD KEY `in_uid` (`in_uid`);

ALTER TABLE `playsms_tblSMSOutgoing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `smslog_id` (`smslog_id`),
  ADD KEY `uid` (`uid`);

ALTER TABLE `playsms_tblSMSOutgoing_queue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `queue_code` (`queue_code`);

ALTER TABLE `playsms_tblSMSOutgoing_queue_dst`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `playsms_tblUser`
  ADD PRIMARY KEY (`uid`);


ALTER TABLE `playsms_featureAutoreply`
  MODIFY `autoreply_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureAutoreply_scenario`
  MODIFY `autoreply_scenario_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureBoard`
  MODIFY `board_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureBoard_log`
  MODIFY `in_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureCommand`
  MODIFY `command_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureCredit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureCustom`
  MODIFY `custom_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureFirewall`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureInboxgroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureInboxgroup_catchall`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureInboxgroup_log_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureInboxgroup_log_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureInboxgroup_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureMsgtemplate`
  MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `playsms_featureOutgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePhonebook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePhonebook_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePhonebook_group_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePoll`
  MODIFY `poll_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePoll_choice`
  MODIFY `choice_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featurePoll_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureQuiz`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureQuiz_log`
  MODIFY `answer_id` int(4) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSchedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSchedule_dst`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSendfromfile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSimplerate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSmssysnc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureStoplist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSubscribe`
  MODIFY `subscribe_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSubscribe_member`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_featureSubscribe_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayGeneric_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayInfobip_apidata`
  MODIFY `apidata_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayJasmin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayNexmo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayPlaynet_outgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewaySmstools_dlr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayTwilio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_gatewayUplink`
  MODIFY `up_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblACL`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `playsms_tblBilling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblCountry`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

ALTER TABLE `playsms_tblDLR`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblGateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `playsms_tblNotif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblPlaysmsd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblRecvSMS`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblRegistry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `playsms_tblSMSInbox`
  MODIFY `in_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblSMSIncoming`
  MODIFY `in_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblSMSOutgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblSMSOutgoing_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblSMSOutgoing_queue_dst`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `playsms_tblUser`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
