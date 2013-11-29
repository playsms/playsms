<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayTwilio_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$twilio_param['name']			= $db_row['cfg_name'];
	$twilio_param['url']			= ( $db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://api.twilio.com' );
	$twilio_param['callback_url']		= ( $db_row['cfg_callback_url'] ? $db_row['cfg_callback_url'] : $core_config['http_path']['base'].'plugin/gateway/twilio/callback.php' );
	$twilio_param['account_sid']		= $db_row['cfg_account_sid'];
	$twilio_param['auth_token']		= $db_row['cfg_auth_token'];
	$twilio_param['global_sender']		= $db_row['cfg_global_sender'];
	$twilio_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['twilio'] = $twilio_param;

//$gateway_number = $twilio_param['global_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_twilio&op=manage", _('Manage twilio'));
//}
?>