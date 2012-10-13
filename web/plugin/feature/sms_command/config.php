<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_feature = $core_config['menu']['feature'];
$arr_menu[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_command&op=sms_command_list", _('Manage command'));

$plugin_config['feature']['sms_command']['bin']	= $apps_path['base'].'/bin';

?>