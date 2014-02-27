<?php
defined('_SECURE_') or die('Forbidden');

// get kannel config from registry
$data = registry_search(1, 'gateway', 'kannel');
$kannel_param = $data['gateway']['kannel'];
$kannel_param['name'] = 'kannel';
$kannel_param['playsms_web'] = ( $kannel_param['playsms_web'] ? $kannel_param['playsms_web'] : _HTTP_PATH_BASE_ );
$kannel_param['bearerbox_host'] = ( $kannel_param['bearerbox_host'] ? $kannel_param['bearerbox_host'] : 'localhost' );
$kannel_param['sendsms_host'] = ( $kannel_param['sendsms_host'] ? $kannel_param['sendsms_host'] : $kannel_param['bearerbox_host'] );
$kannel_param['sendsms_port'] = ( $kannel_param['sendsms_port'] ? $kannel_param['sendsms_port'] : '13131' );
$kannel_param['admin_host'] = ( $kannel_param['admin_host'] ? $kannel_param['admin_host'] : $kannel_param['bearerbox_host'] );
$kannel_param['admin_port'] = ( $kannel_param['admin_port'] ? $kannel_param['admin_port'] : '13000' );
$kannel_param['local_time'] = ( $kannel_param['local_time'] ? 1 : 0 );

// save plugin's parameters or options in $core_config
$core_config['plugin']['kannel'] = $kannel_param;

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
