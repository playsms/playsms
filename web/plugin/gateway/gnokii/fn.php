<?php
defined('_SECURE_') or die('Forbidden');

function gnokii_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
	global $gnokii_param;
	// p_status :
	// 0 = pending
	// 1 = delivered
	// 2 = failed
	if ($gpid) {
		$fn = $gnokii_param['path']."/out.$gpid.$uid.$smslog_id";
		$efn = $gnokii_param['path']."/ERR.out.$gpid.$uid.$smslog_id";
	} else {
		$fn = $gnokii_param['path']."/out.0.$uid.$smslog_id";
		$efn = $gnokii_param['path']."/ERR.out.0.$uid.$smslog_id";
	}
	// set delivered first
	$p_status = 1;
	setsmsdeliverystatus($smslog_id,$uid,$p_status);
	// and then check if its not delivered
	if (file_exists($fn)) {
		$p_datetime_stamp = strtotime($p_datetime);
		$p_update_stamp = strtotime($p_update);
		$p_delay = floor(($p_update_stamp - $p_datetime_stamp)/86400);
		// set pending if its under 2 days
		if ($p_delay <= 2) {
			$p_status = 0;
			setsmsdeliverystatus($smslog_id,$uid,$p_status);
		} else {
			$p_status = 2;
			setsmsdeliverystatus($smslog_id,$uid,$p_status);
			@unlink ($fn);
			@unlink ($efn);
		}
		return;
	}
	// set if its failed
	if (file_exists($efn)) {
		$p_status = 2;
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
		@unlink ($fn);
		@unlink ($efn);
		return;
	}
	return;
}

function gnokii_hook_getsmsinbox() {
	global $gnokii_param;
	$handle = @opendir($gnokii_param['path']);
	while ($sms_in_file = @readdir($handle)) {
		if (preg_match("/^ERR.in/i",$sms_in_file) && !preg_match("/^[.]/i",$sms_in_file)) {
			$fn = $gnokii_param['path']."/$sms_in_file";
			// logger_print("infile:".$fn, 2, "gnokii incoming");
			$tobe_deleted = $fn;
			$lines = @file ($fn);
			$sms_datetime = trim($lines[0]);
			$sms_sender = trim($lines[1]);
			$message = "";
			for ($lc=2;$lc<count($lines);$lc++) {
				$message .= trim($lines[$lc]);
			}
			@unlink($tobe_deleted);
			// continue process only when incoming sms file can be deleted
			if (! file_exists($tobe_deleted)) {
				// collected:
				// $sms_datetime, $sms_sender, $message, $sms_receiver
				$sms_sender = addslashes($sms_sender);
				$message = addslashes($message);
				setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver);
				logger_print("sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".$message, 3, "gnokii incoming");
			}
		}
	}
}

function gnokii_hook_sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	global $gnokii_param;
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$sms_id = "$gpid.$uid.$smslog_id";
	if (empty($sms_id)) {
		$sms_id = mktime();
	}
	if ($sms_footer) {
		$sms_msg = $sms_msg.$sms_footer;
	}
	$sms_msg = str_replace("\n", " ", $sms_msg);
	$sms_msg = str_replace("\r", " ", $sms_msg);
	$the_msg = "$sms_to\n$sms_msg";
	$fn = $gnokii_param['path']."/out.$sms_id";
	logger_print("saving outfile:".$fn, 2, "gnokii outgoing");
	umask(0);
	$fd = @fopen($fn, "w+");
	@fputs($fd, $the_msg);
	@fclose($fd);
	$ok = false;
	if (file_exists($fn)) {
		$ok = true;
		$p_status = 0;
		logger_print("saved outfile:".$fn, 2, "gnokii outgoing");
	} else {
		$p_status = 2;
		logger_print("fail to save outfile:".$fn, 2, "gnokii outgoing");
	}
	setsmsdeliverystatus($smslog_id,$uid,$p_status);
	return $ok;
}

?>