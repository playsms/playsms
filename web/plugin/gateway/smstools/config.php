<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['smstools']['name'] = "smstools";
$plugin_config['smstools']['spool_dir'] = "/var/spool/sms";
$plugin_config['smstools']['spool_bak'] = "/var/spool/smsbackup";

// smsc configuration
$plugin_config['smstools']['_smsc_config_'] = array();

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_smstools&op=manage", _('Manage smstools'));
//}
