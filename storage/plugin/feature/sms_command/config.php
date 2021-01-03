<?php
defined('_SECURE_') or die('Forbidden');

// sms_command bin path should be secured from unwanted access
$plugin_config['sms_command']['bin']			= '/var/lib/playsms/sms_command';

// set to TRUE will allow regular users in playSMS to access this feature
// since 1.0 by default its FALSE (read: https://github.com/antonraharja/playSMS/pull/146)
$plugin_config['sms_command']['allow_user_access']	= FALSE;

if (auth_isadmin() || $plugin_config['sms_command']['allow_user_access']) {
	// insert to left menu array
	$menutab = $core_config['menutab']['features'];
	$menu_config[$menutab][] = array("index.php?app=main&inc=feature_sms_command&op=sms_command_list", _('Manage command'));
}
