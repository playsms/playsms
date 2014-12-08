<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayMsgtoolbox_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['msgtoolbox']['name'] = 'msgtoolbox';
	$plugin_config['msgtoolbox']['url'] = $db_row['cfg_url'];
	$plugin_config['msgtoolbox']['route'] = $db_row['cfg_route'];
	$plugin_config['msgtoolbox']['username'] = $db_row['cfg_username'];
	$plugin_config['msgtoolbox']['password'] = $db_row['cfg_password'];
	$plugin_config['msgtoolbox']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['msgtoolbox']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['msgtoolbox']['_smsc_config_'] = array();

//$gateway_number = $plugin_config['msgtoolbox']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_msgtoolbox&op=manage", _('Manage msgtoolbox'));
//}
