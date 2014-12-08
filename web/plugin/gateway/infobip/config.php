<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayInfobip_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['infobip']['name'] = 'infobip';
	$plugin_config['infobip']['username'] = $db_row['cfg_username'];
	$plugin_config['infobip']['password'] = $db_row['cfg_password'];
	$plugin_config['infobip']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['infobip']['send_url'] = ($db_row['cfg_send_url'] ? $db_row['cfg_send_url'] : 'http://api.infobip.com/api/v3');
	$plugin_config['infobip']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['infobip']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
	// $plugin_config['infobip']['dlr_nopush'] = $db_row['cfg_dlr_nopush'];
	$plugin_config['infobip']['dlr_nopush'] = 1;
}

// smsc configuration
$plugin_config['infobip']['_smsc_config_'] = array();

// $gateway_number = $plugin_config['infobip']['sender'];

// insert to left menu array
//if (isadmin ()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array (
//			"index.php?app=main&inc=gateway_infobip&op=manage",
//			_( 'Manage infobip' ) 
//	);
//}
