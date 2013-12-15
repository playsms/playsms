<?php

defined('_SECURE_') or die('Forbidden');

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$menu_config[$menutab][] = array("index.php?app=menu&inc=tools_gatewaymanager&op=gatewaymanager_list", _('Manage gateway'));
}
?>