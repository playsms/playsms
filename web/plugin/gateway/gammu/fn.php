<?php
defined('_SECURE_') or die('Forbidden');

function gammu_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	global $plugin_config;
	
	// p_status :
	// 0 = pending
	// 1 = sent/delivered
	// 2 = failed
	// OUT<priority><date>_<time>_<serialno>_<phone_number>_<anything>.<ext><options>
	// $fn = 'A'.$date.'_'.$time.'_00_'.$sms_to.'_'.$smslog_id.'10001'.$uid.'10001'.$gpid.'.txtd';
	$sms_id = $smslog_id . '10001' . $uid . '10001' . $gpid;
	
	// sent dir
	$dir[0] = $plugin_config['gammu']['path'] . '/sent/';
	
	// error dir
	$dir[1] = $plugin_config['gammu']['path'] . '/error/';
	
	// list all files in sent and error dir
	$fn = array();
	for($i = 0; $i < count($dir); $i++) {
		$j = 0;
		if ($handle = @opendir($dir[$i])) {
			while ($file = @readdir($handle)) {
				if ($file != "." && $file != "..") {
					$fn[$i][$j] = $file;
					$j++;
				}
			}
			@closedir($handle);
		}
	}
	
	// check listed files above againts sms_id
	$the_fn = '';
	for($i = 0; $i < count($dir); $i++) {
		for($j = 0; $j < count($fn[$i]); $j++) {
			if (preg_match("/" . $sms_id . "/", $fn[$i][$j])) {
				$the_fn = $dir[$i] . $fn[$i][$j];
				if ($i === 0) {
					
					// sms sent
					$p_status = 1;
					dlr($smslog_id, $uid, $p_status);
				} else if ($i == 1) {
					
					// failed to sent sms
					$p_status = 2;
					dlr($smslog_id, $uid, $p_status);
				}
				break;
			}
		}
	}
	
	// if file not found
	if (!file_exists($the_fn)) {
		$p_datetime_stamp = strtotime($p_datetime);
		$p_update_stamp = strtotime($p_update);
		$p_delay = floor(($p_update_stamp - $p_datetime_stamp) / 86400);
		
		// set failed if its at least 2 days old
		if ($p_delay >= 2) {
			$p_status = 2;
			dlr($smslog_id, $uid, $p_status);
		}
	} else {
		
		// delete the file if exists
		logger_print("smslog_id:" . $smslog_id . " unlink the_fn:" . $the_fn . " p_status:" . $p_status, 2, "gammu getsmsstatus");
		@unlink($the_fn);
	}
	return;
}

function gammu_hook_getsmsinbox() {
	// filename
	// IN20101017_091747_00_+628123423141312345_00.txt
	global $plugin_config;
	$handle = @opendir($plugin_config['gammu']['path'] . "/inbox");
	$messages = array();
	$files = array();
	while ($sms_in_file = @readdir($handle)) {
		if ($sms_in_file != "." && $sms_in_file != "..") {
			$files[] = $sms_in_file;
		}
	}
	sort($files);
	foreach ($files as $sms_in_file ) {
		$fn = $plugin_config['gammu']['path'] . "/inbox/$sms_in_file";
		
		$matches = array();
		preg_match('/IN(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})_(\d+)_([+]*\w+)_(\d+)/', basename($fn), $matches);
		list($s, $year, $month, $date, $hour, $minute, $second, $serial, $sms_sender, $seq) = $matches;
		$sms_datetime = $year . "-" . $month . "-" . $date . " " . $hour . ":" . $minute . ":" . $second;
		
		// message is in UTF-16, need to convert it to UTF-8
		$message = file_get_contents($fn);
		
		// if the message is unicode then convert it to UTF-8
		if (core_detect_unicode($message)) {
			$message = mb_convert_encoding($message, "UTF-8", "UTF-16");
		}
		
		@unlink($fn);
		
		// continue process only when incoming sms file can be deleted
		if (!file_exists($fn)) {
			if ($sms_sender && $sms_datetime) {
				// adding message parts to existing array
				if (array_key_exists($sms_sender, $messages) && (int) $seq > 0) {
					$messages[$sms_sender][] = array(
						"fn" => $fn,
						"message" => $message,
						"msg_datetime" => $sms_datetime 
					);
				} else if (!array_key_exists($sms_sender, $messages) || (array_key_exists($sms_sender, $messages) && (int) $seq == 0)) {
					if (count($messages) > 0) {
						// saving concatenated message parts
						$parts_sender = 0;
						foreach ($messages as $sender => $message_parts ) {
							$parts_message = "";
							$parts_sender = $sender;
							foreach ($message_parts as $part ) {
								$parts_message .= $part['message'];
							}
						}
						$parts_datetime = $messages[$parts_sender][0]['msg_datetime'];
						recvsms($parts_datetime, $parts_sender, $parts_message, $sms_receiver, 'gammu');
						logger_print("sender:" . $parts_sender . " receiver:" . $sms_receiver . " dt:" . $parts_datetime . " msg:" . $parts_message, 3, "gammu incoming");
						
						unset($messages);
					}
					// new message parts array
					$messages[$sms_sender] = array(
						array(
							"fn" => $fn,
							"message" => $message,
							"msg_datetime" => $sms_datetime 
						) 
					);
				}
			}
		}
	}
	if (count($messages) > 0) {
		// saving last concatenated message parts
		$parts_sender = 0;
		foreach ($messages as $sender => $message_parts ) {
			$parts_message = "";
			$parts_sender = $sender;
			foreach ($message_parts as $part ) {
				$parts_message .= $part['message'];
			}
		}
		$parts_datetime = $messages[$parts_sender][0]['msg_datetime'];
		recvsms($parts_datetime, $parts_sender, $parts_message, $sms_receiver, $smsc);
		logger_print("sender:" . $parts_sender . " receiver:" . $sms_receiver . " dt:" . $parts_datetime . " msg:" . $_parts_message, 3, "gammu incoming");
		unset($messages);
	}
	@closedir($handle);
}

function gammu_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "gammu_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$date = date('Ymd', time());
	$time = date('Gis', time());
	
	if ($plugin_config['gammu']['dlr']) {
		$option_dlr = 'd';
	} else {
		$option_dlr = '';
	}
	
	// OUT<priority><date>_<time>_<serialno>_<phone_number>_<anything>.<ext><options>
	$sms_id = 'A' . $date . '_' . $time . '_00_' . $sms_to . '_' . $smslog_id . '10001' . $uid . '10001' . $gpid . '.txt' . $option_dlr;
	
	if ($sms_type == 'flash') {
		$sms_id .= 'f';
	}
	
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	// no need to do anything on unicoded messages since InboxFormat and OutboxFormat is already set to unicode
	// meaning gammu will take care of it
	/*
	 * if ($unicode) { if (function_exists('mb_convert_encoding')) { $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto"); } }
	 */
	$fn = $plugin_config['gammu']['path'] . "/outbox/OUT" . $sms_id;
	logger_print("saving outfile:" . $fn, 2, "gammu outgoing");
	umask(0);
	$fd = @fopen($fn, "w+");
	@fputs($fd, $sms_msg);
	@fclose($fd);
	$ok = false;
	if (file_exists($fn)) {
		$ok = true;
		$p_status = 0;
		logger_print("saved outfile:" . $fn, 2, "gammu outgoing");
	} else {
		$p_status = 2;
		logger_print("fail to save outfile:" . $fn, 2, "gammu outgoing");
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}
