<?php
defined('_SECURE_') or die('Forbidden');

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=tools_report&route=admin",
		_('Report all users') ,
		2
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=tools_report&route=online",
		_('Report whose online'),
		2
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=tools_report&route=banned",
		_('Report banned users'),
		2
	);
}

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=tools_report&route=user",
	_('My report')
);
