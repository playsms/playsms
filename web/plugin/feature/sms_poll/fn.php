<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

/**
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword checkavailablekeyword()
 *        will insert keyword for checking to the hook here
 * @return TRUE if keyword is available
 */
function sms_poll_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT poll_id FROM " . _DB_PREF_ . "_featurePoll WHERE poll_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = false;
	}
	return $ok;
}

/**
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime date
 *        and time when incoming sms inserted to playsms
 * @param $sms_sender sender
 *        on incoming sms
 * @param $poll_keyword check
 *        if keyword is for sms_poll
 * @param $poll_param get
 *        parameters from incoming sms
 * @param $sms_receiver receiver
 *        number that is receiving incoming sms
 * @return $ret array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_poll_hook_setsmsincomingaction($sms_datetime, $sms_sender, $poll_keyword, $poll_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	$ok = false;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePoll WHERE poll_keyword='$poll_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['uid'] && $db_row['poll_enable']) {
			logger_print('begin k:' . $poll_keyword . ' c:' . $poll_param, 2, 'sms_poll');
			if (sms_poll_handle($db_row, $sms_datetime, $sms_sender, $poll_keyword, $poll_param, $sms_receiver, $smsc, $raw_message)) {
				$ok = true;
			}
			$status = ($ok ? 'handled' : 'unhandled');
			logger_print('end k:' . $poll_keyword . ' c:' . $poll_param . ' s:' . $status, 2, 'sms_poll');
		}
	}
	$ret['uid'] = $db_row['uid'];
	$ret['status'] = $ok;
	return $ret;
}

function sms_poll_handle($list, $sms_datetime, $sms_sender, $poll_keyword, $poll_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	$ok = false;
	$smsc = gateway_decide_smsc($smsc, $list['smsc']);
	$poll_keyword = strtoupper(trim($poll_keyword));
	$poll_param = strtoupper(trim($poll_param));
	$choice_keyword = $poll_param;
	if ($sms_sender && $poll_keyword && $choice_keyword) {
		$poll_id = $list['poll_id'];
		
		// if poll disabled then immediately return, just ignore the vote
		if (!$list['poll_enable']) {
			logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' poll disabled', 2, 'sms_poll');
			return TRUE;
		}
		
		$db_query = "SELECT choice_id FROM " . _DB_PREF_ . "_featurePoll_choice WHERE choice_keyword='$choice_keyword' AND poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$choice_id = (int) $db_row['choice_id'];
		
		$db_table = _DB_PREF_ . "_featurePoll_log";
		$items = array(
			'poll_id' => $poll_id,
			'choice_id' => $choice_id,
			'poll_sender' => $sms_sender,
			'in_datetime' => core_get_datetime(),
			'status' => 0 
		);
		// status 0 = failed/unknown
		// status 1 = valid
		// status 2 = out of vote option
		// status 3 = invalid
		$log_id = dba_add($db_table, $items);
		
		if ($poll_id && $choice_id) {
			
			$continue = sms_poll_check_option_vote($list, $sms_sender, $poll_keyword, $choice_keyword);
			
			if ($continue) {
				// send message valid
				if (dba_update($db_table, array(
					'status' => 1 
				), array(
					'log_id' => $log_id 
				))) {
					logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' log_id:' . $log_id . ' valid vote', 2, 'sms_poll');
					if (($poll_message_valid = trim($list['poll_message_valid'])) && ($c_username = user_uid2username($list['uid']))) {
						$unicode = core_detect_unicode($poll_message_valid);
						$poll_message_valid = addslashes($poll_message_valid);
						list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_valid, 'text', $unicode, $smsc);
					}
				}
			} else {
				// send message out of vote option
				if (dba_update($db_table, array(
					'status' => 2 
				), array(
					'log_id' => $log_id 
				))) {
					logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' log_id:' . $log_id . ' out of vote option', 2, 'sms_poll');
					if (($poll_message_option = trim($list['poll_message_option'])) && ($c_username = user_uid2username($list['uid']))) {
						$unicode = core_detect_unicode($poll_message_option);
						$poll_message_option = addslashes($poll_message_option);
						list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_option, 'text', $unicode, $smsc);
					}
				}
			}
			$ok = true;
		} else {
			// send message invalid
			if (dba_update($db_table, array(
				'status' => 3 
			), array(
				'log_id' => $log_id 
			))) {
				logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' log_id:' . $log_id . ' invalid vote', 2, 'sms_poll');
				if (($poll_message_invalid = trim($list['poll_message_invalid'])) && ($c_username = user_uid2username($list['uid']))) {
					$unicode = core_detect_unicode($poll_message_invalid);
					$poll_message_invalid = addslashes($poll_message_invalid);
					list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_invalid, 'text', $unicode, $smsc);
				}
			}
		}
	}
	
	return $ok;
}

function sms_poll_check_option_vote($list, $sms_sender, $poll_keyword, $choice_keyword) {
	$poll_id = $list['poll_id'];
	$poll_option_vote = $list['poll_option_vote'];
	$c_sms_sender = substr($sms_sender, 3);
	
	// check already vote
	$db_query = "SELECT in_datetime FROM " . _DB_PREF_ . "_featurePoll_log WHERE poll_sender LIKE '%$c_sms_sender' AND poll_id='$poll_id' AND status!=0 ORDER BY log_id DESC LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		// yup, voted
		if ($poll_option_vote == 4) {
			logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' vote multiple', 2, 'sms_poll');
			return TRUE;
		}
		$in_datetime = $db_row['in_datetime'];
		$votes = (int) @dba_num_rows($db_query);
	} else {
		// nope, go ahead save it in the log
		logger_print('vote s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword . ' continue', 2, 'sms_poll');
		return TRUE;
	}
	
	$continue = TRUE;
	
	switch ($poll_option_vote) {
		case 0: // one time
			if ($votes) {
				logger_print('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote one time', 2, 'sms_poll');
				$continue = FALSE;
			}
			break;
		
		case 1: // one time every 24 hours
			if ($votes) {
				$d = new DateTime($in_datetime);
				$day_in = $d->format("Ymd");
				$d = new DateTime(core_get_datetime());
				$day_current = $d->format("Ymd");
				if ($day_in && $day_current && ($day_in == $day_current)) {
					logger_print('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote today', 2, 'sms_poll');
					$continue = FALSE;
				}
			}
			break;
		
		case 2: // one time every week
			if ($votes) {
				$d = new DateTime($in_datetime);
				$week_in = $d->format("YmW");
				$d = new DateTime(core_get_datetime());
				$week_current = $d->format("YmW");
				if ($week_in && $week_current && ($week_in == $week_current)) {
					logger_print('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote this week', 2, 'sms_poll');
					$continue = FALSE;
				}
			}
			break;
		
		case 3: // one time every month
			if ($votes) {
				$d = new DateTime($in_datetime);
				$month_in = $d->format("Ym");
				$d = new DateTime(core_get_datetime());
				$month_current = $d->format("Ym");
				if ($month_in && $month_current && ($month_in == $month_current)) {
					logger_print('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote this month', 2, 'sms_poll');
					$continue = FALSE;
				}
			}
			break;
	}
	
	return $continue;
}

function sms_poll_statistics($poll_id) {
	$ret = array(
		'once' => 0,
		'multi' => 0,
		'sender' => 0,
		'valid' => 0,
		'invalid' => 0,
		'all' => 0 
	);
	
	$db_table = _DB_PREF_ . '_featurePoll_log';
	
	// once, once_sms, multi, multi_sms, sender, valid
	$once = 0;
	$once_sms = 0;
	$multi = 0;
	$multi_sms = 0;
	$sender = 0;
	$valid = 0;
	$db_query = "
			SELECT poll_sender,count(*) AS count FROM " . $db_table . "
			WHERE poll_id='" . $poll_id . "' AND status='1'
			GROUP BY poll_sender";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['count'] == 1) {
			$once++;
			$once_sms++;
		} else if ($db_row['count'] > 1) {
			$multi++;
			$multi_sms += $db_row['count'];
		}
	}
	$ret['once'] = $once;
	$ret['once_sms'] = $once_sms;
	$ret['multi'] = $multi;
	$ret['multi_sms'] = $multi_sms;
	$ret['sender'] = $once + $multi;
	$ret['valid'] = $once_sms + $multi_sms;
	
	// invalid
	$db_query = "
			SELECT count(*) AS count FROM " . $db_table . " 
			WHERE poll_id='" . $poll_id . "' AND (status='2' OR status='3')";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$ret['invalid'] = (int) $db_row['count'];
	
	// total
	$ret['all'] = $ret['valid'] + $ret['invalid'];
	
	return $ret;
}

function sms_poll_output_serialize($poll_id, $poll_keyword) {
	$list2 = dba_search(_DB_PREF_ . '_featurePoll_choice', '*', array(
		'poll_id' => $poll_id 
	));
	$poll_choices = array();
	for ($i = 0; $i < count($list2); $i++) {
		$c_keyword = $list2[$i]['choice_keyword'];
		$c_title = $list2[$i]['choice_title'];
		$poll_choices[$c_keyword] = $c_title;
		$choice_ids[$c_keyword] = $list2[$i]['choice_id'];
	}
	$poll_results = array();
	$votes = 0;
	foreach ($choice_ids as $key => $val) {
		$c_num = dba_count(_DB_PREF_ . '_featurePoll_log', array(
			'poll_id' => $poll_id,
			'choice_id' => $val,
			'status' => 1 
		));
		$poll_results[$key] = ((int) $c_num ? $c_num : 0);
		$votes += $c_num;
	}
	$ret['keyword'] = $poll_keyword;
	$ret['votes'] = $votes;
	$ret['choices'] = $poll_choices;
	$ret['results'] = $poll_results;
	$ret = serialize($ret);
	
	return $ret;
}

function sms_poll_output_json($poll_id, $poll_keyword) {
	$ret = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$ret = json_encode($ret);
	return $ret;
}

function sms_poll_output_xml($poll_id, $poll_keyword) {
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$ret = "<?xml version=\"1.0\"?>\n";
	$ret .= "<poll>\n";
	$ret .= "<keyword>" . $poll_keyword . "</keyword>\n";
	$ret .= "<votes>" . $data['votes'] . "</votes>\n";
	foreach ($data['choices'] as $key => $val) {
		$poll_choices .= "<item key=\"" . $key . "\">" . $val . "</item>\n";
	}
	$ret .= "<choices>" . $poll_choices . "</choices>\n";
	foreach ($data['results'] as $key => $val) {
		$poll_results .= "<item key=\"" . $key . "\">" . $val . "</item>\n";
	}
	$ret .= "<results>" . $poll_results . "</results>\n";
	$ret .= "</poll>\n";
	return $ret;
}

function sms_poll_output_html($poll_id, $poll_keyword) {
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$ret = "
			<table class=playsms-table>
			<thead>
			<tr>
				<th class=label-sizer>" . _('Choice keyword') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('Number of votes') . "</th>
			</tr>
			</thead>
			<tbody>";
	foreach ($data['choices'] as $key => $val) {
		$ret .= "
				<tr>
					<td>" . $key . "</td>
					<td>" . $val . "</td>
					<td>" . $data['results'][$key] . "</td>
				</tr>";
	}
	$ret .= "</tbody></table>";
	return $ret;
}

function sms_poll_output_graph($poll_id, $poll_keyword) {
	global $core_config;
	$ret = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$choices = $ret['choices'];
	$results = $ret['results'];
	include $core_config['apps_path']['plug'] . '/feature/sms_poll/graph_poll.php';
	exit();
}

function sms_poll_hook_webservices_output($operation, $requests, $returns) {
	global $core_config;
	
	$ret = '';
	
	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}
	
	if (!($operation == 'sms_poll' && $keyword)) {
		return FALSE;
	}
	
	$code = $requests['code'];
	
	if ($operation == 'sms_poll' && $poll_keyword = $keyword) {
		$list = dba_search(_DB_PREF_ . '_featurePoll', 'poll_id,poll_access_code', array(
			'poll_keyword' => $poll_keyword 
		));
		$poll_id = $list[0]['poll_id'];
		$poll_access_code = $list[0]['poll_access_code'];
	}
	
	if ($poll_id && $code && ($code == $poll_access_code) && ($type = $requests['type'])) {
		switch ($type) {
			case 'serialize':
				if ($content = sms_poll_output_serialize($poll_id, $poll_keyword)) {
					$returns['modified'] = TRUE;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/plain';
				}
				break;
			case 'json':
				if ($content = sms_poll_output_json($poll_id, $poll_keyword)) {
					$returns['modified'] = TRUE;
					$returns['param']['content'] = $content;
				}
				break;
			case 'xml':
				if ($content = sms_poll_output_xml($poll_id, $poll_keyword)) {
					$returns['modified'] = TRUE;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/xml';
				}
				break;
			case 'html':
				if ($content = sms_poll_output_html($poll_id, $poll_keyword)) {
					$returns['modified'] = TRUE;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/html';
				}
				break;
			case 'graph':
				if ($content = sms_poll_output_graph($poll_id, $poll_keyword)) {
					$returns['modified'] = TRUE;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'image/png';
				}
				break;
		}
	}
	
	return $returns;
}

function sms_poll_export_csv($poll_id, $poll_keyword) {
	$ret = '';
	
	// header
	$items = array(
		array(
			_('SMS poll keyword'),
			$poll_keyword 
		) 
	);
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";
	
	// statistics
	$stat = sms_poll_statistics($poll_id);
	$items = array(
		array(
			_('Senders sent once'),
			$stat['once'] 
		),
		array(
			_('Senders sent multiple votes'),
			$stat['multi'] 
		),
		array(
			_('Grand total senders'),
			$stat['sender'] 
		),
		array(
			_('Total one time vote SMS'),
			$stat['once_sms'] 
		),
		array(
			_('Total multiple votes SMS'),
			$stat['multi_sms'] 
		),
		array(
			_('Total valid SMS'),
			$stat['valid'] 
		),
		array(
			_('Total invalid SMS'),
			$stat['invalid'] 
		),
		array(
			_('Grand total SMS'),
			$stat['all'] 
		) 
	);
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";
	
	// choices
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$items[0] = array(
		_('Choice keyword'),
		_('Description'),
		_('Number of votes') 
	);
	$i = 1;
	foreach ($data['choices'] as $key => $val) {
		$items[$i] = array(
			$key,
			$val,
			$data['results'][$key] 
		);
		$i++;
	}
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";
	
	return $ret;
}
