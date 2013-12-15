<?php

error_reporting(0);

if (! $called_from_hook_call) {
	chdir("../../../");
	include "init.php";
	include $apps_path['libs']."/function.php";
	chdir("plugin/feature/sms_sync/");
}

$r = $_REQUEST;
$c_uid = $r['uid'];

$list = registry_search($c_uid, 'feature', 'sms_sync');
$sms_sync_secret = $list['feature']['sms_sync']['secret'];
$sms_sync_enable = $list['feature']['sms_sync']['enable'];

$message_id = $r['message_id'];
$sms_datetime = core_get_datetime();
$sms_sender = $r['from'];
$message = $r['message'];
$sms_receiver = $r['sent_to'];

$ok = FALSE;

if ($sms_sync_enable && $c_uid && ($r['secret'] == $sms_sync_secret) && $message_id) {
	$db_table = _DB_PREF_.'_featureSmssysnc';
	$conditions = array('uid' => $c_uid, 'message_id' => $message_id);
	if (dba_isavail($db_table, $conditions, 'AND')) {
		logger_print("saving uid:".$c_uid." dt:".$sms_datetime." ts:".$r['sent_timestamp']." message_id:".$message_id." s:".$sms_sender." m:".$message." r:".$sms_receiver, 3, "sms_sync sync");
		// if keyword does not exists (checkavailablekeyword == TRUE)
		// then prefix the message with an @username so that it will be routed to $c_uid's inbox
		$m = explode(' ', $message);
		$c_m = str_replace('#', '', $m[0]);
		if (checkavailablekeyword($c_m)) {
			logger_print("forwarded to inbox uid:" . $c_uid . " message_id:" . $message_id, 3, "sms_sync sync");
			$message = "@" . user_uid2username($c_uid) . " " . $message;
		}
		if ($recvsms_id = recvsms($sms_datetime, $sms_sender, $message, $sms_receiver)) {
			$items = array('uid' => $c_uid, 'message_id' => $message_id, 'recvsms_id' => $recvsms_id);
			dba_add($db_table, $items);
			logger_print("saved uid:" . $c_uid . " message_id:" . $message_id . " recvsms_id:" . $recvsms_id, 3, "sms_sync sync");
			$ret = array(
			    'payload' => array(
				'success' => "true",
				'error' => NULL
			    )
			);
			$ok = TRUE;
		} else {
			$error_string = "fail to save uid:" . $c_uid . " message_id:" . $message_id;
			logger_print($error_string, 3, "sms_sync sync");
		}
	} else {
		$error_string = "duplicate message uid:".$c_uid." message_id:".$message_id;
		logger_print($error_string, 3, "sms_sync sync");
	}
	if (! $ok) {
		$ret = array(
			'payload' => array(
				'success' => "false",
				'error' => $error_string
			)
		);
	}
	echo json_encode($ret);
}

?>