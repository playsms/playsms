<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

// insert to left menu array
$menutab_feature = $core_config['menu']['main_tab']['feature'];
$arr_menu[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_list", _('Manage autoreply'));

?>