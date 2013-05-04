<?php
defined('_SECURE_') or die('Forbidden');

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
	// Handle DLR options in Kannel - emmanuel
	$kannel_param['dlr']            = $db_row['cfg_dlr'];
	// end of Handle DLR options in Kannel - emmanuel
	$kannel_param['additional_param']	= $db_row['cfg_additional_param'];
	$kannel_param['datetime_timezone']	= $db_row['cfg_datetime_timezone'];
        //fixme edward Adding New Parameter HTTP Kannel Admin
        $kannel_param['admin_url']              = $db_row['cfg_admin_url'];
        $kannel_param['admin_password']              = $db_row['cfg_admin_password'];
        $kannel_param['admin_port']             = $db_row['cfg_admin_port'];
        //end of fixme edward Adding New Parameter HTTP Kannel Admin
}

// default path for kannel.conf, please edit the path if different from default
$kannel_param['kannelconf'] = '/etc/kannel/kannel.conf';

if (! $kannel_param['additional_param']) {
	$kannel_param['additional_param'] = "smsc=default";
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['kannel'] = $kannel_param;

//$gateway_number = $kannel_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_kannel&op=manage", _('Manage kannel'));
}

// Test for DLR checkbox
/* DLR Kannel value
           1: Delivered to phone
           2: Non-Delivered to Phone
           4: Queued on SMSC
           8: Delivered to SMSC
           16: Non-Delivered to SMSC
*/

if( $kannel_param['dlr'] == 0 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 1 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 2 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 3 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 4 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 5 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 6 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 7 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 8 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 9 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 10 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 11 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 12 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 13 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 14 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 15 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "";

} else if($kannel_param['dlr'] == 16 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 17 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 18 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 19 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 20 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 21 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 22 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 23 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 24 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 25 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 26 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 27 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 28 ) {
  $checked[0] = "";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 29 ) {
  $checked[0] = "checked";
  $checked[1] = "";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 30 ) {
  $checked[0] = "";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "checked";

} else if($kannel_param['dlr'] == 31 ) {
  $checked[0] = "checked";
  $checked[1] = "checked";
  $checked[2] = "checked";
  $checked[3] = "checked";
  $checked[4] = "checked";

} 
?>
