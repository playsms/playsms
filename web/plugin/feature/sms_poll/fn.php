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
	$ret['uid'] = $db_row['uid'];
	$ret['status'] = $ok;
	return $ret;
}

function sms_poll_handle($list,$sms_datetime,$sms_sender,$poll_keyword,$poll_param='',$sms_receiver='',$raw_message='') {
	$ok = false;
	$poll_keyword = strtoupper(trim($poll_keyword));
	$poll_param = strtoupper(trim($poll_param));
	$choice_keyword = $poll_param;
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
					VALUES ('$poll_id','$choice_id','$sms_sender','".core_get_datetime()."')";
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

function sms_poll_output_serialize($poll_keyword, $list) {
	$poll_id = $list[0]['poll_id'];
	$list2 = dba_search(_DB_PREF_.'_featurePoll_choice', '*', array('poll_id' => $poll_id));
	$poll_choices = array();
	for ($i=0;$i<count($list2);$i++) {
		$c_keyword = $list2[$i]['choice_keyword'];
		$c_title = $list2[$i]['choice_title'];
		$poll_choices[$c_keyword] = $c_title;
		$choice_ids[$c_keyword] = $list2[$i]['choice_id'];
	}
	$poll_results = array();
	$votes = 0;
	foreach ($choice_ids as $key => $val) {
		$c_num = dba_count(_DB_PREF_.'_featurePoll_log', array('poll_id' => $poll_id, 'choice_id' => $val));
		$poll_results[$key] = ( (int)$c_num ? $c_num : 0 );
		$votes += $c_num;
	}
	$ret['keyword'] = $poll_keyword;
	$ret['votes'] = $votes;
	$ret['choices'] = $poll_choices;
	$ret['results'] = $poll_results;
	$ret = serialize($ret);
	return $ret;
}

function sms_poll_output_json($keyword, $list) {
	$ret = unserialize(sms_poll_output_serialize($keyword, $list));
	$ret = json_encode($ret);
	return $ret;
}

function sms_poll_output_xml($keyword, $list) {
	$data = unserialize(sms_poll_output_serialize($keyword, $list));
	$ret = "<?xml version=\"1.0\"?>\n";
	$ret .= "<poll>\n";
	$ret .= "<keyword>".$keyword."</keyword>\n";
	$ret .= "<votes>".$data['votes']."</votes>\n";
	foreach ($data['choices'] as $key => $val) {
		$poll_choices .= "<item key=\"".$key."\">".$val."</item>\n";
	}
	$ret .= "<choices>".$poll_choices."</choices>\n";
	foreach ($data['results'] as $key => $val) {
		$poll_results .= "<item key=\"".$key."\">".$val."</item>\n";
	}
	$ret .= "<results>".$poll_results."</results>\n";
	$ret .= "</poll>\n";
	return $ret;
}

function sms_poll_output_graph($keyword, $list) {
	global $core_config;
	$ret = unserialize(sms_poll_output_serialize($keyword, $list));
	$choices = $ret['choices'];
	$results = $ret['results'];
	include $core_config['apps_path']['plug'].'/feature/sms_poll/graph_poll.php';
	exit();
}

function sms_poll_hook_webservices_output($ta,$requests) {
	global $core_config;
	$ret = '';
	if ($keyword = $requests['keyword']) {
		$list = dba_search(_DB_PREF_.'_featurePoll', 'poll_id', array('poll_keyword' => $keyword));
		$poll_id = $list[0]['poll_id'];
	}
	if ($poll_id) {
		$type = $requests['type'];
		switch ($type) {
			case 'serialize':
				$ret = sms_poll_output_serialize($keyword, $list);
				break;
			case 'json':
				$ret = sms_poll_output_json($keyword, $list);
				break;
			case 'xml':
				ob_end_clean();
				header('Content-type: text/xml');
				$ret = sms_poll_output_xml($keyword, $list);
				break;
			case 'graph':
				$ret = sms_poll_output_graph($keyword, $list);
				break;
		}
	}
	return $ret;
}

?>