<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTemplate_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$template_param['name'] = $db_row['cfg_name'];
	$template_param['path'] = $db_row['cfg_path'];
	$template_param['module_sender'] = $db_row['cfg_module_sender'];
}

// smsc configuration
$plugin_config['template']['_smsc_config_'] = array();

//$gateway_number = $template_param['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_template&op=manage", _('Manage template'));
//}
