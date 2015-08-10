<?php
defined('_SECURE_') or die('Forbidden');

$callback_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/jasmin/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://" . $callback_url;

$data = registry_search(0, 'gateway', 'jasmin');
$plugin_config['jasmin'] = $data['gateway']['jasmin'];
$plugin_config['jasmin']['name'] = 'jasmin';
if (!$plugin_config['jasmin']['url']) {
	$plugin_config['jasmin']['url'] = 'https://127.0.0.1:1401/send';
}
if (!$plugin_config['jasmin']['callback_url']) {
	$plugin_config['jasmin']['callback_url'] = $callback_url;
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
