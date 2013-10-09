<?php

defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$menu_config[$menutab][] = array("index.php?app=menu&inc=tools_pluginmanager&op=pluginmanager_list", _('Manage plugin'));
}
?>