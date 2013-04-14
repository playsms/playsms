<?php
defined('_SECURE_') or die('Forbidden');

function gammu_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
	global $gammu_param;
	// p_status :
	// 0 = pending
	// 1 = sent/delivered
	// 2 = failed
	// OUT<priority><date>_<time>_<serialno>_<phone_number>_<anything>.<ext><options>
	// $fn = 'A'.$date.'_'.$time.'_00_'.$sms_to.'_'.$smslog_id.'10001'.$uid.'10001'.$gpid.'.txtd';
	$sms_id = $smslog_id.'10001'.$uid.'10001'.$gpid;

	// sent dir
	$dir[0] = $gammu_param['path'].'/sent/';
	// error dir
	$dir[1] = $gammu_param['path'].'/error/';

	// list all files in sent and error dir
	$fn = array();
	for ($i=0;$i<count($dir);$i++) {
		$j=0;
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
	for ($i=0;$i<count($dir);$i++) {
		for ($j=0;$j<count($fn[$i]);$j++) {
			if (preg_match("/".$sms_id."/", $fn[$i][$j])) {
				$the_fn = $dir[$i].$fn[$i][$j];
				if ($i==0) {
					// sms sent
					$p_status = 1;
					setsmsdeliverystatus($smslog_id,$uid,$p_status);
				} else if ($i==1) {
					// failed to sent sms
					$p_status = 2;
					setsmsdeliverystatus($smslog_id,$uid,$p_status);
				}
				break;
			}
		}
	}

	// if file not found
	if (! file_exists($the_fn)) {
		$p_datetime_stamp = strtotime($p_datetime);
		$p_update_stamp = strtotime($p_update);
		$p_delay = floor(($p_update_stamp - $p_datetime_stamp)/86400);
		// set failed if its at least 2 days old
		if ($p_delay >= 2) {
			$p_status = 2;
			setsmsdeliverystatus($smslog_id,$uid,$p_status);
		}
	} else {
		// delete the file if exists
		logger_print("smslog_id:".$smslog_id." unlink the_fn:".$the_fn." p_status:".$p_status, 2, "gammu getsmsstatus");
		@unlink ($the_fn);
	}
	return;
}

function gammu_hook_getsmsinbox() {
	// IN20101017_091747_00_+628123423141312345_00.txt
	global $gammu_param;
	$handle = @opendir($gammu_param['path']."/inbox");
	while ($sms_in_file = @readdir($handle)) {
		if ($sms_in_file != "." && $sms_in_file != "..") {
			$fn = $gammu_param['path']."/inbox/$sms_in_file";
			// logger_print("infile:".$fn, 2, "gammu incoming");
			$the_fn = str_replace('IN','',basename($fn));
			$arr_fn = explode('_', $the_fn);
			// let me know if you got better way :)
			$year = substr($arr_fn[0],0,4);
			$month = substr($arr_fn[0],4,2);
			$date = substr($arr_fn[0],6,2);
			$hour = substr($arr_fn[1],0,2);
			$minute = substr($arr_fn[1],2,2);
			$second = substr($arr_fn[1],4,2);
			$sms_datetime = $year."-".$month."-".$date." ".$hour.":".$minute.":".$second;
			// sender
			$sms_sender = $arr_fn[3];
			// message is in UTF-16, need to convert it to UTF-8
			$message = file_get_contents($fn);
			$message = mb_convert_encoding($message, "UTF-8", "UTF-16");
			@unlink($fn);
			// continue process only when incoming sms file can be deleted
			if (! file_exists($fn)) {
				if ($sms_sender && $sms_datetime) {
					// collected:
					// $sms_datetime, $sms_sender, $message, $sms_receiver
					$sms_sender = addslashes($sms_sender);
					$message = addslashes($message);
					setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver);
				}
				logger_print("sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".$message, 3, "gammu incoming");
			}
		}
	}
	@closedir($handle);
}

function gammu_hook_sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	global $gammu_param;
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$date = date('Ymd', time());
	$time = date('Gis', time());
	// OUT<priority><date>_<time>_<serialno>_<phone_number>_<anything>.<ext><options>
	$sms_id = 'A'.$date.'_'.$time.'_00_'.$sms_to.'_'.$smslog_id.'10001'.$uid.'10001'.$gpid.'.txtd';
	if ($sms_type=='flash') {
		$sms_id .= 'f';
	}
	if ($sms_footer) {
		$sms_msg = $sms_msg.$sms_footer;
	}
	// no need to do anything on unicoded messages since InboxFormat and OutboxFormat is already set to unicode
	// meaning gammu will take care of it
	/*
	if ($unicode) {
	if (function_exists('mb_convert_encoding')) {
	$sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
	}
	}
	*/
	$fn = $gammu_param['path']."/outbox/OUT".$sms_id;
	logger_print("saving outfile:".$fn, 2, "gammu outgoing");
	umask(0);
	$fd = @fopen($fn, "w+");
	@fputs($fd, $sms_msg);
	@fclose($fd);
	$ok = false;
	if (file_exists($fn)) {
		$ok = true;
		$p_status = 0;
		logger_print("saved outfile:".$fn, 2, "gammu outgoing");
	} else {
		$p_status = 2;
		logger_print("fail to save outfile:".$fn, 2, "gammu outgoing");
	}
	setsmsdeliverystatus($smslog_id,$uid,$p_status);
	return $ok;
}

?>