<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_feature = $core_config['menutab']['feature'];
$menu_config[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list", _('Manage poll'));

?>