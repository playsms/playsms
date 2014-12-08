<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayUplink_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['uplink']['name'] = 'uplink';
	$plugin_config['uplink']['master'] = $db_row['cfg_master'];
	$plugin_config['uplink']['username'] = $db_row['cfg_username'];
	$plugin_config['uplink']['token'] = $db_row['cfg_token'];
	$plugin_config['uplink']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['uplink']['path'] = $db_row['cfg_incoming_path'];
	$plugin_config['uplink']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['uplink']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
	$plugin_config['uplink']['try_disable_footer'] = $db_row['cfg_try_disable_footer'];
}

// smsc configuration
$plugin_config['uplink']['_smsc_config_'] = array();

//$gateway_number = $plugin_config['uplink']['module_sender'];
// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_uplink&op=manage", _('Manage uplink'));
//}
