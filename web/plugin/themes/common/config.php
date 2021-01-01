<?php
defined('_SECURE_') or die('Forbidden');

// common action icons

$icon_config = array(
	'add' => "<span class='playsms-icon fas fa-plus' alt='" . _('Add') . "' title='" . _('Add') . "'></span>",
	'edit' => "<span class='playsms-icon fas fa-edit' alt='" . _('Edit') . "' title='" . _('Edit') . "'></span>",
	'delete' => "<span class='playsms-icon fas fa-trash' alt='" . _('Delete') . "' title='" . _('Delete') . "'></span>",
	'view' => "<span class='playsms-icon fas fa-eye' alt='" . _('View') . "' title='" . _('View') . "'></span>",
	'hide' => "<span class='playsms-icon fas fa-eye-slash' alt='" . _('Hide') . "' title='" . _('Hide') . "'></span>",
	'manage' => "<span class='playsms-icon fas fa-wrench' alt='" . _('Manage') . "' title='" . _('Manage') . "'></span>",
	'forward' => "<span class='playsms-icon fas fa-share' alt='" . _('Forward') . "' title='" . _('Forward') . "'></span>",
	'reply' => "<span class='playsms-icon fas fa-reply' alt='" . _('Reply') . "' title='" . _('Reply') . "'></span>",
	'resend' => "<span class='playsms-icon fas fa-share-square' alt='" . _('Resend') . "' title='" . _('Resend') . "'></span>",
	'user' => "<span class='playsms-icon fas fa-user' alt='" . _('User') . "' title='" . _('User') . "'></span>",
	'user_all' => "<span class='playsms-icon fas fa-globe' alt='" . _('All users') . "' title='" . _('All users') . "'></span>",
	'user_pref' => "<span class='playsms-icon fas fa-user' alt='" . _('User preference') . "' title='" . _('User preference') . "'></span>",
	'user_config' => "<span class='playsms-icon fas fa-wrench' alt='" . _('User configuration') . "' title='" . _('User configuration') . "'></span>",
	'user_delete' => "<span class='playsms-icon fas fa-trash' alt='" . _('Delete user') . "' title='" . _('Delete user') . "'></span>",
	'admin' => "<span class='playsms-icon fas fa-user-secret' alt='" . _('Administrator') . "' title='" . _('Administrator') . "'></span>",
	'import' => "<span class='playsms-icon fas fa-upload' alt='" . _('Import') . "' title='" . _('Import') . "'></span>",
	'export' => "<span class='playsms-icon fas fa-download' alt='" . _('Export') . "' title='" . _('Export') . "'></span>",
	'user_add' => "<span class='playsms-icon fas fa-user-plus' alt='" . _('Add') . "' title='" . _('Add') . "'></span>",
	'user_delete' => "<span class='playsms-icon fas fa-user-times' alt='" . _('Delete') . "' title='" . _('Delete') . "'></span>",
	'user_group' => "<span class='playsms-icon fas fa-users' alt='" . _('Group') . "' title='" . _('Group') . "'></span>",
	'group' => "<span class='playsms-icon fas fa-object-group' alt='" . _('Group') . "' title='" . _('Group') . "'></span>",
	'move' => "<span class='playsms-icon fa fas fa-arrows' alt='" . _('Move') . "' title='" . _('Move') . "'></span>",
	'go' => "<span class='playsms-icon fas fa-tasks' alt='" . _('Go') . "' title='" . _('Go') . "'></span>",
	'online' => "<span class='playsms-icon fas fa-check-circle' alt='" . _('Online') . "' title='" . _('Online') . "'></span>",
	'offline' => "<span class='playsms-icon fas fa-circle-o' alt='" . _('Offline') . "' title='" . _('Offline') . "'></span>",
	'ban' => "<span class='playsms-icon fas fa-thumbs-o-down' alt='" . _('Ban') . "' title='" . _('Ban') . "'></span>",
	'unban' => "<span class='playsms-icon fas fa-thumbs-o-up' alt='" . _('Unban') . "' title='" . _('Unban') . "'></span>",
	'idle' => "<span class='playsms-icon fas fa-clock-o' alt='" . _('Idle') . "' title='" . _('Idle') . "'></span>",
	'reduce' => "<span class='playsms-icon fas fa-minus' alt='" . _('Reduce') . "' title='" . _('Reduce') . "'></span>",
	'login_as' => "<span class='playsms-icon fas fa-sign-in-alt' alt='" . _('Login as') . "' title='" . _('Login as') . "'></span>",
	'login' => "<span class='playsms-icon fas fa-sign-in-alt' alt='" . _('Login') . "' title='" . _('Login') . "'></span>",
	'logout' => "<span class='playsms-icon fas fa-sign-out-alt' alt='" . _('Logout') . "' title='" . _('Logout') . "'></span>",
	'buy' => "<span class='playsms-icon fas fa-credit-card' alt='" . _('Buy') . "' title='" . _('Buy') . "'></span>",
	'credit' => "<span class='playsms-icon fas fa-credit-card' alt='" . _('Credit') . "' title='" . _('Credit') . "'></span>",
	'upload' => "<span class='playsms-icon fas fa-upload' alt='" . _('Upload') . "' title='" . _('Upload') . "'></span>",
	'download' => "<span class='playsms-icon fas fa-download' alt='" . _('Download') . "' title='" . _('Download') . "'></span>",
	'lock' => "<span class='playsms-icon fas fa-lock' alt='" . _('Lock') . "' title='" . _('Lock') . "'></span>",
	'unlock' => "<span class='playsms-icon fas fa-unlock' alt='" . _('Unlock') . "' title='" . _('Unlock') . "'></span>",
	'key' => "<span class='playsms-icon fas fa-key' alt='" . _('Key') . "' title='" . _('Key') . "'></span>",
	'keyword' => "<span class='playsms-icon fas fa-key' alt='" . _('Keyword') . "' title='" . _('Keyword') . "'></span>",
	'feature' => "<span class='playsms-icon fas fa-plug' alt='" . _('Feature') . "' title='" . _('Feature') . "'></span>",
	'sms' => "<span class='playsms-icon fas fa-comment-o' alt='" . _('SMS') . "' title='" . _('SMS') . "'></span>",
	'message' => "<span class='playsms-icon fas fa-comment-o' alt='" . _('Message') . "' title='" . _('Message') . "'></span>",
	'inbox' => "<span class='playsms-icon fas fa-inbox' alt='" . _('Inbox') . "' title='" . _('Inbox') . "'></span>",
	'info' => "<span class='playsms-icon fas fa-info-circle' alt='" . _('Info') . "' title='" . _('Info') . "'></span>",
	'action' => "<span class='playsms-icon fas fa-check' alt='" . _('Action') . "' title='" . _('Action') . "'></span>",
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
