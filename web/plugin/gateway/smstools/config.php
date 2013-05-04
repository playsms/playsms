<?php
defined('_SECURE_') or die('Forbidden');

$smstools_param['name'] = "smstools";
$smstools_param['spool_dir'] = "/var/spool/sms";
$smstools_param['spool_bak'] = "/var/spool/smsbackup";

// save plugin's parameters or options in $core_config
$core_config['plugin']['smstools'] = $smstools_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_smstools&op=manage", _('Manage smstools'));
}
?>