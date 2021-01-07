<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
if (auth_isadmin()) {
	$menutab = $core_config['menutab']['settings'];
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_mailsms&op=mailsms',
		_('Manage email to SMS') 
	);
}

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_mailsms&route=mailsms_user&op=mailsms_user',
	_('My email to SMS') 
);
