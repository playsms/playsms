<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTelnyx_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['']['name'] = 'telnyx';
	$plugin_config['telnyx']['url'] = ($db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://sms.telnyx.com');
	$plugin_config['telnyx']['callback_url'] = ($db_row['cfg_callback_url'] ? $db_row['cfg_callback_url'] : $core_config['http_path']['base'] . 'plugin/gateway/telnyx/callback.php');
	$plugin_config['telnyx']['auth_token'] = $db_row['cfg_auth_token'];
	$plugin_config['telnyx']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['telnyx']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['telnyx']['_smsc_config_'] = array(
	'auth_token' => _('Auth Token'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);

//$gateway_number = $plugin_config['telnyx']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_telnyx&op=manage", _('Manage telnyx'));
//}
