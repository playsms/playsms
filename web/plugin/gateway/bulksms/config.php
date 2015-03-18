<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayBulksms_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['bulksms']['name'] = 'bulksms';
	$plugin_config['bulksms']['username'] = $db_row['cfg_username'];
	$plugin_config['bulksms']['password'] = $db_row['cfg_password'];
	$plugin_config['bulksms']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['bulksms']['send_url'] = $db_row['cfg_send_url'];
	$plugin_config['bulksms']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['bulksms']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

if (!$plugin_config['bulksms']['additional_param']) {
	$plugin_config['bulksms']['additional_param'] = "routing_group=1&repliable=0";
}

// smsc configuration
$plugin_config['bulksms']['_smsc_config_'] = array();

// $gateway_number = $plugin_config['bulksms']['sender'];

// insert to left menu array
//if (isadmin ()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array (
//			"index.php?app=main&inc=gateway_bulksms&op=manage",
//			_ ( 'Manage bulksms' ) 
//	);
//}
