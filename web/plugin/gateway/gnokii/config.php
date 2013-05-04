<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayGnokii_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$gnokii_param['name']	= $db_row['cfg_name'];
	$gnokii_param['path'] = $db_row['cfg_path'];
}

// save plugin's parameters or options in $core_config
$core_config['plugin']['gnokii'] = $gnokii_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$menu_config[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_gnokii&op=manage", _('Manage gnokii'));
}
?>