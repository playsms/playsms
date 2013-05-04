<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayMsgtoolbox_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$msgtoolbox_param['name']				= $db_row['cfg_name'];
	$msgtoolbox_param['url']				= $db_row['cfg_url'];
	$msgtoolbox_param['route']				= $db_row['cfg_route'];
	$msgtoolbox_param['username']			= $db_row['cfg_username'];
	$msgtoolbox_param['password']			= $db_row['cfg_password'];
	$msgtoolbox_param['global_sender']		= $db_row['cfg_global_sender'];
	$msgtoolbox_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['msgtoolbox'] = $msgtoolbox_param;

//$gateway_number = $msgtoolbox_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_msgtoolbox&op=manage", _('Manage msgtoolbox'));
}
?>