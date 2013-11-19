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

if ($smssync_enable && ($r['secret'] == $smssync_secret)) {
	$sms_datetime = $core_config['datetime']['now'];
	$sms_sender = $r['from'];
	$message = $r['message'];
	$sms_receiver = $r['sent_to'];
	logger_print("dt:".$sms_datetime." ts:".$r['sent_timestamp']." id:".$r['message_id']." s:".$sms_sender." m:".$message." r:".$sms_receiver, "3", "smssync callback");
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver);
}

?>

