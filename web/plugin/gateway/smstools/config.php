<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

$smstools_param['name'] = "smstools";
$smstools_param['spool_dir'] = "/var/spool/sms";

// save plugin's parameters or options in $core_config
$core_config['plugin']['smstools'] = $smstools_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menu']['main_tab']['gateway'];
	$arr_menu[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_smstools&op=manage", _('Manage smstools'));
}
?>