<?php

error_reporting(0);

if (! $called_from_hook_call) {
	chdir("../../../");
	include "init.php";
	include $apps_path['libs']."/function.php";
	chdir("plugin/tools/smssync/");
}

$r = $_REQUEST;

$list = registry_search('tools', 'smssync');
$smssync_secret = $list['tools']['smssync']['secret'];
$smssync_enable = $list['tools']['smssync']['enable'];

$message_id = $r['message_id'];
$sms_datetime = $core_config['datetime']['now'];
$sms_sender = $r['from'];
$message = $r['message'];
$sms_receiver = $r['sent_to'];

if ($smssync_enable && ($r['secret'] == $smssync_secret) && $message_id) {
	$db_table = _DB_PREF_.'_toolsSmssysnc';
	$conditions = array('message_id' => $message_id);
	if (dba_isavail($db_table, $conditions)) {
		logger_print("saving dt:".$sms_datetime." ts:".$r['sent_timestamp']." message_id:".$message_id." s:".$sms_sender." m:".$message." r:".$sms_receiver, 3, "smssync sync");
		if ($recvsms_id = recvsms($sms_datetime, $sms_sender, $message, $sms_receiver)) {
			$items = array('message_id' => $message_id, 'recvsms_id' => $recvsms_id);
			dba_add($db_table, $items);
			logger_print("saved message_id:".$message_id." recvsms_id:".$recvsms_id, 3, "smssync sync");
		} else {
			logger_print("fail to save message_id:".$message_id, 3, "smssync sync");
		}
	}
}

?>

