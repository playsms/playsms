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
 * This function hooks sendsms() and called by daemon sendsmsd
 * 
 * @param string $smsc Selected SMSC
 * @param string $sms_sender SMS sender ID
 * @param string $sms_footer SMS message footer
 * @param string $sms_to Mobile phone number
 * @param string $sms_msg SMS message
 * @param int $uid User ID
 * @param int $gpid Group phonebook ID
 * @param int $smslog_id SMS Log ID
 * @param string $sms_type Type of SMS
 * @param int $unicode Indicate that the SMS message is in unicode
 * @return bool true if delivery successful
 */
function smstools_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = 0, $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	// override $plugin_config by $plugin_config from selected SMSC
	$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	// re-filter, sanitize, modify some vars if needed
	$sms_sender = core_sanitize_sender($sms_sender);
	$sms_to = core_sanitize_mobile($sms_to);
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_msg = stripslashes($sms_msg . $sms_footer);

	_log('enter smsc:' . $smsc . ' smslog_id:' . $smslog_id . ' uid:' . $uid . ' to:' . $sms_to, 3, 'smstools_hook_sendsms');

	$the_msg = 'From: ' . $sms_sender . "\n";
	$the_msg .= 'To: ' . $sms_to . "\n";
	$the_msg .= "Report: yes\n";
	if ($sms_type == 'flash') {
		$the_msg .= "Flash: yes\n";
	}

	if ($unicode) {
		if (function_exists('mb_convert_encoding')) {
			$sms_msg = mb_convert_encoding($sms_msg, 'UCS-2BE', 'auto');
			$the_msg .= "Alphabet: UCS\n";
		}
		// $sms_msg = str2hex($sms_msg);
	}

	// final message file content
	$the_msg .= "\n" . $sms_msg;

	// outfile
	$gpid = (int) ($gpid ?: 0);
	$uid = (int) ($uid ?: 0);
	$smslog_id = (int) ($smslog_id ?: 0);
	//$d = sendsms_get_sms($smslog_id);
	//$sms_datetime = core_sanitize_numeric($d['p_datetime']);
	$sms_datetime = core_sanitize_numeric(core_get_datetime());
	$sms_id = $sms_datetime . '.' . $gpid . '.' . $uid . '.' . $smslog_id;
	$outfile = 'out.' . $sms_id;

	$fn = $c_plugin_config['smstools']['queue'] . '/' . $outfile;
	if ($fd = @fopen($fn, 'w+')) {
		@fputs($fd, $the_msg);
		@fclose($fd);
		_log('saving outfile:' . $fn . ' smsc:[' . $smsc . ']', 3, 'smstools_hook_sendsms');
	}

	$ok = false;
	if (file_exists($fn)) {
		$ok = true;
		$p_status = 0;
		_log('saved outfile:' . $fn . ' smsc:[' . $smsc . ']', 2, 'smstools_hook_sendsms');
	} else {
		$p_status = 2;
		_log('fail to save outfile:' . $fn . ' smsc:[' . $smsc . ']', 2, 'smstools_hook_sendsms');
	}
	dlr($smslog_id, $uid, $p_status);

	return $ok;
}

/**
 * This function hooks getsmsstatus() and called by daemon dlrssmsd()
 * 
 * There are 2 ways getting DLRs from SMS provider or SMS gateway software
 *   1. Hooks getsmsstatus() - playSMS periodically fetchs DLRs
 *   2. Use callback URL - playSMS waits for callback call (via HTTP)
 * 
 * @param int $gpid Group phonebook ID
 * @param int $uid User ID
 * @param int $smslog_id SMS Log ID
 * @param string $p_datetime SMS delivery datetime
 * @param string $p_update SMS last update datetime
 * @return void
 */
function smstools_hook_getsmsstatus($gpid = 0, $uid = 0, $smslog_id = 0, $p_datetime = '', $p_update = '')
{
	global $plugin_config;

	$smscs = gateway_getall_smsc_names($plugin_config['smstools']['name']);
	foreach ( $smscs as $smsc ) {
		$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

		// smsd writes sent/failed files to the main spool (not the per-SMSC queue), as 1.4.3 expected
		$c_plugin_config['smstools']['backup'] = $plugin_config['smstools']['queue'] . '/backup';
		if (!is_dir($c_plugin_config['smstools']['backup'] . '/sent')) {
			mkdir($c_plugin_config['smstools']['backup'] . '/sent', 0777, true);
		}
		if (!is_dir($c_plugin_config['smstools']['backup'] . '/failed')) {
			mkdir($c_plugin_config['smstools']['backup'] . '/failed', 0777, true);
		}

		// outfile
		$gpid = ((int) $gpid ? (int) $gpid : 0);
		$uid = ((int) $uid ? (int) $uid : 0);
		$smslog_id = ((int) $smslog_id ? (int) $smslog_id : 0);
		$sms_datetime = core_sanitize_numeric($p_datetime);
		$sms_id = $sms_datetime . '.' . $gpid . '.' . $uid . '.' . $smslog_id;
		$outfile = 'out.' . $sms_id;

		$fn = $plugin_config['smstools']['queue'] . '/sent/' . $outfile;
		$efn = $plugin_config['smstools']['queue'] . '/failed/' . $outfile;

		// smstools_hook_sendsms() names the file with core_get_datetime() at send time, which can be a second
		// later than p_datetime; if the exact name is missing, find the file by its unique gpid.uid.smslog_id suffix
		if ($smslog_id && !file_exists($fn) && !file_exists($efn)) {
			$suffix = '.' . $gpid . '.' . $uid . '.' . $smslog_id;
			foreach (['sent' => 'fn', 'failed' => 'efn'] as $dir => $var) {
				$found = glob($plugin_config['smstools']['queue'] . '/' . $dir . '/out.*' . $suffix);
				if ($found) {
					$$var = $found[0];
					$outfile = basename($found[0]);
					break;
				}
			}
		}

		// set if its sent, SMS Log ID must exists
		if ($smslog_id && file_exists($fn)) {
			$message_id = 0;

			$lines = @file($fn);
			if (isset($lines) && is_array($lines)) {
				for ($c = 0; $c < count($lines); $c++) {
					$c_line = $lines[$c];
					if (preg_match('/^Message_id: /', $c_line)) {
						$message_id = trim(str_replace('Message_id: ', '', trim($c_line)));
						if ($message_id) {
							break;
						}
					}
				}
			}

			if ($message_id) {

				if (!rename($fn, $c_plugin_config['smstools']['backup'] . '/sent/' . $outfile)) {
					if (file_exists($fn)) {
						@unlink($fn);
					}
				}

				if (!file_exists($fn)) {
					$db_query = "
							INSERT INTO " . _DB_PREF_ . "_gatewaySmstools_dlr 
							(c_timestamp,uid,smslog_id,message_id,status) 
							VALUES 
							('" . time() . "','" . $uid . "','" . $smslog_id . "','" . $message_id . "','1')";
					$dlr_id = dba_insert_id($db_query);
					if ($dlr_id) {
						_log('DLR mapped fn:' . $fn . ' id:' . $dlr_id . ' uid:' . $uid . ' smslog_id:' . $smslog_id . ' message_id:' . $message_id, 2, 'smstools_hook_getsmsstatus');
					} else {
						_log('Fail to map DLR fn:' . $fn . ' id:' . $dlr_id . ' uid:' . $uid . ' smslog_id:' . $smslog_id . ' message_id:' . $message_id, 2, 'smstools_hook_getsmsstatus');
					}

					$p_status = 1;
					dlr($smslog_id, $uid, $p_status);
				}
			}
		}

		// set if its failed, SMS Log ID must exists
		if ($smslog_id && file_exists($efn)) {
			if (!rename($efn, $c_plugin_config['smstools']['backup'] . '/failed/' . $outfile)) {
				if (file_exists($efn)) {
					@unlink($efn);
				}
			}

			$p_status = 2;
			dlr($smslog_id, $uid, $p_status);
		}

		// set failed if its at least 2 days old
		$p_datetime_stamp = strtotime($p_datetime);
		$p_update_stamp = strtotime($p_update);
		$p_delay = floor(($p_update_stamp - $p_datetime_stamp) / 86400);
		if ($smslog_id && ($p_delay >= 2)) {
			$p_status = 2;
			dlr($smslog_id, $uid, $p_status);
		}
	}
}

/**
 * This function hooks getsmsinbox() and called by daemon recvsmsd()
 * 
 * There are 2 ways getting incoming SMS from SMS provider or SMS gateway software
 *   1. Hooks getsmsinbox() - playSMS periodically fetchs incoming SMS
 *   2. Use callback URL - playSMS waits for callback call (via HTTP)
 * 
 * @return void
 */
function smstools_hook_getsmsinbox()
{
	global $plugin_config;

	$smscs = gateway_getall_smsc_names($plugin_config['smstools']['name']);
	foreach ( $smscs as $smsc ) {
		$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

		$c_plugin_config['smstools']['backup'] = $c_plugin_config['smstools']['queue'] . '/backup';
		if (!is_dir($c_plugin_config['smstools']['backup'] . '/incoming')) {
			mkdir($c_plugin_config['smstools']['backup'] . '/incoming', 0777, true);
		}

		$handle = @opendir($c_plugin_config['smstools']['queue'] . '/incoming');
		while ($sms_in_file = @readdir($handle)) {
			$smsc = '';
			$sms_receiver = '';
			$sms_sender = '';
			$sms_datetime = '';
			$found_sender = FALSE;
			$found_datetime = FALSE;

			$fn = $c_plugin_config['smstools']['queue'] . '/incoming/' . $sms_in_file;
			$fn_backup = $c_plugin_config['smstools']['backup'] . '/incoming/' . $sms_in_file;

			$lines = @file($fn);
			$start = 0;
			if (isset($lines) && is_array($lines)) {
				for ($c = 0; $c < count($lines); $c++) {
					$c_line = $lines[$c];
					if (preg_match('/^From: /', $c_line)) {
						$sms_sender = '+' . trim(str_replace('From: ', '', trim($c_line)));
						$found_sender = true;
					} else if (preg_match('/^Received: /', $c_line)) {
						$sms_datetime = '20' . trim(str_replace('Received: ', '', trim($c_line)));
						$found_datetime = true;
					} else if (preg_match('/^Modem: /', $c_line)) {
						if ($smsc = trim(str_replace('Modem: ', '', trim($c_line)))) {
							$c_plugin_config = gateway_apply_smsc_config($smsc, $c_plugin_config);
							$sms_receiver = $c_plugin_config['smstools']['sms_receiver'];
						}
					} else if ($c_line == "\n") {
						$start = $c + 1;
						break;
					}
				}
			}

			// proceed only when the file contains some hint that it is an incoming SMS
			if ($found_sender && $found_datetime && $start) {

				// inspired by keke's suggestion (smstools3 dev).
				// copy to backup folder instead of delete it directly from original spool dir.
				// playSMS does the backup since probably not many smstools3 users configure
				// an eventhandler to backup incoming sms
				if (!rename($fn, $c_plugin_config['smstools']['backup'] . '/incoming/' . $sms_in_file)) {
					if (file_exists($fn)) {
						@unlink($fn);
					}
				}

				// continue process only when incoming sms file can be deleted
				if (!file_exists($fn) && $start) {
					if ($sms_sender && $sms_datetime) {
						$message = '';
						for ($lc = $start; $lc < count($lines); $lc++) {
							$message .= trim($lines[$lc]) . "\n";
						}
						if (strlen($message) > 0) {
							$message = substr($message, 0, -1);
						}

						$is_dlr = false;
						$msg = explode("\n", $message);
						if (trim($msg[0]) == 'SMS STATUS REPORT') {
							$label = explode(':', $msg[1]);
							if (trim($label[0]) == 'Message_id') {
								$message_id = trim($label[1]);
							}
							unset($label);
							$label = explode(':', $msg[3]);
							if (trim($label[0]) == 'Status') {
								$status_var = explode(',', trim($label[1]));
								$status = (int) $status_var[0];
							}
							if ($message_id && $status_var[1]) {
								_log('DLR received message_id:' . $message_id . ' status:' . $status . ' info1:[' . $status_var[1] . '] info2:[' . $status_var[2] . '] smsc:[' . $smsc . ']', 2, 'smstools_hook_getsmsinbox');
								$db_query = "SELECT id,uid,smslog_id FROM " . _DB_PREF_ . "_gatewaySmstools_dlr WHERE message_id='" . $message_id . "' AND status='1' ORDER BY id DESC LIMIT 1";
								$db_result = dba_query($db_query);
								$db_row = dba_fetch_array($db_result);
								$id = $db_row['id'];
								$uid = $db_row['uid'];
								$smslog_id = $db_row['smslog_id'];
								if ($uid && $smslog_id && $status === 0) {
									$db_query = "UPDATE " . _DB_PREF_ . "_gatewaySmstools_dlr SET status='2' WHERE id='" . $id . "'";
									if ($db_result = dba_affected_rows($db_query)) {
										$p_status = 3;
										dlr($smslog_id, $uid, $p_status);
										_log('DLR smslog_id:' . $smslog_id . ' p_status:' . $p_status . ' smsc:[' . $smsc . ']', 2, 'smstools_hook_getsmsinbox');
									}
								}
								$is_dlr = true;
							}
						}

						// collected: $sms_datetime, $sms_sender, $message, $sms_receiver
						// if not a DLR then route it to incoming handler
						if (!$is_dlr) {
							_log('sender:' . $sms_sender . ' receiver:' . $sms_receiver . ' dt:' . $sms_datetime . ' msg:[' . $message . '] smsc:[' . $smsc . ']', 3, 'smstools_hook_getsmsinbox');
							$sms_sender = addslashes($sms_sender);
							$message = addslashes($message);
							recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
						}
					}
				}
			}
		}
	}
}
