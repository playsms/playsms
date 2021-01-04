<?php
defined('_SECURE_') or die('Forbidden');

$callback_url = '';
if (!$core_config['daemon_process']) {
	$callback_url = _HTTP_PATH_BASE_ . "/index.php?app=call&cat=gateway&plugin=jasmin&access=callback";
}

$data = registry_search(0, 'gateway', 'jasmin');
$plugin_config['jasmin'] = $data['gateway']['jasmin'];
$plugin_config['jasmin']['name'] = 'jasmin';
$plugin_config['jasmin']['default_url'] = 'https://127.0.0.1:1401/send';
$plugin_config['jasmin']['default_callback_url'] = $callback_url;
if (!trim($plugin_config['jasmin']['url'])) {
	$plugin_config['jasmin']['url'] = $plugin_config['jasmin']['default_url'];
}
if (!trim($plugin_config['jasmin']['callback_url'])) {
	$plugin_config['jasmin']['callback_url'] = $plugin_config['jasmin']['default_callback_url'];
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
