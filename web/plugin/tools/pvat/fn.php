<?php
defined('_SECURE_') or die('Forbidden');

/*
 * intercept incoming sms and look for @ sign followed by username
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @param $sms_receiver
 *   receiver number that is receiving incoming SMS
 * @return
 *   array $ret
 */
function pvat_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$ret = array();

	// continue only when keyword does not exists
	$m = explode(' ', $message);
	$c_m = str_replace('#', '', $m[0]);
	if (! checkavailablekeyword($c_m)) {
		return $ret;
	}

	// scan for @<username>
	$msg = explode(' ', $message);
	if (count($msg) > 1) {
		$in['sms_datetime'] = $sms_datetime;
		$in['sms_sender'] = $sms_sender;
		$in['message'] = $message;
		$in['sms_receiver'] = $sms_receiver;
		$in['msg'] = $msg;
		$ret = pvat_handle($in);
	}

	// check for reply message, only if hasn't hooked
	if (! $ret['hooked']) {
		$c_sms_sender = str_replace('+','',$sms_sender);
		if (strlen($c_sms_sender) > 7) { $c_sms_sender = substr($c_sms_sender,3); }
		$db_query = "
			SELECT uid,p_datetime FROM "._DB_PREF_."_tblSMSOutgoing
			WHERE p_dst LIKE '%".$c_sms_sender."' AND (p_status='1' OR p_status='3')
			ORDER BY p_datetime DESC LIMIT 1";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($c_username = uid2username($db_row['uid'])) {
			$mobile = user_getfieldbyuid($db_row['uid'], 'mobile');
			$c_mobile = str_replace('+','',$mobile);
			if (strlen($c_mobile) > 7) { $c_mobile = substr($c_mobile,3); }
			if ($c_mobile != $c_sms_sender) {
				$c1 = strtotime($db_row['p_datetime']);
				$c2 = strtotime(core_display_datetime($sms_datetime));
				$p = floor(($c2 - $c1)/86400);
				if ($p <= 1) {
					logger_print("reply u:".$c_username." uid:".$db_row['uid']." dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." m:".$message, 3, "pvat");
					insertsmstoinbox($sms_datetime, $sms_sender, $c_username, $message, $sms_receiver);
					logger_print("reply end", 3, "pvat");
					$ret['hooked'] = true;
				}
			}
		}
	}

	return $ret;
}

function pvat_handle($in) {
	$ret = array();
	$in['sms_datetime'] = core_display_datetime($in['sms_datetime']);
	$x = array();
	for ($i=0;$i<count($in['msg']);$i++) {
		$c_text = trim($in['msg'][$i]);
		if (substr($c_text, 0, 1) == '@') {
			$x[] = strtolower(substr($c_text, 1));
		}
	}
	$y = array_unique($x);
	foreach ($y as $key => $c_username) {
		$c_username = str_replace(',', '', $c_username);
		$c_username = str_replace(':', '', $c_username);
		$c_username = str_replace(';', '', $c_username);
		$c_username = str_replace('!', '', $c_username);
		$c_username = str_replace('?', '', $c_username);
		$c_username = str_replace("'", '', $c_username);
		$c_username = str_replace('"', '', $c_username);
		if ($c_uid = username2uid($c_username)) {
			logger_print("insert u:".$c_username." uid:".$c_uid." dt:".$in['sms_datetime']." s:".$in['sms_sender']." r:".$in['sms_receiver']." m:".$in['message'], 3, "pvat");
			insertsmstoinbox($in['sms_datetime'], $in['sms_sender'], $c_username, $in['message'], $in['sms_receiver']);
			logger_print("insert end", 3, "pvat");
			$ret['hooked'] = true;
		}
	}
	return $ret;
}

?>