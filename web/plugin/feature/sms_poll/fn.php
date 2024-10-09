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
 * Implementations of hook keyword_isavail()
 *
 * @param string $keyword keyword_isavail() will insert keyword for checking to the hook here

 * @return bool true if keyword is available
 */
function sms_poll_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT poll_id FROM " . _DB_PREF_ . "_featurePoll WHERE poll_keyword=?";
	if (dba_num_rows($db_query, [$keyword])) {

		return false;
	}

	return true;
}

/**
 * Implementations of hook recvsms_process()
 *
 * @param string $sms_datetime date and time when incoming sms inserted to playsms
 * @param string $sms_sender sender on incoming sms
 * @param string $poll_keyword check if keyword is for sms_poll
 * @param string $poll_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status, true if incoming sms handled
 */
function sms_poll_hook_recvsms_process($sms_datetime, $sms_sender, $poll_keyword, $poll_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$poll_keyword = strtoupper(core_sanitize_alphanumeric($poll_keyword));
	$poll_param = trim($poll_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePoll WHERE poll_keyword=?";
	$db_result = dba_query($db_query, [$poll_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($uid = $db_row['uid'] && $poll_id = $db_row['poll_id'] && $db_row['poll_enable']) {
			_log('begin poll_id:' . $poll_id . ' k:' . $poll_keyword . ' c:' . $poll_param, 2, 'sms_poll');
			if (sms_poll_handle($db_row, $sms_datetime, $sms_sender, $poll_keyword, $poll_param, $sms_receiver, $smsc, $raw_message)) {

				$status = true;
			}
			$status_text = $status ? 'handled' : 'unhandled';
			_log('end poll_id:' . $poll_id . ' k:' . $poll_keyword . ' c:' . $poll_param . ' s:' . $status_text, 2, 'sms_poll');
		}
	}
	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

function sms_poll_handle($list, $sms_datetime, $sms_sender, $poll_keyword, $poll_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$smsc = gateway_decide_smsc($smsc, $list['smsc']);
	$poll_param = strtoupper($poll_param);
	$choice_keyword = $poll_param;

	if (!($sms_sender && $poll_keyword && $choice_keyword)) {

		return false;
	}

	$poll_id = $list['poll_id'];

	// if poll disabled then immediately return, just ignore the vote
	if (!$list['poll_enable']) {
		_log('poll disabled poll_id:' . $poll_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

		return true;
	}

	$choice_id = 0;
	$db_query = "SELECT choice_id FROM " . _DB_PREF_ . "_featurePoll_choice WHERE choice_keyword=? AND poll_id=?";
	$db_result = dba_query($db_query, [$choice_keyword, $poll_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$choice_id = (int) $db_row['choice_id'];
	} else {
		_log('choice not found poll_id:' . $poll_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

		return true;
	}

	$db_table = _DB_PREF_ . "_featurePoll_log";
	$items = [
		'poll_id' => $poll_id,
		'choice_id' => $choice_id,
		'poll_sender' => $sms_sender,
		'in_datetime' => core_get_datetime(),
		'status' => 0
	];
	// status 0 = failed/unknown
	// status 1 = valid
	// status 2 = out of vote option
	// status 3 = invalid
	$log_id = dba_add($db_table, $items);

	if (!($poll_id && $choice_id && $log_id)) {
		// send message invalid
		if (
			dba_update($db_table, [
				'status' => 3
			], [
				'log_id' => $log_id
			])
		) {
			_log('invalid vote poll_id:' . $poll_id . ' choice_id:' . $choice_id . ' log_id:' . $log_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

			if ($poll_message_invalid = trim($list['poll_message_invalid']) && $c_username = user_uid2username($list['uid'])) {
				$unicode = core_detect_unicode($poll_message_invalid);
				$poll_message_invalid = addslashes($poll_message_invalid);
				list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_invalid, 'text', $unicode, $smsc);
			}
		}

		return true;
	}

	if (sms_poll_check_option_vote($list, $sms_sender, $poll_keyword, $choice_keyword)) {
		// send message valid
		if (
			dba_update($db_table, [
				'status' => 1
			], [
				'log_id' => $log_id
			])
		) {
			_log('valid vote poll_id:' . $poll_id . ' choice_id:' . $choice_id . ' log_id:' . $log_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

			if ($poll_message_valid = trim($list['poll_message_valid']) && $c_username = user_uid2username($list['uid'])) {
				$unicode = core_detect_unicode($poll_message_valid);
				$poll_message_valid = addslashes($poll_message_valid);
				list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_valid, 'text', $unicode, $smsc);
			}
		}
	} else {
		// send message out of vote option
		if (
			dba_update($db_table, [
				'status' => 2
			], [
				'log_id' => $log_id
			])
		) {
			_log('out of vote option poll_id:' . $poll_id . ' choice_id:' . $choice_id . ' log_id:' . $log_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

			if ($poll_message_option = trim($list['poll_message_option']) && $c_username = user_uid2username($list['uid'])) {
				$unicode = core_detect_unicode($poll_message_option);
				$poll_message_option = addslashes($poll_message_option);
				list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($c_username, $sms_sender, $poll_message_option, 'text', $unicode, $smsc);
			}
		}
	}

	return true;
}

function sms_poll_check_option_vote($list, $sms_sender, $poll_keyword, $choice_keyword)
{
	$poll_id = $list['poll_id'];
	$poll_option_vote = $list['poll_option_vote'];
	$c_sms_sender = substr($sms_sender, 3);

	$votes = 0;

	// check already vote
	$db_query = "SELECT in_datetime FROM " . _DB_PREF_ . "_featurePoll_log WHERE poll_sender LIKE ? AND poll_id=? AND status!=0 ORDER BY log_id DESC LIMIT 1";
	$db_result = dba_query($db_query, ['%' . $c_sms_sender, $poll_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		// yup, voted
		if ($poll_option_vote == 4) {
			_log('multiple vote poll_id:' . $poll_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

			return true;
		}
		$in_datetime = $db_row['in_datetime'];
		$votes = (int) dba_num_rows($db_query);
	} else {
		// nope, no vote, go ahead save it in the log
		_log('continue vote poll_id:' . $poll_id . ' s:' . $sms_sender . ' k:' . $poll_keyword . ' c:' . $choice_keyword, 2, 'sms_poll');

		return true;
	}

	if (!$votes) {

		return true;
	}

	switch ($poll_option_vote) {
		case 0: // one time
			_log('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote one time', 2, 'sms_poll');
			break;

		case 1: // one time every 24 hours
			$d = new DateTime($in_datetime);
			$day_in = $d->format("Ymd");
			$d = new DateTime(core_get_datetime());
			$day_current = $d->format("Ymd");
			if ($day_in && $day_current && ($day_in == $day_current)) {
				_log('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote today', 2, 'sms_poll');
			}
			break;

		case 2: // one time every week
			$d = new DateTime($in_datetime);
			$week_in = $d->format("YmW");
			$d = new DateTime(core_get_datetime());
			$week_current = $d->format("YmW");
			if ($week_in && $week_current && ($week_in == $week_current)) {
				_log('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote this week', 2, 'sms_poll');
			}
			break;

		case 3: // one time every month
			$d = new DateTime($in_datetime);
			$month_in = $d->format("Ym");
			$d = new DateTime(core_get_datetime());
			$month_current = $d->format("Ym");
			if ($month_in && $month_current && ($month_in == $month_current)) {
				_log('vote s:' . $sms_sender . 'k:' . $poll_keyword . ' c:' . $choice_keyword . ' option_vote:' . $poll_option_vote . ' vote_count:' . $votes . ' already vote this month', 2, 'sms_poll');
			}
			break;
	}

	return true;
}

function sms_poll_statistics($poll_id)
{
	$ret = [
		'once' => 0,
		'multi' => 0,
		'sender' => 0,
		'valid' => 0,
		'invalid' => 0,
		'all' => 0
	];

	$db_table = _DB_PREF_ . '_featurePoll_log';

	// once, once_sms, multi, multi_sms, sender, valid
	$once = 0;
	$once_sms = 0;
	$multi = 0;
	$multi_sms = 0;
	$db_query = "SELECT poll_sender,count(*) AS count FROM " . $db_table . " WHERE poll_id=? AND status=1 GROUP BY poll_sender";
	$db_result = dba_query($db_query, [$poll_id]);
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
	$db_query = "SELECT count(*) AS count FROM " . $db_table . " WHERE poll_id=? AND (status=2 OR status=3)";
	$db_result = dba_query($db_query, [$poll_id]);
	$db_row = dba_fetch_array($db_result);
	$ret['invalid'] = (int) $db_row['count'];

	// total
	$ret['all'] = $ret['valid'] + $ret['invalid'];

	return $ret;
}

function sms_poll_output_serialize($poll_id, $poll_keyword)
{
	$list = dba_search(_DB_PREF_ . '_featurePoll_choice', '*', [
		'poll_id' => $poll_id
	]);
	$poll_choices = [];
	$choice_ids = [];
	for ($i = 0; $i < count($list); $i++) {
		$c_keyword = $list[$i]['choice_keyword'];
		$c_title = $list[$i]['choice_title'];
		$poll_choices[$c_keyword] = $c_title;
		$choice_ids[$c_keyword] = $list[$i]['choice_id'];
	}
	$poll_results = [];
	$votes = 0;
	foreach ( $choice_ids as $key => $val ) {
		$c_num = dba_count(_DB_PREF_ . '_featurePoll_log', [
			'poll_id' => $poll_id,
			'choice_id' => $val,
			'status' => 1
		]);
		$poll_results[$key] = (int) $c_num ? $c_num : 0;
		$votes += $c_num;
	}
	$ret['keyword'] = $poll_keyword;
	$ret['votes'] = $votes;
	$ret['choices'] = $poll_choices;
	$ret['results'] = $poll_results;
	$ret = serialize($ret);

	return $ret;
}

function sms_poll_output_json($poll_id, $poll_keyword)
{
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));

	$ret = json_encode($data);

	return $ret;
}

function sms_poll_output_xml($poll_id, $poll_keyword)
{
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));

	$ret = "<?xml version=\"1.0\"?>\n";
	$ret .= "<poll>\n";
	$ret .= "<keyword>" . $poll_keyword . "</keyword>\n";
	$ret .= "<votes>" . $data['votes'] . "</votes>\n";
	foreach ( $data['choices'] as $key => $val ) {
		$poll_choices .= "<item key=\"" . $key . "\">" . $val . "</item>\n";
	}
	$ret .= "<choices>" . $poll_choices . "</choices>\n";
	foreach ( $data['results'] as $key => $val ) {
		$poll_results .= "<item key=\"" . $key . "\">" . $val . "</item>\n";
	}
	$ret .= "<results>" . $poll_results . "</results>\n";
	$ret .= "</poll>\n";

	return $ret;
}

function sms_poll_output_html($poll_id, $poll_keyword)
{
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));

	$ret = "
		<table id=\"sms-poll-" . strtolower($poll_keyword) . "\" class=\"playsms-table sms-poll\">
		<thead>
		<tr>
			<th id=\"title-keyword\">" . _('Choice keyword') . "</th>
			<th id=\"title-description\">" . _('Description') . "</th>
			<th id=\"title-result\">" . _('Number of votes') . "</th>
		</tr>
		</thead>
		<tbody>";
	foreach ( $data['choices'] as $key => $val ) {
		$ret .= "
			<tr id=\"sms-poll-choice-keyword-" . strtolower($key) . "\">
				<td id=\"keyword\">" . $key . "</td>
				<td id=\"description\">" . $val . "</td>
				<td id=\"result\">" . $data['results'][$key] . "</td>
			</tr>";
	}
	$ret .= "
		</tbody></table>
	";

	return $ret;
}

function sms_poll_hook_webservices_output($operation, $requests, $returns)
{
	global $core_config;

	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}

	if (!($operation == 'sms_poll' && $keyword)) {

	}

	$code = $requests['code'];

	if ($operation == 'sms_poll' && $poll_keyword = $keyword) {
		$list = dba_search(_DB_PREF_ . '_featurePoll', 'poll_id,poll_access_code', [
			'poll_keyword' => $poll_keyword
		]);
		$poll_id = $list[0]['poll_id'];
		$poll_access_code = $list[0]['poll_access_code'];
	}

	if ($poll_id && $code && ($code == $poll_access_code) && ($type = $requests['type'])) {
		switch ($type) {
			case 'serialize':
				if ($content = sms_poll_output_serialize($poll_id, $poll_keyword)) {

					$returns['modified'] = true;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/plain';
				}
				break;
			case 'json':
				if ($content = sms_poll_output_json($poll_id, $poll_keyword)) {

					$returns['modified'] = true;
					$returns['param']['content'] = $content;
				}
				break;
			case 'xml':
				if ($content = sms_poll_output_xml($poll_id, $poll_keyword)) {

					$returns['modified'] = true;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/xml';
				}
				break;
			case 'html':
				if ($content = sms_poll_output_html($poll_id, $poll_keyword)) {

					$returns['modified'] = true;
					$returns['param']['content'] = $content;
					$returns['param']['content-type'] = 'text/html';
				}
				break;
		}
	}

	return $returns;
}

function sms_poll_export_csv($poll_id, $poll_keyword)
{
	$ret = '';

	// header
	$items = [
		[
			_('SMS poll keyword'),
			$poll_keyword
		]
	];
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";

	// statistics
	$stat = sms_poll_statistics($poll_id);
	$items = [
		[
			_('Senders sent once'),
			$stat['once']
		],
		[
			_('Senders sent multiple votes'),
			$stat['multi']
		],
		[
			_('Grand total senders'),
			$stat['sender']
		],
		[
			_('Total one time vote SMS'),
			$stat['once_sms']
		],
		[
			_('Total multiple votes SMS'),
			$stat['multi_sms']
		],
		[
			_('Total valid SMS'),
			$stat['valid']
		],
		[
			_('Total invalid SMS'),
			$stat['invalid']
		],
		[
			_('Grand total SMS'),
			$stat['all']
		]
	];
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";

	// choices
	$data = unserialize(sms_poll_output_serialize($poll_id, $poll_keyword));
	$items[0] = [
		_('Choice keyword'),
		_('Description'),
		_('Number of votes')
	];
	$i = 1;
	foreach ( $data['choices'] as $key => $val ) {
		$items[$i] = [
			$key,
			$val,
			$data['results'][$key]
		];
		$i++;
	}
	$ret .= core_csv_format($items);
	unset($items);
	$ret .= "\n";

	return $ret;
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_poll_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featurePoll', 'poll_id');
}