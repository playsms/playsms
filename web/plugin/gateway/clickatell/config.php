<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayClickatell_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['clickatell']['name'] = 'clickatell';
	$plugin_config['clickatell']['api_id'] = $db_row['cfg_api_id'];
	$plugin_config['clickatell']['username'] = $db_row['cfg_username'];
	$plugin_config['clickatell']['password'] = $db_row['cfg_password'];
	$plugin_config['clickatell']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['clickatell']['send_url'] = $db_row['cfg_send_url'];
	$plugin_config['clickatell']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['clickatell']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

if (!$plugin_config['clickatell']['additional_param']) {
	$plugin_config['clickatell']['additional_param'] = "deliv_ack=1&callback=3";
}

// smsc configuration
$plugin_config['clickatell']['_smsc_config_'] = array();

// $gateway_number = $plugin_config['clickatell']['sender'];

// insert to left menu array
//if (isadmin ()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array (
//			"index.php?app=main&inc=gateway_clickatell&op=manage",
//			_( 'Manage clickatell' ) 
//	);
//}
