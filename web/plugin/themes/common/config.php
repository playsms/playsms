<?php
defined('_SECURE_') or die('Forbidden');

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $core_config['apps_path']['plug'] . '/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// common action icons

$icon_config = array(
	'add' => "<span class='playsms-icon glyphicon glyphicon-plus' alt='" . _('Add') . "' title='" . _('Add') . "'></span>",
	'edit' => "<span class='playsms-icon glyphicon glyphicon-cog' alt='" . _('Edit') . "' title='" . _('Edit') . "'></span>",
	'delete' => "<span class='playsms-icon glyphicon glyphicon-trash' alt='" . _('Delete') . "' title='" . _('Delete') . "'></span>",
	'view' => "<span class='playsms-icon glyphicon glyphicon-eye-open' alt='" . _('View') . "' title='" . _('View') . "'></span>",
	'manage' => "<span class='playsms-icon glyphicon glyphicon-folder-open' alt='" . _('Manage') . "' title='" . _('Manage') . "'></span>",
	'forward' => "<span class='playsms-icon glyphicon glyphicon-new-window' alt='" . _('Forward') . "' title='" . _('Forward') . "'></span>",
	'reply' => "<span class='playsms-icon glyphicon glyphicon-log-out' alt='" . _('Reply') . "' title='" . _('Reply') . "'></span>",
	'resend' => "<span class='playsms-icon glyphicon glyphicon-log-in' alt='" . _('Resend') . "' title='" . _('Resend') . "'></span>",
	'user_pref' => "<span class='playsms-icon glyphicon glyphicon-user' alt='" . _('User preference') . "' title='" . _('User preference') . "'></span>",
	'user_config' => "<span class='playsms-icon glyphicon glyphicon-wrench' alt='" . _('User configuration') . "' title='" . _('User configuration') . "'></span>",
	'user_delete' => "<span class='playsms-icon glyphicon glyphicon-trash' alt='" . _('Delete user') . "' title='" . _('Delete user') . "'></span>",
	'admin' => "<span class='playsms-icon glyphicon glyphicon-certificate' alt='" . _('Administrator') . "' title='" . _('Administrator') . "'></span>",
	'export' => "<span class='playsms-icon glyphicon glyphicon-export' alt='" . _('Export') . "' title='" . _('Export') . "'></span>",
	'import' => "<span class='playsms-icon glyphicon glyphicon-import' alt='" . _('Import') . "' title='" . _('Import') . "'></span>",
	'group' => "<span class='playsms-icon glyphicon glyphicon-briefcase' alt='" . _('Group') . "' title='" . _('Group') . "'></span>",
	'move' => "<span class='playsms-icon glyphicon glyphicon glyphicon-move' alt='" . _('Move') . "' title='" . _('Move') . "'></span>",
	'go' => "<span class='playsms-icon glyphicon glyphicon-cog' alt='" . _('Go') . "' title='" . _('Go') . "'></span>",
	'online' => "<span class='playsms-icon glyphicon glyphicon-ok-circle' alt='" . _('Online') . "' title='" . _('Online') . "'></span>",
	'offline' => "<span class='playsms-icon glyphicon glyphicon-remove-circle' alt='" . _('Offline') . "' title='" . _('Offline') . "'></span>",
	'idle' => "<span class='playsms-icon glyphicon glyphicon-time' alt='" . _('Idle') . "' title='" . _('Idle') . "'></span>",
	'ban' => "<span class='playsms-icon glyphicon glyphicon-thumbs-down' alt='" . _('Ban') . "' title='" . _('Ban') . "'></span>",
	'unban' => "<span class='playsms-icon glyphicon glyphicon-thumbs-up' alt='" . _('Unban') . "' title='" . _('Unban') . "'></span>",
	'logout' => "<span class='playsms-icon glyphicon glyphicon-off' alt='" . _('Logout') . "' title='" . _('Logout') . "'></span>",
	'reduce' => "<span class='playsms-icon glyphicon glyphicon-minus' alt='" . _('Reduce') . "' title='" . _('Reduce') . "'></span>",
	'buy' => "<span class='playsms-icon glyphicon glyphicon-credit-card' alt='" . _('Buy') . "' title='" . _('Buy') . "'></span>",
	'login_as' => "<span class='playsms-icon glyphicon glyphicon-adjust' alt='" . _('Login as') . "' title='" . _('Login as') . "'></span>",
);

// menu structure

// menu tabs
$core_config['menutab'] = array(
	'home' => _('Home') ,
	'my_account' => _('My account') ,
	'reports' => _('Reports') ,
	'features' => _('Features') ,
	'settings' => _('Settings') ,
);

// my account tab
$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab] = array(
	array(
		'index.php?app=main&inc=core_sendsms&op=sendsms',
		_('Compose message') ,
		1
	) ,
);
// divider
$menu_config[$menutab][] = array(
	'#',
	'-',
	99
);
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=core_user&route=user_config&op=user_config',
	_('User configuration') ,
	99
);
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=core_user&route=user_pref&op=user_pref',
	_('Preferences') ,
	99
);

// settings tab
if (auth_isadmin()) {
	
	// admin settings
	$menutab = $core_config['menutab']['settings'];

	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list',
		_('Manage account') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_acl&op=acl_list',
		_('Manage ACL') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list',
		_('Manage subuser') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_sender_id&op=sender_id_list',
		_('Manage sender ID') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_main_config&op=main_config',
		_('Main configuration') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_gateway&op=gateway_list',
		_('Manage gateway and SMSC') ,
		3
	);
} else if ($user_config['status'] == 3) {
	
	// user menus
	$menutab = $core_config['menutab']['settings'];
	
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list',
		_('Manage subuser') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_sender_id&op=sender_id_list',
		_('Manage sender ID') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_site&op=site_config',
		_('Manage site') ,
		3
	);
} else if ($user_config['status'] == 4) {

	// subuser menus
	$menutab = $core_config['menutab']['settings'];
	
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=core_sender_id&op=sender_id_list',
		_('Manage sender ID') ,
		3
	);
	
}
