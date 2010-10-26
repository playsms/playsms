<?php
$gammu_param['name'] = "gammu";
$gammu_param['path'] = "/var/spool/gammu";

// save plugin's parameters or options in $core_config
$core_config['plugin']['gammu'] = $gammu_param;

// insert to left menu array
if (isadmin()) {
    $arr_menu['Gateway'][] = array("index.php?app=menu&inc=gateway_gammu&op=manage", _('Manage gammu'));
}
?>