<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['blocked']['name'] = 'blocked';

// smsc configuration
$plugin_config['blocked']['_smsc_config_'] = array();

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_blocked&op=manage", _('Manage blocked'));
//}
