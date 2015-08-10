<?php
defined('_SECURE_') or die('Forbidden');

$callback_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/jasmin/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://" . $callback_url;

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayJasmin_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['jasmin']['name'] = 'jasmin';
	$plugin_config['jasmin']['url'] = ($db_row['url'] ? $db_row['url'] : 'https://127.0.0.1:1401/send');
	$plugin_config['jasmin']['callback_url'] = ($db_row['callback_url'] ? $db_row['callback_url'] : $callback_url);
	$plugin_config['jasmin']['api_username'] = $db_row['api_username'];
	$plugin_config['jasmin']['api_password'] = $db_row['api_password'];
	$plugin_config['jasmin']['module_sender'] = $db_row['module_sender'];
	$plugin_config['jasmin']['datetime_timezone'] = $db_row['datetime_timezone'];
}

// smsc configuration
$plugin_config['jasmin']['_smsc_config_'] = array(
	'url' => _('Jasmin send SMS URL'),
	'callback_url' => _('Callback URL'),
	'api_username' => _('API username'),
	'api_password' => _('API password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_jasmin&op=manage", _('Manage jasmin'));
//}
