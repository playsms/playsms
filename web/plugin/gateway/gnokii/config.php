<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayGnokii_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['gnokii']['name'] = 'gnokii';
	$plugin_config['gnokii']['path'] = $db_row['cfg_path'];
}

// smsc configuration
$plugin_config['gnokii']['_smsc_config_'] = array();

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_gnokii&op=manage", _('Manage gnokii'));
//}
