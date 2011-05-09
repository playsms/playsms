<?php
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayKannel_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$kannel_param['name']		= $db_row['cfg_name'];
	$kannel_param['path']		= $db_row['cfg_incoming_path'];
	$kannel_param['username']		= $db_row['cfg_username'];
	$kannel_param['password']		= $db_row['cfg_password'];
	$kannel_param['global_sender']	= $db_row['cfg_global_sender'];
	$kannel_param['bearerbox_host']	= $db_row['cfg_bearerbox_host'];
	$kannel_param['sendsms_port']	= $db_row['cfg_sendsms_port'];
	$kannel_param['playsms_web']	= $db_row['cfg_playsms_web'];
	$kannel_param['additional_param']	= $db_row['cfg_additional_param'];
	$kannel_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

if (! $kannel_param['additional_param']) {
	$kannel_param['additional_param'] = "smsc=default";
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['kannel'] = $kannel_param;

//$gateway_number = $kannel_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menu']['main_tab']['gateway'];
	$arr_menu[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_kannel&op=manage", _('Manage kannel'));
}
?>