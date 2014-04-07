<?php
defined('_SECURE_') or die('Forbidden');

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=tools_report&route=admin",
		_('All reports') ,
		2
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=tools_report&route=online",
		_('Whose online')
	);
}

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=tools_report&route=user",
	_('My report')
);
