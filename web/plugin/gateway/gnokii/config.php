<?php
$db_query = "SELECT * FROM "._DB_PREF_."_gatewayGnokii_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
    $gnokii_param['name']	= $db_row['cfg_name'];
    $gnokii_param['path'] = $db_row['cfg_path'];
}

// insert to left menu array
if (isadmin()) {
    $arr_menu['Gateway'][] = array("index.php?app=menu&inc=gateway_gnokii&op=manage", _('Manage gnokii'));
}
?>