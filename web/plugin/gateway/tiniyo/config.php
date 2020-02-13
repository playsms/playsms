<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTiniyo_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['tiniyo']['name'] = 'tiniyo';
	$plugin_config['tiniyo']['url'] = ($db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://api.tiniyo.com');
	$plugin_config['tiniyo']['callback_url'] = ($db_row['cfg_callback_url'] ? $db_row['cfg_callback_url'] : $core_config['http_path']['base'] . 'plugin/gateway/tiniyo/callback.php');
	$plugin_config['tiniyo']['account_sid'] = $db_row['cfg_auth_id'];
	$plugin_config['tiniyo']['auth_token'] = $db_row['cfg_auth_secret'];
	$plugin_config['tiniyo']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['tiniyo']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['tiniyo']['_smsc_config_'] = array(
	'account_sid' => _('Auth ID'),
	'auth_token' => _('Auth SecretID'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);

//$gateway_number = $plugin_config['tiniyo']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_tiniyo&op=manage", _('Manage tiniyo'));
//}
