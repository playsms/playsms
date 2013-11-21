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
$sms_datetime = $core_config['datetime']['now'];
$sms_sender = $r['from'];
$message = $r['message'];
$sms_receiver = $r['sent_to'];

$ok = FALSE;

if ($sms_sync_enable && $c_uid && ($r['secret'] == $sms_sync_secret) && $message_id) {
	$db_table = _DB_PREF_.'_featureSmssysnc';
	$conditions = array('uid' => $c_uid, 'message_id' => $message_id);
	if (dba_isavail($db_table, $conditions)) {
		logger_print("saving dt:".$sms_datetime." uid:".$c_uid." ts:".$r['sent_timestamp']." message_id:".$message_id." s:".$sms_sender." m:".$message." r:".$sms_receiver, 3, "sms_sync sync");
		if ($recvsms_id = recvsms($sms_datetime, $sms_sender, $message, $sms_receiver)) {
			$items = array('message_id' => $message_id, 'recvsms_id' => $recvsms_id);
			dba_add($db_table, $items);
			logger_print("saved message_id:".$message_id." recvsms_id:".$recvsms_id, 3, "sms_sync sync");
			$ret = array(
				'payload' => array(
					'success' => true,
					'error' => NULL
				)
			);
			$ok = TRUE;
		} else {
			logger_print("fail to save message_id:".$message_id, 3, "sms_sync sync");
		}
	}
}

if (! $ok) {
	$ret = array(
		'payload' => array(
			'success' => "false",
			'error' => "Unable to process"
		)
	);
}

echo json_encode($ret);

?>