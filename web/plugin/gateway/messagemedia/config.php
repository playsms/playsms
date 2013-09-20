<?php
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayMessagemedia_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
    $messagemedia_param['name']			= $db_row['cfg_name'];
    $messagemedia_param['api_id']		= $db_row['cfg_api_id'];
    $messagemedia_param['username']		= $db_row['cfg_username'];
    $messagemedia_param['password']		= $db_row['cfg_password'];
    $messagemedia_param['delay']		= $db_row['cfg_delay'];
    $messagemedia_param['send_url']		= $db_row['cfg_send_url'];
    $messagemedia_param['incoming_path']	= $db_row['cfg_incoming_path'];
    $messagemedia_param['additional_param']	= $db_row['cfg_additional_param'];
    $messagemedia_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
}

if (! $messagemedia_param['additional_param']) {
    $messagemedia_param['additional_param'] = "deliv_ack=1&callback=3";
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['messagemedia'] = $messagemedia_param;

//$gateway_number = $messagemedia_param['sender'];

// insert to left menu array
if (isadmin()) {
    $menutab_gateway = $core_config['menutab']['gateway'];
    $menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_messagemedia&op=manage", _('Manage messagemedia'));
}
?>
