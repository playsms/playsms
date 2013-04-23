<?php
defined('_SECURE_') or die('Forbidden');

/*
 * intercept incoming sms and look for # sign followed by group code
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
function bchash_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$ret = array();

	// continue only when keyword does not exists
	$m = explode(' ', $message);
	if (! checkavailablekeyword($m[0])) {
		return $ret;
	}

	// scan for #<sender's phonebook group code>
	$msg = explode(' ', $message);
	if (count($msg) > 1) {
		$in['sms_datetime'] = $sms_datetime;
		$in['sms_sender'] = $sms_sender;
		$in['message'] = $message;
		$in['sms_receiver'] = $sms_receiver;
		$in['msg'] = $msg;
		$ret = bchash_handle($in);
	}

	return $ret;
}

function bchash_handle($in) {
	$ret = array();
	$c_uid = mobile2uid($in['sms_sender']);
	$in['sms_datetime'] = core_display_datetime($in['sms_datetime']);
	$x = array();
	for ($i=0;$i<count($in['msg']);$i++) {
		$c_text = trim($in['msg'][$i]);
		if (substr($c_text, 0, 1) == '#') {
			$x[] = strtolower(substr($c_text, 1));
		}
	}
	$y = array_unique($x);
	foreach ($y as $key => $c_group_code) {
		$c_group_code = str_replace(',', '', $c_group_code);
		$c_group_code = str_replace(':', '', $c_group_code);
		$c_group_code = str_replace(';', '', $c_group_code);
		$c_group_code = str_replace('!', '', $c_group_code);
		$c_group_code = str_replace('?', '', $c_group_code);
		$c_group_code = str_replace("'", '', $c_group_code);
		$c_group_code = str_replace('"', '', $c_group_code);
		if ($c_uid && ($c_gpid = phonebook_groupcode2id($c_uid, $c_group_code))) {
			$c_username = uid2username($c_uid);
			logger_print("bc g:".$c_group_code." gpid:".$c_gpid." uid:".$c_uid." dt:".$in['sms_datetime']." s:".$in['sms_sender']." r:".$in['sms_receiver']." m:".$in['message'], 3, "bchash");
			sendsms_bc($c_username, $c_gpid, $in['message']);
			logger_print("bc end", 3, "bchash");
			$ret['hooked'] = true;
		}
	}
	return $ret;
}

?>