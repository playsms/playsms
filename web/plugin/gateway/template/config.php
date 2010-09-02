<?php
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayTemplate_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result))
{
    $template_param['name'] = $db_row['cfg_name'];
    $template_param['path'] = $db_row['cfg_path'];
    $template_param['global_sender'] = $db_row['cfg_global_sender'];
}

$gateway_number = $template_param['global_sender'];

// insert to left menu array
if (isadmin())
{
    $arr_menu['Gateway'][] = array("menu.php?inc=gateway_template&op=manage", "Manage template");
}
?>