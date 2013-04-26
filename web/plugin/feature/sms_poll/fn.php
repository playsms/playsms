<?php
defined('_SECURE_') or die('Forbidden');

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
 */
function sms_poll_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT poll_id FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = false;
	}
	return $ok;
}

/*
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime
 *   date and time when incoming sms inserted to playsms
 * @param $sms_sender
 *   sender on incoming sms
 * @param $poll_keyword
 *   check if keyword is for sms_poll
 * @param $poll_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_poll_hook_setsmsincomingaction($sms_datetime,$sms_sender,$poll_keyword,$poll_param='',$sms_receiver='',$raw_message='') {
	$ok = false;
	$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$poll_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['uid'] && $db_row['poll_enable']) {
			logger_print('begin k:'.$poll_keyword.' c:'.$poll_param, 2, 'sms_poll');
			if (sms_poll_handle($db_row,$sms_datetime,$sms_sender,$poll_keyword,$poll_param,$sms_receiver,$raw_message)) {
				$ok = true;
			}
			$status = ( $ok ? 'handled' : 'unhandled' );
			logger_print('end k:'.$poll_keyword.' c:'.$poll_param.' s:'.$status, 2, 'sms_poll');
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_poll_handle($list,$sms_datetime,$sms_sender,$poll_keyword,$poll_param='',$sms_receiver='',$raw_message='') {
	global $datetime_now;
	$ok = false;
	$poll_keyword = strtoupper($poll_keyword);
	$choice_keyword = strtoupper($poll_param);
	if ($sms_sender && $poll_keyword && $choice_keyword) {
		$poll_id = $list['poll_id'];
		$db_query = "SELECT choice_id FROM "._DB_PREF_."_featurePoll_choice WHERE choice_keyword='$choice_keyword' AND poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$choice_id = $db_row['choice_id'];
		if ($poll_id && $choice_id) {
			$c_sms_sender = substr($sms_sender, 3);
			$db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_sender LIKE '%$c_sms_sender' AND poll_id='$poll_id'";
			$vote = @dba_num_rows($db_query);
			$poll_enable = $list['poll_enable'];
			logger_print('vote k:'.$poll_keyword.' c:'.$choice_keyword.' already:'.$vote.' enable:'.$poll_enable, 2, 'sms_poll');
			if ((! $vote) && $poll_enable) {
				$db_query = "
					INSERT INTO "._DB_PREF_."_featurePoll_log 
					(poll_id,choice_id,poll_sender,in_datetime) 
					VALUES ('$poll_id','$choice_id','$sms_sender','$datetime_now')";
				if (($new_id = @dba_insert_id($db_query)) && ($c_username = uid2username($list['uid']))) {
					if ($poll_message_valid = $list['poll_message_valid']) {
						$unicode = core_detect_unicode($poll_message_valid);
						list($ok, $to, $smslog_id, $queue_code) = sendsms($c_username, $sms_sender, $poll_message_valid, 'text', $unicode);
					}
					$ok = true;
				}
			}
		} else {
			if (($poll_message_invalid = $list['poll_message_invalid']) && ($c_username = uid2username($list['uid']))) {
				$unicode = core_detect_unicode($poll_message_invalid);
				list($ok, $to, $smslog_id, $queue_code) = sendsms($c_username, $sms_sender, $poll_message_invalid, 'text', $unicode);
			}
		}
	}
	return $ok;
}

function sms_poll_hook_webservices_output($ta,$requests) {
	global $http_path, $themes_module;
	$keyword = $requests['keyword'];
	$db_query = "SELECT poll_id,poll_title FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_id = $db_row['poll_id'];
	$poll_title = $db_row['poll_title'];
	$db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id'";
	$total_voters = @dba_num_rows($db_query);

	if ($poll_id) {
		$mult = $requests['mult'];
		$bodybgcolor = $requests['bodybgcolor'];
		if (!isset($mult)) {
			$mult = "2";
		}
		if (!isset($bodybgcolor)) {
			$bodybgcolor = "#FEFEFE";
		}
		$content = "
			<html>
			<head>
				<title>$web_title</title>
				<meta name=\"author\" content=\"http://playsms.org\">
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".$http_path['themes']."/".$themes_module."/jscss/common.css\">
			</head>
			<body bgcolor=\"gray\" topmargin=\"0\" leftmargin\"0\">";
		$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
		$db_result = dba_query($db_query);
		$results= "";
		$answers = "";
		$no_results="";
		while ($db_row = dba_fetch_array($db_result)) {
			$choice_id = $db_row['choice_id'];
			$choice_title = $db_row['choice_title'];
			$answers .= $choice_title . ",";
			$choice_keyword = $db_row['choice_keyword'];
			$db_query1 = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
			$choice_voted = @dba_num_rows($db_query1);
			$results .= $choice_voted . ",";
			$no_results .= "0,";
		}
		
		$answers = substr_replace($answers,"",-1);
		$results = substr_replace($results,"",-1);
		$no_results = substr_replace($no_results,"",-1);
		if ($results == $no_results) {
			$content .= "<br />"._('This poll has 0 votes!');
		} else {
			$content .= "
				<iframe width=\"900\" height=\"500\" frameborder=\"0\" 
					src=\"plugin/feature/sms_poll/graph_poll.php?results=$results&answers=".urlencode($answers)."\">
				</iframe>";
		}
		$content .= "</body></html>";
		$ret = $content;
	}
	return $ret;
}

?>