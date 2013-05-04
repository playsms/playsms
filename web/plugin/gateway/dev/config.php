<?php
defined('_SECURE_') or die('Forbidden');

$dev_param['name']		= 'dev';
$dev_param['enable_incoming']	= true;
$dev_param['enable_outgoing']	= true;

// save plugin's parameters or options in $core_config
$core_config['plugin']['dev'] = $dev_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_dev&op=manage", _('Manage dev'));
}
?>