<?php
defined('_SECURE_') or die('Forbidden');

// get gammu config from registry
$data = registry_search(0, 'gateway', 'smstools');

$plugin_config['smstools']['name'] = 'smstools';
$plugin_config['smstools']['modem'] = trim($data['gateway']['smstools']['modem']);
$plugin_config['smstools']['sms_receiver'] = trim($data['gateway']['smstools']['sms_receiver']);
$plugin_config['smstools']['spool_dir'] = trim(core_sanitize_path($data['gateway']['smstools']['spool_dir']));
if (!$plugin_config['smstools']['spool_dir']) {
	$plugin_config['smstools']['spool_dir'] = "/var/spool/sms";
}
$plugin_config['smstools']['spool_bak'] = trim(core_sanitize_path($data['gateway']['smstools']['spool_bak']));
if (!$plugin_config['smstools']['spool_bak']) {
	$plugin_config['smstools']['spool_bak'] = "/var/spool/smsbackup";
}

// smsc configuration
$plugin_config['smstools']['_smsc_config_'] = array(
	'sms_receiver' => _('Receiver number'),
	'modem' => _('Modem name') 
);

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_smstools&op=manage", _('Manage smstools'));
//}
