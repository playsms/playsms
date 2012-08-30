<?php
if (! $called_from_hook_call) {
	chdir ("../../../");
	include "init.php";
	include $apps_path['libs']."/function.php";
	chdir ("plugin/gateway/kannel");
	$requests = $_REQUEST;
}

$remote_addr = $_SERVER['REMOTE_ADDR'];
// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];
// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $kannel_param['bearerbox_host'])
if ($remote_addr != $kannel_param['bearerbox_host'] && $remote_host != $kannel_param['bearerbox_host']) {
	logger_print("exit remote_addr:".$remote_addr." remote_host:".$remote_host." bearerbox_host:".$kannel_param['bearerbox_host'], 3, "kannel incoming");
	exit();
}

$t = trim($requests['t']); 	// sms_datetime
$q = trim($requests['q']); 	// sms_sender
$a = trim($requests['a']); 	// message
$Q = trim($requests['Q']); 	// sms_receiver

logger_print("addr:".$remote_addr." host:".$remote_host." t:".$t." q:".$q." a:".$a." Q:".$Q, 3, "kannel incoming");

// fixme anton - assumed magic quotes gpc is Off, setsmsincomingaction() requires quoted inputs
if (! get_magic_quotes_gpc()) {
	$q = addslashes($q);
	$a = addslashes($a);
}

if ($t && $q && $a) {
	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	setsmsincomingaction($t, $q, $a, $Q);
}
?>