<?php
defined('_SECURE_') or die('Forbidden');

$gammu_param['name'] = "gammu";
$gammu_param['path'] = "/var/spool/gammu";
$gammu_param['ready'] = FALSE;

$db_query = "SELECT ready FROM "._DB_PREF_."_gatewayGammu_config";
$db_result = dba_query($db_query);

if ($db_row = dba_fetch_array($db_result))
	$gammu_param['ready'] = $db_row['ready'];
	
// save plugin's parameters or options in $core_config
$core_config['plugin']['gammu'] = $gammu_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$arr_menu[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_gammu&op=manage", _('Manage gammu'));
}
?>
