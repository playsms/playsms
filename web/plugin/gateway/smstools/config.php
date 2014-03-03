<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['smstools']['name'] = "smstools";
$plugin_config['smstools']['spool_dir'] = "/var/spool/sms";
$plugin_config['smstools']['spool_bak'] = "/var/spool/smsbackup";

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_smstools&op=manage", _('Manage smstools'));
//}
