<?php
defined('_SECURE_') or die('Forbidden');

// sms_command bin path should be secured from unwanted access
$core_config['plugin']['sms_command']['bin']			= '/var/lib/playsms/sms_command';

// set to TRUE will allow regular users in playSMS to access this feature
// since 1.0 by default its FALSE (read: https://github.com/antonraharja/playSMS/pull/146)
$core_config['plugin']['sms_command']['allow_user_access']	= FALSE;

if (auth_isadmin() || $core_config['plugin']['sms_command']['allow_user_access']) {
	// insert to left menu array
	$menutab_feature = $core_config['menutab']['feature'];
	$menu_config[$menutab_feature][] = array("index.php?app=menu&inc=feature_sms_command&op=sms_command_list", _('Manage command'));
}
