<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['dev']['name']			= 'dev';
$plugin_config['dev']['enable_incoming']	= true;
$plugin_config['dev']['enable_outgoing']	= true;

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_dev&op=manage", _('Manage dev'));
//}
