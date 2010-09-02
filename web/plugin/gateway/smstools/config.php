<?php
$smstools_param['name'] = "smstools";
$smstools_param['path'] = "/var/spool/sms";

// insert to left menu array
if (isadmin())
{
    $arr_menu['Gateway'][] = array("menu.php?inc=gateway_smstools&op=manage", "Manage smstools");
}
?>