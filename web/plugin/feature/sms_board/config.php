<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_feature = $core_config['menu']['feature'];
$arr_menu[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_board&op=sms_board_list", _('Manage board'));

?>