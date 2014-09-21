<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['gammu']['name'] = "gammu";
$plugin_config['gammu']['path'] = "/var/spool/gammu";
$plugin_config['gammu']['dlr'] = FALSE;

// smsc configuration
$plugin_config['gammu']['_smsc_config_'] = array();

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_gammu&op=manage", _('Manage gammu'));
//}
