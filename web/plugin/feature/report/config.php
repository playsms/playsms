<?php
defined('_SECURE_') or die('Forbidden');

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['reports'];
	
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_inbox&op=all_inbox',
		_('All inbox') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_incoming&op=all_incoming',
		_('All incoming messages') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_outgoing&op=all_outgoing',
		_('All outgoing messages') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=sandbox&op=sandbox',
		_('Sandbox') ,
		3
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=admin",
		_('Report all users') ,
		10
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=online",
		_('Report whose online') ,
		10
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=banned",
		_('Report banned users') ,
		10
	);
}

$menutab = $core_config['menutab']['my_account'];

$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox',
	_('Inbox') ,
	1
);
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_incoming&op=user_incoming',
	_('Incoming messages') ,
	1
);
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing',
	_('Outgoing messages') ,
	1
);

$menutab = $core_config['menutab']['reports'];

$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_report&route=user",
	_('My report')
);
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_report&route=credit&op=credit_list",
	_('My credit transactions')
);
