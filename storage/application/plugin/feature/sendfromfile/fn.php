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
 * @return void
 */
function sendfromfile_verify($csv_file) {
	global $user_config, $sendfromfile_row_limit;

	$all_numbers = array();
	$item_valid = array();
	$item_invalid = array();
	$valid = 0;
	$invalid = 0;
	$num_of_rows = 0;
	$sendfromfile_id = '';

	if ($csv_file && file_exists($csv_file)) {
		$csv_file_size = filesize($csv_file);
	} else {

		return [$all_numbers, $item_valid, $item_invalid, $valid, $invalid, $num_of_rows, $sendfromfile_id];
	}

	if (($fd = fopen($csv_file, 'r')) !== false && ($sendfromfile_id = md5(uniqid('SID', true)))) {
		$continue = true;
		while ((($data = fgetcsv($fd, $csv_file_size, ',')) !== false) && $continue) {
			$dup = false;
			$data[0] = core_sanitize_sender($data[0]);
			$data[1] = core_sanitize_string($data[1]);

			$skip = false;
			// if admin
			if (auth_isadmin()) {
				if ($sms_username = core_sanitize_username($data[2])) {
					// if supplied username is clean and it exists
					if (($sms_username == $data[2]) && ($uid = user_username2uid($sms_username))) {
						// user found
						$data[2] = $sms_username;
					} else {
						// username filled but user not found
						$skip = true;
					}
				} else {
					// username not defined or empty
					$uid = $user_config['uid'];
					$data[2] = $user_config['username'];
				}
			} else {
				// not an admin
				$uid = $user_config['uid'];
				$data[2] = $user_config['username'];
			}

			// check dups
			$dup = (in_array($data[0], $all_numbers) ? true : false);

			// $data[0] : destination number
			// $data[1] : stripslashed message
			// $data[2] : sender's username

			if ($data[0] && $data[1] && $uid && !$skip && !$dup) {
				$all_numbers[] = $data[0];
				$db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid, sid, sms_datetime, sms_to, sms_msg, sms_username) ";
				$db_query .= "VALUES ('" . $uid . "','" . $sendfromfile_id . "','" . core_get_datetime() . "','" . $data[0] . "','" . addslashes($data[1]) . "','" . $data[2] . "')";
				if (dba_insert_id($db_query)) {
					$item_valid[$valid] = $data;
					$valid++;
				} else {
					$item_invalid[$invalid] = $data;
					$invalid++;
				}
			} else if (($data[0] || $data[1]) && !$dup) {
				$item_invalid[$invalid] = $data;
				$invalid++;
			}
			$num_of_rows = $valid + $invalid;
			if ($num_of_rows >= $sendfromfile_row_limit) {

				$continue = false;
			}
		}
	}

	if (!$valid) {
		sendfromfile_destroy($sendfromfile_id);
	}

	return [$all_numbers, $item_valid, $item_invalid, $valid, $invalid, $num_of_rows, $sendfromfile_id];
}

/**
 * Process send from file session
 *
 * @param  string $sendfromfile_id Send from file session ID
 * @return void
 */
function sendfromfile_process($sendfromfile_id) {
	if (!($sendfromfile_id = trim($sendfromfile_id))) {
		
		return;
	}

	@set_time_limit(0);
	
	$data = array();

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='" . $sendfromfile_id . "'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_sms_to = $db_row['sms_to'];
		$c_sms_msg = $db_row['sms_msg'];
		$c_username = $db_row['sms_username'];
		$c_hash = md5($c_username . $c_sms_msg);
		if ($c_sms_to && $c_username && $c_sms_msg) {
			$data[$c_hash]['sms_to'][] = $c_sms_to;
			$data[$c_hash]['message'] = $c_sms_msg;
			$data[$c_hash]['username'] = $c_username;
		}
	}

	foreach ($data as $hash => $item) {
		$sms_to = $item['sms_to'];
		$message = $item['message'];
		$username = $item['username'];
		_log('hash:' . $hash . ' u:' . $username . ' m:[' . $message . '] to_count:' . count($sms_to), 3, 'sendfromfile_process');
		if ($username && $message && count($sms_to)) {
			$type = 'text';
			$unicode = core_detect_unicode($message);
			$message = addslashes($message);
			list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $sms_to, $message, $type, $unicode);
		}
	}
	
	sendfromfile_destroy($sendfromfile_id);
}

/**
 * Destroy send from file session
 *
 * @param  string $sendfromfile_id Send from file session ID
 * @return void
 */
function sendfromfile_destroy($sendfromfile_id) {
	if (!($sendfromfile_id = trim($sendfromfile_id))) {

    	return;
	}

	$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='" . $sendfromfile_id . "'";
	dba_query($db_query);
}
