<?php
defined('_SECURE_') or die('Forbidden');

// get kannel config from registry
$data = registry_search(1, 'gateway', 'kannel');
$plugin_config['kannel'] = $data['gateway']['kannel'];
$plugin_config['kannel']['name'] = 'kannel';
$plugin_config['kannel']['playsms_web'] = ($plugin_config['kannel']['playsms_web'] ? $plugin_config['kannel']['playsms_web'] : _HTTP_PATH_BASE_);
$plugin_config['kannel']['bearerbox_host'] = ($plugin_config['kannel']['bearerbox_host'] ? $plugin_config['kannel']['bearerbox_host'] : 'localhost');
$plugin_config['kannel']['sendsms_host'] = ($plugin_config['kannel']['sendsms_host'] ? $plugin_config['kannel']['sendsms_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['sendsms_port'] = ($plugin_config['kannel']['sendsms_port'] ? $plugin_config['kannel']['sendsms_port'] : '13131');
$plugin_config['kannel']['admin_host'] = ($plugin_config['kannel']['admin_host'] ? $plugin_config['kannel']['admin_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['admin_port'] = ($plugin_config['kannel']['admin_port'] ? $plugin_config['kannel']['admin_port'] : '13000');
$plugin_config['kannel']['local_time'] = ($plugin_config['kannel']['local_time'] ? 1 : 0);

// Test for DLR checkbox
/*
 * DLR Kannel value 1: Delivered to phone 2: Non-Delivered to Phone 4: Queued on SMSC 8: Delivered to SMSC 16: Non-Delivered to SMSC
 */

if ($plugin_config['kannel']['dlr'] == 0) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 1) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 2) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 3) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 4) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 5) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 6) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 7) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 8) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 9) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 10) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 11) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 12) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 13) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 14) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 15) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "";
} else if ($plugin_config['kannel']['dlr'] == 16) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 17) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 18) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 19) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 20) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 21) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 22) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 23) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 24) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 25) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 26) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 27) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 28) {
	$checked[0] = "";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 29) {
	$checked[0] = "checked";
	$checked[1] = "";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 30) {
	$checked[0] = "";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "checked";
} else if ($plugin_config['kannel']['dlr'] == 31) {
	$checked[0] = "checked";
	$checked[1] = "checked";
	$checked[2] = "checked";
	$checked[3] = "checked";
	$checked[4] = "checked";
}

// smsc configuration
$plugin_config['kannel']['_smsc_config_'] = array(
	'additional_param' => _('Additional URL parameter'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);

