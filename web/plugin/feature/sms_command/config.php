<?php

// insert to left menu array
$arr_menu['Feature'][] = array("index.php?app=menu&inc=feature_sms_command&op=sms_command_list", _('Manage command'));

$plugin_config['feature']['sms_command']['bin']	= $apps_path['base'].'/bin';

?>