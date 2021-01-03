<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['dev']['name'] = 'dev';
$plugin_config['dev']['enable_incoming'] = true;
$plugin_config['dev']['enable_outgoing'] = true;

// smsc configuration
$plugin_config['dev']['_smsc_config_'] = array();

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['settings'];
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=gateway_dev&route=simulate&op=simulate",
		_('Simulate incoming SMS') 
	);
}
