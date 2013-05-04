<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayNexmo_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$nexmo_param['name']			= $db_row['cfg_name'];
	$nexmo_param['url']			= ( $db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://rest.nexmo.com/sms/json' );
	$nexmo_param['api_key']			= $db_row['cfg_api_key'];
	$nexmo_param['api_secret']		= $db_row['cfg_api_secret'];
	$nexmo_param['global_sender']		= $db_row['cfg_global_sender'];
	$nexmo_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['nexmo'] = $nexmo_param;

//$gateway_number = $nexmo_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_nexmo&op=manage", _('Manage nexmo'));
}
?>