<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['blocked']['name'] = 'blocked';

// virtual gateway configuration
$plugin_config['blocked']['_dynamic_variables_'] = array();

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_blocked&op=manage", _('Manage blocked'));
//}
