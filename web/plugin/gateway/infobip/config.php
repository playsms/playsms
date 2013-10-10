<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayInfobip_config";
$db_result = dba_query ( $db_query );
if ($db_row = dba_fetch_array ( $db_result )) {
	$infobip_param ['name'] = $db_row ['cfg_name'];
	$infobip_param ['username'] = $db_row ['cfg_username'];
	$infobip_param ['password'] = $db_row ['cfg_password'];
	$infobip_param ['global_sender'] = $db_row ['cfg_sender'];
	$infobip_param ['send_url'] = ( $db_row ['cfg_send_url'] ? $db_row ['cfg_send_url'] : 'http://api.infobip.com/api/v3' );
	$infobip_param ['additional_param'] = $db_row ['cfg_additional_param'];
	$infobip_param ['datetime_timezone'] = $db_row ['cfg_datetime_timezone'];
	//$infobip_param ['dlr_nopush'] = $db_row ['cfg_dlr_nopush'];
	$infobip_param ['dlr_nopush'] = 1;
}

// save plugin's parameters or options in $core_config
$core_config ['plugin'] ['infobip'] = $infobip_param;

// $gateway_number = $infobip_param['sender'];

// insert to left menu array
if (isadmin ()) {
	$menutab_gateway = $core_config ['menutab'] ['gateway'];
	$menu_config [$menutab_gateway] [] = array (
			"index.php?app=menu&inc=gateway_infobip&op=manage",
			_ ( 'Manage infobip' ) 
	);
}
?>