<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayRoutesms_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['routesms']['name'] = 'routesms';
	$plugin_config['routesms']['username'] = $db_row['cfg_username'];
	$plugin_config['routesms']['password'] = $db_row['cfg_password'];
	$plugin_config['routesms']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['routesms']['send_url'] = ($db_row['cfg_send_url'] ? $db_row['cfg_send_url'] : 'http://ngn.rmlconnect.net/bulksms/bulksms');
	$plugin_config['routesms']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['routesms']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
	// $plugin_config['routesms']['dlr_nopush'] = $db_row['cfg_dlr_nopush'];
	$plugin_config['routesms']['dlr_nopush'] = 1;
}

// smsc configuration
$plugin_config['routesms']['_smsc_config_'] = array();

// $gateway_number = $plugin_config['routesms']['sender'];

// insert to left menu array
//if (isadmin ()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array (
//			"index.php?app=main&inc=gateway_routesms&op=manage",
//			_( 'Manage routesms' ) 
//	);
//}
