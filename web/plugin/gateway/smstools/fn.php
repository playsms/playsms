<?php
defined('_SECURE_') or die('Forbidden');

function smstools_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	global $plugin_config;
	
	// p_status :
	// 0 = pending
	// 1 = sent/delivered
	// 2 = failed
	if ($gpid) {
		$fn = $plugin_config['smstools']['spool_dir'] . "/sent/out.$gpid.$uid.$smslog_id";
		$efn = $plugin_config['smstools']['spool_dir'] . "/failed/out.$gpid.$uid.$smslog_id";
	} else {
		$fn = $plugin_config['smstools']['spool_dir'] . "/sent/out.0.$uid.$smslog_id";
		$efn = $plugin_config['smstools']['spool_dir'] . "/failed/out.0.$uid.$smslog_id";
	}
	// set if its sent/delivered
	if (file_exists($fn)) {
		
		$lines = @file($fn);
		for($c = 0; $c < count($lines); $c++) {
			$c_line = $lines[$c];
			if (preg_match('/^Message_id: /', $c_line)) {
				$message_id = trim(str_replace('Message_id: ', '', trim($c_line)));
				if ($message_id) {
					break;
				}
			}
		}
		if ($smslog_id && $message_id) {
			$db_query = "INSERT INTO " . _DB_PREF_ . "_gatewaySmstools_dlr (c_timestamp,uid,smslog_id,message_id,status) VALUES ('" . mktime() . "','$uid','$smslog_id','$message_id','-1')";
			$dlr_id = dba_insert_id($db_query);
			if ($dlr_id) {
				logger_print("DLR mapped id:" . $dlr_id . " uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $message_id, 2, "smstools getsmsstatus");
			} else {
				logger_print("Fail to map DLR id:" . $dlr_id . " uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $message_id, 2, "smstools getsmsstatus");
			}
		} else {
			logger_print("No valid DLR id:" . $dlr_id . " uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $message_id, 2, "smstools getsmsstatus");
		}
		
		$p_status = 1;
		dlr($smslog_id, $uid, $p_status);
		if (is_dir($plugin_config['smstools']['spool_bak'] . '/sent')) {
			@shell_exec('mv ' . $fn . ' ' . $plugin_config['smstools']['spool_bak'] . '/sent/');
		}
		if (file_exists($fn)) {
			@unlink($fn);
		}
	}
	// set if its failed
	if (file_exists($efn)) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
		if (is_dir($plugin_config['smstools']['spool_bak'] . '/failed')) {
			@shell_exec('mv ' . $efn . ' ' . $plugin_config['smstools']['spool_bak'] . '/failed/');
		}
		if (file_exists($efn)) {
			@unlink($efn);
		}
	}
	// set failed if its at least 2 days old
	$p_datetime_stamp = strtotime($p_datetime);
	$p_update_stamp = strtotime($p_update);
	$p_delay = floor(($p_update_stamp - $p_datetime_stamp) / 86400);
	if ($p_delay >= 2) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}
	return;
}

function smstools_hook_getsmsinbox() {
	global $plugin_config;
	$handle = @opendir($plugin_config['smstools']['spool_dir'] . "/incoming");
	while ($sms_in_file = @readdir($handle)) {
		$fn = $plugin_config['smstools']['spool_dir'] . "/incoming/$sms_in_file";
		$fn_bak = $plugin_config['smstools']['spool_bak'] . "/incoming/$sms_in_file";
		
		$lines = @file($fn);
		$start = 0;
		for($c = 0; $c < count($lines); $c++) {
			$c_line = $lines[$c];
			if (preg_match('/^From: /', $c_line)) {
				$sms_sender = '+' . trim(str_replace('From: ', '', trim($c_line)));
			} else if (preg_match('/^Received: /', $c_line)) {
				$sms_datetime = '20' . trim(str_replace('Received: ', '', trim($c_line)));
			} else if ($c_line == "\n") {
				$start = $c + 1;
				break;
			}
		}
		
		// inspired by keke's suggestion (smstools3 dev).
		// copy to backup folder instead of delete it directly from original spool dir.
		// playSMS does the backup since probably not many smstools3 users configure
		// an eventhandler to backup incoming sms
		if (is_dir($plugin_config['smstools']['spool_bak'] . '/incoming') && $start) {
			logger_print("infile backup:" . $fn_bak, 2, "smstools incoming");
			@shell_exec('mv ' . $fn . ' ' . $plugin_config['smstools']['spool_bak'] . '/incoming/');
		} else {
			@unlink($fn);
		}
		
		// continue process only when incoming sms file can be deleted
		if (!file_exists($fn) && $start) {
			if ($sms_sender && $sms_datetime) {
				$message = "";
				for($lc = $start; $lc < count($lines); $lc++) {
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
						$status = $status_var[0];
					}
					if ($message_id && $status_var[1]) {
						logger_print("DLR received message_id:" . $message_id . " status:" . $status . " info1:" . $status_var[1] . " info2:" . $status_var[2], 2, "smstools incoming");
						$db_query = "SELECT uid,smslog_id FROM " . _DB_PREF_ . "_gatewaySmstools_dlr WHERE message_id='$message_id'";
						$db_result = dba_query($db_query);
						$db_row = dba_fetch_array($db_result);
						$uid = $db_row['uid'];
						$smslog_id = $db_row['smslog_id'];
						if ($uid && $smslog_id && $status == 0) {
							$p_status = 3;
							dlr($smslog_id, $uid, $p_status);
							logger_print("DLR smslog_id:" . $smslog_id . " p_status:" . $p_status, 2, "smstools incoming");
						}
						$is_dlr = true;
					}
				}
				
				// collected: $sms_datetime, $sms_sender, $message, $sms_receiver
				// if not a DLR then route it to incoming handler
				if (!$is_dlr) {
					logger_print("sender:" . $sms_sender . " receiver:" . $sms_receiver . " dt:" . $sms_datetime . " msg:" . $message, 3, "smstools incoming");
					$sms_sender = addslashes($sms_sender);
					$message = addslashes($message);
					recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
				}
			}
		}
	}
}

function smstools_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "smstools_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$sms_id = "$gpid.$uid.$smslog_id";
	if (empty($sms_id)) {
		$sms_id = mktime();
	}
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	$the_msg = "From: $sms_sender\n";
	$the_msg .= "To: $sms_to\n";
	$the_msg .= "Report: yes\n";
	if ($sms_type == 'flash') {
		$the_msg .= "Flash: yes\n";
	}
	if ($unicode) {
		if (function_exists('mb_convert_encoding')) {
			$the_msg .= "Alphabet: UCS\n";
			$sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
		}
		// $sms_msg = str2hex($sms_msg);
	}
	$the_msg .= "\n$sms_msg";
	
	// try to backup outgoing file first
	$fn_bak = $plugin_config['smstools']['spool_bak'] . "/outgoing/out.$sms_id";
	if (is_dir($plugin_config['smstools']['spool_bak'] . '/outgoing')) {
		umask(0);
		$fd = @fopen($fn_bak, 'w+');
		@fputs($fd, $the_msg);
		@fclose($fd);
	}
	
	// copy from backup if exists, or create new one in spool dir
	$fn = $plugin_config['smstools']['spool_dir'] . "/outgoing/out.$sms_id";
	if (file_exists($fn_bak)) {
		logger_print("outfile backup:" . $fn_bak, 2, "smstools outgoing");
		@shell_exec('cp ' . $fn_bak . ' ' . $fn);
	} else {
		umask(0);
		$fd = @fopen($fn, 'w+');
		@fputs($fd, $the_msg);
		@fclose($fd);
	}
	
	logger_print("saving outfile:" . $fn, 2, "smstools outgoing");
	$ok = false;
	if (file_exists($fn)) {
		$ok = true;
		$p_status = 0;
		logger_print("saved outfile:" . $fn, 2, "smstools outgoing");
	} else {
		$p_status = 2;
		logger_print("fail to save outfile:" . $fn, 2, "smstools outgoing");
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}

?>
