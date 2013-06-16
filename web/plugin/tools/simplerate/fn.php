<?php
defined('_SECURE_') or die('Forbidden');

function simplerate_getdst($id) {
	if ($id) {
		$db_query = "SELECT dst FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$dst = $db_row['dst'];
	}
	return $dst;
}

function simplerate_getprefix($id) {
	if ($id) {
		$db_query = "SELECT prefix FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$prefix = $db_row['prefix'];
	}
	return $prefix;
}

function simplerate_getbyid($id) {
	if ($id) {
		$db_query = "SELECT rate FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$rate = $db_row['rate'];
	}
	$rate = ( ($rate > 0) ? $rate : 0 );
	return $rate;
}

function simplerate_getbyprefix($p_dst) {
	global $default_rate;
	$rate = $default_rate;
	$prefix = preg_replace('/[^0-9.]*/','',$p_dst);
	$m = ( strlen($prefix) > 10 ? 10 : strlen($prefix) );
	for ($i=$m+1;$i>0;$i--) {
		$prefix = substr($prefix, 0, $i);
		$db_query = "SELECT rate FROM "._DB_PREF_."_toolsSimplerate WHERE prefix='$prefix'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$rate = $db_row['rate'];
			break;
		}
	}
	$rate = ( ($rate > 0) ? $rate : 0 );
	return $rate;
}

// -----------------------------------------------------------------------------------------

function simplerate_hook_rate_setusercredit($uid, $remaining=0) {
	$ok = false;
	logger_print("saving uid:".$uid." remaining:".$remaining, 2, "simplerate setusercredit");
	$db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp='".core_get_datetime()."',credit='$remaining' WHERE uid='$uid'";
	if ($db_result = @dba_affected_rows($db_query)) {
		logger_print("saved uid:".$uid." remaining:".$remaining, 2, "simplerate setusercredit");
		$ok = true;
	}
	return $ok;
}

function simplerate_hook_rate_getusercredit($username) {
	if ($username) {
		$db_query = "SELECT credit FROM "._DB_PREF_."_tblUser WHERE username='$username'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$credit = $db_row['credit'];
	}
	$credit = ( $credit ? $credit : 0 );
	return $credit;
}

function simplerate_hook_rate_cansend($username, $sms_to) {
	global $default_rate;
	$credit = rate_getusercredit($username);
	$maxrate = simplerate_getbyprefix($sms_to);
	if ($default_rate > $maxrate) {
		$maxrate = $default_rate;
	}
	logger_print("check username:".$username." sms_to:".$sms_to." credit:".$credit." maxrate:".$maxrate, 2, "simplerate cansend");
	if ($ok = ( ($credit >= $maxrate) ? true : false )) {
		logger_print("allowed username:".$username." sms_to:".$sms_to." credit:".$credit." maxrate:".$maxrate, 2, "simplerate cansend");
	}
	return $ok;
}

function simplerate_hook_rate_deduct($smslog_id) {
        global $core_config;
	$ok = false;
	logger_print("enter smslog_id:".$smslog_id, 2, "simplerate deduct");
	$db_query = "SELECT p_dst,p_footer,p_msg,uid,unicode FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$p_footer = $db_row['p_footer'];
		$uid = $db_row['uid'];
                $unicode = $db_row['unicode'];
		if ($p_dst && $p_msg && $uid) {
                        
                        // get sms count
                        $sms_length = ( $unicode ? 70 : 160 );
                        $p_msg_len = strlen($p_msg) + strlen($p_footer);
                        $count = 1;
                        if ($core_config['main']['cfg_sms_max_count'] > 1) {
                                if ($p_msg_len > $sms_length) {
                                        $count = ceil($p_msg_len / ($sms_length - 7));
                                }
                        }

                        $rate = simplerate_getbyprefix($p_dst);
			$charge = $count * $rate;
			$username = uid2username($uid);
			$credit = rate_getusercredit($username);
			$remaining = $credit - $charge;
			logger_print("deduct smslog_id:".$smslog_id." msglen:".$p_msg_len." count:".$count." rate:".$rate." charge:".$charge." credit:".$credit." remaining:".$remaining, 2, "simplerate deduct");
			if (rate_setusercredit($uid, $remaining)) {
				if (billing_post($smslog_id, $rate, $credit, $count, $charge)) {
					$ok = true;
				}
			}
		}
	}
	return $ok;
}

function simplerate_hook_rate_refund($smslog_id) {
        global $core_config;
	$ok = false;
	logger_print("start smslog_id:".$smslog_id, 2, "simplerate refund");
	$db_query = "SELECT p_dst,p_msg,uid FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='2' AND smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$p_footer = $db_row['p_footer'];
		$uid = $db_row['uid'];
                $unicode = $db_row['unicode'];
		if ($p_dst && $p_msg && $uid) {
			if (billing_rollback($smslog_id)) {
				$bill = billing_getdata($smslog_id);
				$credit = $bill['credit'];
				$charge = $bill['charge'];
				$status = $bill['status'];
				logger_print("rolling smslog_id:".$smslog_id, 2, "simplerate refund");
				if ($status == '2') {
					$username = uid2username($uid);
					$credit = rate_getusercredit($username);
					$remaining = $credit + $charge;
					if (rate_setusercredit($uid, $remaining)) {
						logger_print("refund smslog_id:".$smslog_id, 2, "simplerate refund");
						$ok = true;
					}
				}
			}
		}
	}
	return $ok;
}

function simplerate_hook_setsmsdeliverystatus($smslog_id,$uid,$p_status) {
	//logger_print("start smslog_id:".$smslog_id, 2, "simplerate setsmsdeliverystatus");
	if ($p_status == 2) {
		// check in billing table smslog_id with status=0, status=1 is finalized, status=2 is rolled-back
		$db_query = "SELECT id FROM "._DB_PREF_."_tblBilling WHERE status='0' AND smslog_id='$smslog_id'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			rate_refund($smslog_id);
		}
	}
}

?>