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
 * Verify uploaded CSV file
 *
 * @param  string $csv_file CSV file
 * @return array
 */
function sendfromfile_verify($csv_file)
{
	global $user_config, $plugin_config;

	$all_numbers = array();
	$item_valid = array();
	$item_discharged = array();
	$valid = 0;
	$discharged = 0;
	$num_of_rows = 0;
	$charges = (float) 0;
	$sendfromfile_id = '';
	$error_strings = array();

	// CSV file must exists and has content
	if ($csv_file && file_exists($csv_file)) {
		$csv_file_size = filesize($csv_file);
	} else {
		_log("CSV file not found or empty file:" . $csv_file, 2, "sendfromfile_verify");
		$error_strings[] = _('Error occurred while verifying CSV file');

		return [$all_numbers, $item_valid, $item_discharged, $valid, $discharged, $num_of_rows, $sendfromfile_id, $error_strings];
	}

	// open CSV file
	if (!(($fd = fopen($csv_file, 'r')) !== false && ($sendfromfile_id = md5(uniqid('SID', true))))) {
		_log("fail to open CSV file file:" . $csv_file . " size:" . $csv_file_size . " sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_verify");
		$error_strings[] = _('Error occurred while opening CSV file');

		return [$all_numbers, $item_valid, $item_discharged, $valid, $discharged, $num_of_rows, $sendfromfile_id, $error_strings];
	}

	// mark start
	_log("start sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_verify");

	// check if admin once to save resources
	$is_admin = auth_isadmin() ? true : false;

	// load and verify data from CSV file
	$continue = true;
	while ((($data = fgetcsv($fd, $csv_file_size, ',')) !== false) && $continue) {
		$dup = false;

		// destination number
		$item_to = core_sanitize_sender($data[0]);

		// stripslashed message
		$item_msg = core_sanitize_string($data[1]);

		$skip = false;
		if ($is_admin) {
			if ($item_username = core_sanitize_username($data[2])) {
				// if supplied username is clean and it exists
				if (!($item_username == $data[2])) {
					// username filled but user not found
					$skip = true;
				}
			} else {
				// supplied username's got cleaned
				if ($data[2]) {
					$skip = true;
				} else {
					// username not defined or empty
					$item_username = $user_config['username'];
				}
			}
		} else {
			// not an admin
			$item_username = $user_config['username'];
		}

		// check dups
		$dup = (in_array($item_to, $all_numbers) ? true : false);

		// collect for dups check
		$all_numbers[] = $item_to;

		// sender's uid
		$sender_uid = (int) user_username2uid($item_username);

		// check unicode
		$unicode = core_detect_unicode($item_msg);

		// seperates valid and discharged SMS
		if ($item_to && $item_msg && $sender_uid && !$skip && !$dup) {
			// calculate charges			
			list($count, $rate, $charge) = rate_getcharges(
				$sender_uid,
				strlen($item_msg),
				$unicode,
				$item_to
			);

			// group all valid SMS by sender's uid
			$item_valid[$sender_uid][] = [
				'sms_to' => $item_to,
				'sms_msg' => $item_msg,
				'sms_username' => $item_username,
				'unicode' => (int) $unicode,
				'charge' => (float) $charge,
			];

			$valid++;
		} else {
			// group all discharged SMS by sender's uid
			$item_discharged[$sender_uid][] = [
				'sms_to' => $item_to,
				'sms_msg' => $item_msg,
				'sms_username' => $item_username,
				'unicode' => (int) $unicode,
				'charge' => (float) 0,
			];

			$discharged++;
		}

		// stop verifying when limit reached
		$num_of_rows = (int) ($valid + $discharged);
		if ($num_of_rows > (int) $plugin_config['feature']['sendfromfile']['row_limit']) {
			$error_strings[] = sprintf(_('Send from file limit of %d SMS have been reached'), (int) $plugin_config['feature']['sendfromfile']['row_limit']);

			$continue = false;
		}
	}
	// end while

	// mark collected from file to array
	_log("collected sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_verify");

	$charges = (float) 0;
	foreach ( $item_valid as $sender_uid => $item_data ) {
		// get total charges
		foreach ( $item_data as $key => $item ) {
			$charges += $item['charge'];
		}

		// cancel send if user does not have enough balance
		$sender_balance = credit_getbalance($sender_uid);
		if ($charges > $sender_balance) {
			// move it to discharged
			$item_discharged = array_merge($item_discharged, $item_valid);

			// recount
			$item_count = count($item_valid[$sender_uid]);
			$valid -= $item_count;
			$discharged += $item_count;

			// delete from valid
			unset($item_valid[$sender_uid]);

			_log("not enough balance sendfromfile_id:" . $sendfromfile_id . " uid:" . $user_config['uid'] . " sender_uid:" . $sender_uid . " sms_username:" . $item_data[0]['sms_username'] . " item_count:" . $item_count . " charges:" . $charges . " balance:" . $sender_balance, 2, "sendfromfile_verify");
			$error_strings[] = sprintf(_('%s do not have enough balance for sending %d SMS at %s credit'), $item_data[0]['sms_username'], $item_count, core_display_credit($charges));
		} else {

			// mark save collected rows to db when sender has enough balance
			_log("saving data to db for processing sendfromfile_id:" . $sendfromfile_id . " uid:" . $user_config['uid'] . " sender_uid:" . $sender_uid . " sms_username:" . $item_data[0]['sms_username'] . " item_count:" . count($item_data) . " charges:" . $charges . " balance:" . $sender_balance, 2, "sendfromfile_verify");

			// save to db for delivery
			foreach ( $item_data as $key => $item ) {
				$hash = md5($item['sms_uid'] . $item['sms_username'] . $item['sms_msg']);
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureSendfromfile 
					(uid, sid, sms_datetime, sms_to, sms_msg, sms_username, sms_uid, hash, unicode, charge, smslog_id, queue_code, status, flag_processed) 
					VALUES 
					(?,?,'" . core_get_datetime() . "',?,?,?,?,?,?,?,0,'',0,0)";
				$db_argv = [
					$user_config['uid'],
					$sendfromfile_id,
					$item['sms_to'],
					addslashes($item['sms_msg']),
					$item['sms_username'],
					$sender_uid,
					$hash,
					$item['unicode'],
					$item['charge']
				];
				if (dba_insert_id($db_query, $db_argv) === 0) {
					_log("fail to insert data u:" . $user_config['uid'] . ' to:' . $item['sms_to'], 2, "sendfromfile_verify");
				}
				//_log("debug db_query:[" . trim($db_query) . "]", 2, "sendfromfile_verify");
			}

			// mark saved			
			_log("saved sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_verify");
		}
	}

	if ($valid) {
		_log("found verified entries sendfromfile_id:" . $sendfromfile_id . " valid:" . $valid . " discharged:" . $discharged, 2, "sendfromfile_verify");
		$error_strings[] = sprintf(_('Found %d of %d valid entries'), $valid, $num_of_rows);
		if ($discharged) {
			$error_strings[] = sprintf(_('Found %d of %d discharged entries'), $discharged, $num_of_rows);
		}
	} else {
		_log("verified entries not found sendfromfile_id:" . $sendfromfile_id . " discharged:" . $discharged, 2, "sendfromfile_verify");
		$error_strings[] = sprintf(_('No valid entries found from %d uploaded rows'), $num_of_rows);

		sendfromfile_destroy($sendfromfile_id);
	}

	// mark finish
	_log("finish sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_verify");

	return [$all_numbers, $item_valid, $item_discharged, $valid, $discharged, $num_of_rows, $sendfromfile_id, $error_strings];
}

/**
 * Process send from file session
 *
 * @param  string $sendfromfile_id Send from file session ID
 * @return void
 */
function sendfromfile_process($sendfromfile_id)
{

	// send from file session ID
	if (!($sendfromfile_id = trim($sendfromfile_id))) {

		return;
	}

	// mark start
	_log("start sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_process");

	// set time limit to forever
	@set_time_limit(0);

	$data = array();

	// collect send from file data and group them by hash
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid=? AND flag_processed=0";
	$db_result = dba_query($db_query, [$sendfromfile_id]);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['sms_to'] && $db_row['sms_msg'] && $db_row['sms_username']) {
			$data[$db_row['hash']]['sms_to'][] = $db_row['sms_to'];
			$data[$db_row['hash']]['message'] = $db_row['sms_msg'];
			$data[$db_row['hash']]['username'] = $db_row['sms_username'];
			$data[$db_row['hash']]['unicode'] = (int) $db_row['unicode'];
		}
	}

	// send SMS
	foreach ( $data as $hash => $item ) {
		_log('process sendfromfile_id:' . $sendfromfile_id . ' hash:' . $item['hash'] . ' u:' . $item['username'] . ' m:[' . $item['message'] . '] to_count:' . count($item['sms_to']) . ' unicode:' . $item['unicode'], 3, 'sendfromfile_process');
		if ($item['username'] && $item['message'] && count($item['sms_to'])) {

			// send SMS to queue
			list($ok, $to, $smslog_id, $queue_code) = sendsms_helper($item['username'], $item['sms_to'], addslashes($item['message']), 'text', $item['unicode']);

			// update send from file data
			if (isset($to) && is_array($to)) {
				for ($i = 0; $i < count($to); $i++) {
					$db_query = "
						UPDATE " . _DB_PREF_ . "_featureSendfromfile 
						SET smslog_id=?, queue_code=?, status=?, flag_processed=1
						WHERE hash=? AND sms_to=?";
					$db_argv = [
						$smslog_id[$i],
						$queue_code[$i],
						$ok[$i],
						$hash,
						$to[$i]
					];
					if (dba_affected_rows($db_query, $db_argv) === 0) {
						_log("fail to send SMS smslog_id:" . $smslog_id[$i] . " queue:" . $queue_code[$i] . " to:" . $to[$i], 2, "sendfromfile_process");
					}
					//_log("i:" . $i . " db_query:[" . trim($db_query) . "]", 2, "sendfromfile_process");
				}
			}
		}
	}

	// mark finish
	_log("finish sendfromfile_id:" . $sendfromfile_id, 2, "sendfromfile_process");

	//sendfromfile_destroy($sendfromfile_id);
}

/**
 * Destroy send from file session
 *
 * @param  string $sendfromfile_id Send from file session ID
 * @return void
 */
function sendfromfile_destroy($sendfromfile_id)
{
	if (!($sendfromfile_id = trim($sendfromfile_id))) {

		return;
	}

	$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid=?";
	if (dba_affected_rows($db_query, [(int) $sendfromfile_id]) === 0) {
		_log("fail to delete data sid:" . $sendfromfile_id, 2, "sendfromfile_destroy");
	}
}

function sendfromfile_hook_playsmsd_once($command, $command_param)
{
	if (!($command == 'sendfromfile_process' && $sendfromfile_id = trim($command_param))) {

		return false;
	}

	_log('running once command:' . $command . ' param:' . $command_param, 2, 'sendfromfile_hook_playsmsd_once');

	sendfromfile_process($sendfromfile_id);

	return true;
}