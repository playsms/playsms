<?php
$smstools_param['name'] = "smstools";
$smstools_param['path'] = "/var/spool/sms";

// save plugin's parameters or options in $core_config
$core_config['plugin']['smstools'] = $smstools_param;

// insert to left menu array
if (isadmin()) {
    $arr_menu['Gateway'][] = array("index.php?app=menu&inc=gateway_smstools&op=manage", _('Manage smstools'));
}
?>