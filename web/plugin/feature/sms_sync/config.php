<?php
defined('_SECURE_') or die('Forbidden');

if (valid()) {
	$menutab = $core_config['menutab']['feature'];
	$menu_config[$menutab][] = array("index.php?app=menu&inc=feature_sms_sync&op=sms_sync_list", _('Manage sync'));
}
?>