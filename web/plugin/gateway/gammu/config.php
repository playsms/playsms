<?php
$gammu_param['name'] = "gammu";
$gammu_param['path'] = "/var/spool/gammu";

// insert to left menu array
if (isadmin()) {
    $arr_menu['Gateway'][] = array("index.php?app=menu&inc=gateway_gammu&op=manage", _('Manage gammu'));
}
?>