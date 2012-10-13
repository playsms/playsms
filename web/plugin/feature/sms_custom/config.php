<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_feature = $core_config['menu']['feature'];
$arr_menu[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_custom&op=sms_custom_list", _('Manage custom'));

?>