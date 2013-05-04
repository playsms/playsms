<?php
defined('_SECURE_') or die('Forbidden');
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayClickatell_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$clickatell_param['name']			= $db_row['cfg_name'];
	$clickatell_param['api_id']			= $db_row['cfg_api_id'];
	$clickatell_param['username']		= $db_row['cfg_username'];
	$clickatell_param['password']		= $db_row['cfg_password'];
	$clickatell_param['global_sender']			= $db_row['cfg_sender'];
	$clickatell_param['send_url']		= $db_row['cfg_send_url'];
	$clickatell_param['incoming_path']		= $db_row['cfg_incoming_path'];
	$clickatell_param['additional_param']	= $db_row['cfg_additional_param'];
	$clickatell_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

if (! $clickatell_param['additional_param']) {
	$clickatell_param['additional_param'] = "deliv_ack=1&callback=3";
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['clickatell'] = $clickatell_param;

//$gateway_number = $clickatell_param['sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_clickatell&op=manage", _('Manage clickatell'));
}
?>