<?php
defined('_SECURE_') or die('Forbidden');

function outgoing_getdata() {
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureOutgoing ORDER BY dst";
	$db_result = dba_query($db_query);
	
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	
	return $ret;
}

function outgoing_getdst($id) {
	if ($id) {
		$db_query = "SELECT dst FROM " . _DB_PREF_ . "_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$dst = $db_row['dst'];
	}
	return $dst;
}

function outgoing_getprefix($id) {
	if ($id) {
		$db_query = "SELECT prefix FROM "._DB_PREF_."_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$prefix = $db_row['prefix'];
	}
	return $prefix;
}

function outgoing_getbyid($id) {
	if ($id) {
		$db_query = "SELECT gateway FROM "._DB_PREF_."_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$rate = $db_row['gateway'];
	}
	// $rate = ( ($rate > 0) ? $rate : 0 );
	return $rate;
}

// // -----------------------------------------------------------------------------------------

// function outgoing_hook_rate_getbyprefix($sms_to) {
// 	global $core_config;
// 	$found = FALSE;
// 	$default_rate = ( $core_config['main']['default_rate'] > 0 ? $core_config['main']['default_rate'] : 0 );
// 	$rate = $default_rate;
// 	$prefix = preg_replace('/[^0-9.]*/','',$sms_to);
// 	$m = ( strlen($prefix) > 10 ? 10 : strlen($prefix) );
// 	for ($i=$m+1;$i>0;$i--) {
// 		$prefix = substr($prefix, 0, $i);
// 		$db_query = "SELECT id,dst,prefix,rate FROM "._DB_PREF_."_featureOutgoing WHERE prefix='$prefix'";
// 		$db_result = dba_query($db_query);
// 		$db_row = dba_fetch_array($db_result);
// 		if ($db_row['id']) {
// 			$rate = $db_row['rate'];
// 			$found = TRUE;
// 			break;
// 		}
// 	}
// 	if ($found) {
// 		logger_print("found rate id:".$db_row['id']." prefix:".$db_row['prefix']." rate:".$rate." description:".$db_row['dst']." to:".$sms_to, 3, "outgoing_hook_rate_getbyprefix");
// 	} else {
// 		logger_print("rate not found to:".$sms_to." default_rate:".$default_rate, 3, "outgoing_hook_rate_getbyprefix");
// 	}
// 	$rate = ( ($rate > 0) ? $rate : 0 );
// 	return $rate;
// }

// function outgoing_hook_rate_setusercredit($uid, $balance=0) {
// 	$ok = false;
// 	logger_print("saving uid:".$uid." balance:".$balance, 2, "outgoing setusercredit");
// 	$db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp='".mktime()."',credit='$balance' WHERE uid='$uid'";
// 	if ($db_result = @dba_affected_rows($db_query)) {
// 		logger_print("saved uid:".$uid." balance:".$balance, 2, "outgoing setusercredit");
// 		$ok = true;
// 	}
// 	return $ok;
// }

// function outgoing_hook_rate_getusercredit($username) {
// 	if ($username) {
// 		$db_query = "SELECT credit FROM "._DB_PREF_."_tblUser WHERE username='$username'";
// 		$db_result = dba_query($db_query);
// 		$db_row = dba_fetch_array($db_result);
// 		$credit = $db_row['credit'];
// 	}
// 	$credit = ( $credit ? $credit : 0 );
// 	return $credit;
// }

// function outgoing_hook_rate_getcharges($sms_len, $unicode, $sms_to) {
// 	global $core_config;

// 	// get sms count
// 	$length = ( $unicode ? 70 : 160 );
// 	$count = 1;
// 	if ($core_config['main']['sms_max_count'] > 1) {
// 	        if ($sms_len > $length) {
// 	                $count = ceil($sms_len / ($length - 7));
// 	        }
// 	}

// 	// calculate charges
// 	$rate = rate_getbyprefix($sms_to);
// 	$charge = $count * $rate;

// 	return array($count, $rate, $charge);
// }

// function outgoing_hook_rate_cansend($username, $sms_len, $unicode, $sms_to) {
// 	global $core_config;

// 	list($count, $rate, $charge) = rate_getcharges($sms_len, $unicode, $sms_to);

// 	// sender's
// 	$credit = rate_getusercredit($username);
// 	$balance = $credit - $charge;

// 	// parent's when sender is a subuser
// 	$uid = user_username2uid($username);
// 	$parent_uid = user_getparentbyuid($uid);
// 	if ($parent_uid) {
// 		$username_parent = user_uid2username($parent_uid);
// 		$credit_parent = rate_getusercredit($username_parent);
// 		$balance_parent = $credit_parent - $charge;
// 	}

// 	if ($parent_uid) {
// 		if ($balance_parent >= 0) {
// 			logger_print("allowed subuser uid:".$uid." parent_uid:".$parent_uid." sms_to:".$sms_to." credit:".$credit." count:".$count." rate:".$rate." charge:".$charge." balance:".$balance." balance_parent:".$balance_parent, 2, "outgoing cansend");
// 			return TRUE;
// 		} else {
// 			logger_print("disallowed subuser uid:".$uid." parent_uid:".$parent_uid." sms_to:".$sms_to." credit:".$credit." count:".$count." rate:".$rate." charge:".$charge." balance:".$balance." balance_parent:".$balance_parent, 2, "outgoing cansend");
// 			return FALSE;
// 		}
// 	} else {
// 		if ($balance >= 0) {
// 			logger_print("allowed user uid:".$uid." sms_to:".$sms_to." credit:".$credit." count:".$count." rate:".$rate." charge:".$charge." balance:".$balance, 2, "outgoing cansend");
// 			return TRUE;
// 		} else {
// 			logger_print("disallowed user uid:".$uid." sms_to:".$sms_to." credit:".$credit." count:".$count." rate:".$rate." charge:".$charge." balance:".$balance, 2, "outgoing cansend");
// 			return FALSE;
// 		}
// 	}
// }

// function outgoing_hook_rate_deduct($smslog_id) {
//         global $core_config;

// 	logger_print("enter smslog_id:".$smslog_id, 2, "outgoing deduct");
// 	$db_query = "SELECT p_dst,p_footer,p_msg,uid,unicode FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
// 	$db_result = dba_query($db_query);
// 	if ($db_row = dba_fetch_array($db_result)) {
// 		$p_dst = $db_row['p_dst'];
// 		$p_msg = $db_row['p_msg'];
// 		$p_footer = $db_row['p_footer'];
// 		$uid = $db_row['uid'];
//                 $unicode = $db_row['unicode'];
// 		if ($p_dst && $p_msg && $uid) {

//                         // get charge
//                         $p_msg_len = strlen($p_msg) + strlen($p_footer);
// 			list($count, $rate, $charge) = rate_getcharges($p_msg_len, $unicode, $p_dst);

//                         // sender's
// 			$username = user_uid2username($uid);
// 			$credit = rate_getusercredit($username);
// 			$balance = $credit - $charge;

// 			// parent's when sender is a subuser
// 			$parent_uid = user_getparentbyuid($uid);
// 			if ($parent_uid) {
// 				$username_parent = user_uid2username($parent_uid);
// 				$credit_parent = rate_getusercredit($username_parent);
// 				$balance_parent = $credit_parent - $charge;
// 			}

// 			// if sender have parent then deduct parent first
// 			if ($parent_uid) {
// 				if (! rate_setusercredit($parent_uid, $balance_parent)) {
// 					return FALSE;
// 				}
// 				logger_print("parent uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id." msglen:".$p_msg_len." count:".$count." rate:".$rate." charge:".$charge." credit_parent:".$credit_parent." balance_parent:".$balance_parent, 2, "outgoing deduct");
// 			}

// 			if (rate_setusercredit($uid, $balance)) {
// 				logger_print("user uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id." msglen:".$p_msg_len." count:".$count." rate:".$rate." charge:".$charge." credit:".$credit." balance:".$balance, 2, "outgoing deduct");
// 				if (billing_post($smslog_id, $rate, $credit, $count, $charge)) {
// 					logger_print("deduct successful uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id, 3, "outgoing deduct");
// 					return TRUE;
// 				} else {
// 					logger_print("deduct failed uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id, 3, "outgoing deduct");
// 					return FALSE;
// 				}
// 			}
// 		}
// 	}

// 	return FALSE;
// }

// function outgoing_hook_rate_refund($smslog_id) {
//         global $core_config;

// 	logger_print("start smslog_id:".$smslog_id, 2, "outgoing refund");
// 	$db_query = "SELECT p_dst,p_msg,uid FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='2' AND smslog_id='$smslog_id'";
// 	$db_result = dba_query($db_query);
// 	if ($db_row = dba_fetch_array($db_result)) {
// 		$p_dst = $db_row['p_dst'];
// 		$p_msg = $db_row['p_msg'];
// 		$p_footer = $db_row['p_footer'];
// 		$uid = $db_row['uid'];
//                 $unicode = $db_row['unicode'];
// 		if ($p_dst && $p_msg && $uid) {
// 			if (billing_rollback($smslog_id)) {
// 				$bill = billing_getdata($smslog_id);
// 				$credit = $bill['credit'];
// 				$charge = $bill['charge'];
// 				$status = $bill['status'];
// 				logger_print("rolling smslog_id:".$smslog_id, 2, "outgoing refund");
// 				if ($status == '2') {

// 					// sender's
// 					$username = user_uid2username($uid);
// 					$credit = rate_getusercredit($username);
// 					$balance = $credit + $charge;

// 					// parent's when sender is a subuser
// 					$parent_uid = user_getparentbyuid($uid);
// 					if ($parent_uid) {
// 						$username_parent = user_uid2username($parent_uid);
// 						$credit_parent = rate_getusercredit($username_parent);
// 						$balance_parent = $credit_parent + $charge;
// 					}

// 					// if sender have parent then deduct parent first
// 					if ($parent_uid) {
// 						if (! rate_setusercredit($parent_uid, $balance_parent)) {
// 							return FALSE;
// 						}
// 						logger_print("parent uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id." credit_parent:".$credit_parent." balance_parent:".$balance_parent, 2, "outgoing refund");
// 					}

// 					if (rate_setusercredit($uid, $balance)) {
// 						logger_print("user uid:".$uid." parent_uid:".$parent_uid." smslog_id:".$smslog_id." credit:".$credit." balance:".$balance, 2, "outgoing refund");
// 						return TRUE;
// 					}
// 				}
// 			}
// 		}
// 	}

// 	return FALSE;
// }

// function outgoing_hook_setsmsdeliverystatus($smslog_id,$uid,$p_status) {
// 	//logger_print("start smslog_id:".$smslog_id, 2, "outgoing setsmsdeliverystatus");
// 	if ($p_status == 2) {
// 		// check in billing table smslog_id with status=0, status=1 is finalized, status=2 is rolled-back
// 		$db_query = "SELECT id FROM "._DB_PREF_."_tblBilling WHERE status='0' AND smslog_id='$smslog_id'";
// 		$db_result = dba_query($db_query);
// 		if ($db_row = dba_fetch_array($db_result)) {
// 			rate_refund($smslog_id);
// 		}
// 	}
// }
