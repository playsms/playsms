<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayUplink_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$uplink_param['name']			= $db_row['cfg_name'];
	$uplink_param['master']			= $db_row['cfg_master'];
	$uplink_param['username']		= $db_row['cfg_username'];
	$uplink_param['token']			= $db_row['cfg_token'];
	$uplink_param['global_sender']		= $db_row['cfg_global_sender'];
	$uplink_param['path']     		= $db_row['cfg_incoming_path'];
	$uplink_param['additional_param']	= $db_row['cfg_additional_param'];
	$uplink_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['uplink'] = $uplink_param;

//$gateway_number = $uplink_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_uplink&op=manage", _('Manage uplink'));
}
?>