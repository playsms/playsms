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

function smstools_hook_getsmsstatus($gpid = 0, $uid = '', $smslog_id = '', $p_datetime = '', $p_update = '') {
	global $plugin_config;
	
	$plugin_config['smstools']['backup'] = $plugin_config['smstools']['default_queue'] . '/backup';
	if (!is_dir($plugin_config['smstools']['backup'] . '/sent')) {
		mkdir($plugin_config['smstools']['backup'] . '/sent', 0777, TRUE);
	}
	if (!is_dir($plugin_config['smstools']['backup'] . '/failed')) {
		mkdir($plugin_config['smstools']['backup'] . '/failed', 0777, TRUE);
	}
	
	// outfile
	$gpid = ((int) $gpid ? (int) $gpid : 0);
	$uid = ((int) $uid ? (int) $uid : 0);
	$smslog_id = ((int) $smslog_id ? (int) $smslog_id : 0);
	$sms_datetime = core_sanitize_numeric($p_datetime);
	$sms_id = $sms_datetime . '.' . $gpid . '.' . $uid . '.' . $smslog_id;
	$outfile = 'out.' . $sms_id;
	
	$fn = $plugin_config['smstools']['default_queue'] . '/sent/' . $outfile;
	$efn = $plugin_config['smstools']['default_queue'] . '/failed/' . $outfile;
	
	// set if its sent
	if (file_exists($fn)) {
		$message_id = 0;
		
		$lines = @file($fn);
		for ($c = 0; $c < count($lines); $c++) {
			$c_line = $lines[$c];
			if (preg_match('/^Message_id: /', $c_line)) {
				$message_id = trim(str_replace('Message_id: ', '', trim($c_line)));
				if ($message_id) {
					break;
				}
			}
		}
		
		if ($message_id) {
			
			if (!rename($fn, $plugin_config['smstools']['backup'] . '/sent/' . $outfile)) {
				if (file_exists($fn)) {
					@unlink($fn);
				}
			}
			
			if (!file_exists($fn)) {
				if ($smslog_id && $message_id) {
					$db_query = "
						INSERT INTO " . _DB_PREF_ . "_gatewaySmstools_dlr 
						(c_timestamp,uid,smslog_id,message_id,status) 
						VALUES 
						('" . mktime() . "','" . $uid . "','" . $smslog_id . "','" . $message_id . "','1')";
					$dlr_id = dba_insert_id($db_query);
					if ($dlr_id) {
						_log('DLR mapped fn:' . $fn . ' id:' . $dlr_id . ' uid:' . $uid . ' smslog_id:' . $smslog_id . ' message_id:' . $message_id, 2, 'smstools_hook_getsmsstatus');
					} else {
						_log('Fail to map DLR fn:' . $fn . ' id:' . $dlr_id . ' uid:' . $uid . ' smslog_id:' . $smslog_id . ' message_id:' . $message_id, 2, 'smstools_hook_getsmsstatus');
					}
				} else {
					_log('No valid DLR fn:' . $fn . ' uid:' . $uid . ' smslog_id:' . $smslog_id . ' message_id:' . $message_id, 2, 'smstools_hook_getsmsstatus');
				}
				
				if ($smslog_id) {
					$p_status = 1;
					dlr($smslog_id, $uid, $p_status);
				} else {
					_log('Error no smslog_id fn:' . $fn . ' uid:' . $uid, 2, 'smstools_hook_getsmsstatus');
				}
			}
		}
	}
	
	// set if its failed
	if (file_exists($efn)) {
		if (!rename($efn, $plugin_config['smstools']['backup'] . '/failed/' . $outfile)) {
			if (file_exists($efn)) {
				@unlink($efn);
			}
		}
		
		if ($smslog_id) {
			$p_status = 2;
			dlr($smslog_id, $uid, $p_status);
		} else {
			_log('Error no smslog_id efn:' . $efn . ' uid:' . $uid, 2, 'smstools_hook_getsmsstatus');
		}
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

function smstools_hook_getsmsinbox() {
	global $plugin_config;
	
	$plugin_config['smstools']['backup'] = $plugin_config['smstools']['default_queue'] . '/backup';
	if (!is_dir($plugin_config['smstools']['backup'] . '/incoming')) {
		mkdir($plugin_config['smstools']['backup'] . '/incoming', 0777, TRUE);
	}
	
	$handle = @opendir($plugin_config['smstools']['default_queue'] . '/incoming');
	while ($sms_in_file = @readdir($handle)) {
		$smsc = '';
		$sms_receiver = '';
		$sms_sender = '';
		$sms_datetime = '';
		$found_sender = FALSE;
		$found_datetime = FALSE;
		
		$fn = $plugin_config['smstools']['default_queue'] . '/incoming/' . $sms_in_file;
		$fn_backup = $plugin_config['smstools']['backup'] . '/incoming/' . $sms_in_file;
		
		$lines = @file($fn);
		$start = 0;
		for ($c = 0; $c < count($lines); $c++) {
			$c_line = $lines[$c];
			if (preg_match('/^From: /', $c_line)) {
				$sms_sender = '+' . trim(str_replace('From: ', '', trim($c_line)));
				$found_sender = TRUE;
			} else if (preg_match('/^Received: /', $c_line)) {
				$sms_datetime = '20' . trim(str_replace('Received: ', '', trim($c_line)));
				$found_datetime = TRUE;
			} else if (preg_match('/^Modem: /', $c_line)) {
				if ($smsc = trim(str_replace('Modem: ', '', trim($c_line)))) {
					$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
					$sms_receiver = $c_plugin_config['smstools']['sms_receiver'];
				}
			} else if ($c_line == "\n") {
				$start = $c + 1;
				break;
			}
		}
		
		// proceed only when the file contains some hint that it is an incoming SMS
		if ($found_sender && $found_datetime && $start) {
			
			// inspired by keke's suggestion (smstools3 dev).
			// copy to backup folder instead of delete it directly from original spool dir.
			// playSMS does the backup since probably not many smstools3 users configure
			// an eventhandler to backup incoming sms
			if (!rename($fn, $plugin_config['smstools']['backup'] . '/incoming/' . $sms_in_file)) {
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

function smstools_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log('enter smsc:' . $smsc . ' smslog_id:' . $smslog_id . ' uid:' . $uid . ' to:' . $sms_to, 3, 'smstools_hook_sendsms');
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	$the_msg = 'From: ' . $sms_sender . "\n";
	$the_msg .= 'To: ' . $sms_to . "\n";
	$the_msg .= "Report: yes\n";
	if ($sms_type == 'flash') {
		$the_msg .= "Flash: yes\n";
	}
	if ($unicode) {
		if (function_exists('mb_convert_encoding')) {
			$the_msg .= "Alphabet: UCS\n";
			$sms_msg = mb_convert_encoding($sms_msg, 'UCS-2BE', 'auto');
		}
		// $sms_msg = str2hex($sms_msg);
	}
	
	// final message file content
	$the_msg .= "\n" . $sms_msg;
	
	// outfile
	$gpid = ((int) $gpid ? (int) $gpid : 0);
	$uid = ((int) $uid ? (int) $uid : 0);
	$smslog_id = ((int) $smslog_id ? (int) $smslog_id : 0);
	$d = sendsms_get_sms($smslog_id);
	$sms_datetime = core_sanitize_numeric($d['p_datetime']);	
	$sms_id = $sms_datetime. '.' . $gpid . '.' . $uid . '.' . $smslog_id;
	$outfile = 'out.' . $sms_id;
	
	$fn = $plugin_config['smstools']['queue'] . '/' . $outfile;
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
