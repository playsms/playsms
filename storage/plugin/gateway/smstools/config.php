<?php
defined('_SECURE_') or die('Forbidden');

// get gammu config from registry
$data = registry_search(0, 'gateway', 'smstools');

$plugin_config['smstools']['name'] = 'smstools';
$plugin_config['smstools']['default_queue'] = trim(core_sanitize_path($data['gateway']['smstools']['default_queue']));
if (!$plugin_config['smstools']['default_queue']) {
	$plugin_config['smstools']['default_queue'] = "/var/spool/sms";
}

// smsc configuration
$plugin_config['smstools']['_smsc_config_'] = array(
	'sms_receiver' => _('Receiver number'),
	'queue' => _('Queue directory') 
);

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_smstools&op=manage", _('Manage smstools'));
//}
