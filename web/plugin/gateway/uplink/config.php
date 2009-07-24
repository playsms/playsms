<?
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayUplink_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result))
{
    $uplink_param[name]			= $db_row[cfg_name];
    $uplink_param[master]		= $db_row[cfg_master];
    $uplink_param[username]		= $db_row[cfg_username];
    $uplink_param[password]		= $db_row[cfg_password];
    $uplink_param[global_sender]	= $db_row[cfg_global_sender];
    $uplink_param[path]     		= $db_row[cfg_incoming_path];
}

$gateway_number = $uplink_param['global_sender'];

// insert to left menu array
if (isadmin())
{
    $arr_menu["Gateway"][] = array("menu.php?inc=gateway_uplink&op=manage", "Manage uplink");
}
?>