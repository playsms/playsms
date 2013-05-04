<?php
defined('_SECURE_') or die('Forbidden');

$gammu_param['name'] = "gammu";
$gammu_param['path'] = "/var/spool/gammu";

// save plugin's parameters or options in $core_config
$core_config['plugin']['gammu'] = $gammu_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_gammu&op=manage", _('Manage gammu'));
}
?>