<?php
defined('_SECURE_') or die('Forbidden');

if (auth_isvalid()) {
	$menutab = $core_config['menutab']['features'];
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_sms_sync&op=sms_sync_list",
		_('Manage sync')
	);
}
