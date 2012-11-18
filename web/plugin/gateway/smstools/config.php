<?php
defined('_SECURE_') or die('Forbidden');

$smstools_param['name'] = "smstools";
$smstools_param['spool_dir'] = "/var/spool/sms";
$smstools_param['spool_bak'] = "/var/spool/smsbackup";
$smstools_param['ready'] = FALSE;

$db_query = "SELECT ready FROM "._DB_PREF_."_gatewaySMSTools_config";
$db_result = dba_query($db_query);

if ($db_row = dba_fetch_array($db_result))
	$smstools_param['ready'] = $db_row['ready'];
	
// save plugin's parameters or options in $core_config
$core_config['plugin']['smstools'] = $smstools_param;

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$arr_menu[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_smstools&op=manage", _('Manage smstools'));
}
?>
