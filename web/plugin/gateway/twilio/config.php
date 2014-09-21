<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTwilio_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['twilio']['name'] = 'twilio';
	$plugin_config['twilio']['url'] = ($db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://api.twilio.com');
	$plugin_config['twilio']['callback_url'] = ($db_row['cfg_callback_url'] ? $db_row['cfg_callback_url'] : $core_config['http_path']['base'] . 'plugin/gateway/twilio/callback.php');
	$plugin_config['twilio']['account_sid'] = $db_row['cfg_account_sid'];
	$plugin_config['twilio']['auth_token'] = $db_row['cfg_auth_token'];
	$plugin_config['twilio']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['twilio']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['twilio']['_smsc_config_'] = array();

//$gateway_number = $plugin_config['twilio']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_twilio&op=manage", _('Manage twilio'));
//}
