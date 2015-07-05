<?php
defined('_SECURE_') or die('Forbidden');

// get gammu config from registry
$data = registry_search(0, 'gateway', 'gammu');

$plugin_config['gammu']['name'] = 'gammu';
$plugin_config['gammu']['sms_receiver'] = trim($data['gateway']['gammu']['sms_receiver']);
$plugin_config['gammu']['path'] = trim(core_sanitize_path($data['gateway']['gammu']['path']));
if (!$plugin_config['gammu']['path']) {
	$plugin_config['gammu']['path'] = '/var/spool/gammu';
}
$plugin_config['gammu']['dlr'] = TRUE;

// smsc configuration
$plugin_config['gammu']['_smsc_config_'] = array(
	'sms_receiver' => _('Receiver number'),
	'path' => _('Spool folder') 
);

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_gammu&op=manage", _('Manage gammu'));
//}
