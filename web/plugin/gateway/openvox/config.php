<?php
defined('_SECURE_') or die('Forbidden');

// get kannel config from registry
$data = registry_search(1, 'gateway', 'openvox');
$plugin_config['openvox'] = $data['gateway']['openvox'];
$plugin_config['openvox']['name'] = 'openvox';
$plugin_config['openvox']['gateway_port'] = ($plugin_config['openvox']['gateway_port'] ? $plugin_config['openvox']['gateway_port'] : '80');

// smsc configuration
$plugin_config['openvox']['_smsc_config_'] = array(
	'gateway_host' => _('Gateway host'),
	'gateway_port' => _('Gateway port'),
	'username' => _('Username'),
	'password' => _('Password') 
);

//$gateway_number = $plugin_config['openvox']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_openvox&op=manage", _('Manage openvox'));
//}
