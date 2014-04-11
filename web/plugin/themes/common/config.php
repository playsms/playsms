<?php
defined('_SECURE_') or die('Forbidden');

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $core_config['apps_path']['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}


// common action icons

$icon_config['add']		= "<span class='playsms-icon glyphicon glyphicon-plus' alt='"._('Add')."' title='"._('Add')."'></span>";
$icon_config['edit']		= "<span class='playsms-icon glyphicon glyphicon-cog' alt='"._('Edit')."' title='"._('Edit')."'></span>";
$icon_config['delete']		= "<span class='playsms-icon glyphicon glyphicon-trash' alt='"._('Delete')."' title='"._('Delete')."'></span>";
$icon_config['view']		= "<span class='playsms-icon glyphicon glyphicon-eye-open' alt='"._('View')."' title='"._('View')."'></span>";
$icon_config['manage']		= "<span class='playsms-icon glyphicon glyphicon-folder-open' alt='"._('Manage')."' title='"._('Manage')."'></span>";
$icon_config['forward']		= "<span class='playsms-icon glyphicon glyphicon-new-window' alt='"._('Forward')."' title='"._('Forward')."'></span>";
$icon_config['reply']		= "<span class='playsms-icon glyphicon glyphicon-log-out' alt='"._('Reply')."' title='"._('Reply')."'></span>";
$icon_config['resend']		= "<span class='playsms-icon glyphicon glyphicon-log-in' alt='"._('Resend')."' title='"._('Resend')."'></span>";
$icon_config['user_pref'] 	= "<span class='playsms-icon glyphicon glyphicon-user' alt='"._('User preference')."' title='"._('User preference')."'></span>";
$icon_config['user_config'] 	= "<span class='playsms-icon glyphicon glyphicon-wrench' alt='"._('User configuration')."' title='"._('User configuration')."'></span>";
$icon_config['user_delete'] 	= "<span class='playsms-icon glyphicon glyphicon-trash' alt='"._('Delete user')."' title='"._('Delete user')."'></span>";
$icon_config['admin']		= "<span class='playsms-icon glyphicon glyphicon-certificate' alt='"._('Administrator')."' title='"._('Administrator')."'></span>";
$icon_config['export']		= "<span class='playsms-icon glyphicon glyphicon-export' alt='"._('Export')."' title='"._('Export')."'></span>";
$icon_config['import']		= "<span class='playsms-icon glyphicon glyphicon-import' alt='"._('Import')."' title='"._('Import')."'></span>";
$icon_config['group']		= "<span class='playsms-icon glyphicon glyphicon-briefcase' alt='"._('Group')."' title='"._('Group')."'></span>";
$icon_config['move']		= "<span class='playsms-icon glyphicon glyphicon glyphicon-move' alt='"._('Move')."' title='"._('Move')."'></span>";
$icon_config['go']		= "<span class='playsms-icon glyphicon glyphicon-cog' alt='"._('Go')."' title='"._('Go')."'></span>";
$icon_config['online']		= "<span class='playsms-icon glyphicon glyphicon-ok-circle' alt='"._('Online')."' title='"._('Online')."'></span>";
$icon_config['offline']		= "<span class='playsms-icon glyphicon glyphicon-remove-circle' alt='"._('Offline')."' title='"._('Offline')."'></span>";
$icon_config['idle']		= "<span class='playsms-icon glyphicon glyphicon-time' alt='"._('Idle')."' title='"._('Idle')."'></span>";
$icon_config['ban']		= "<span class='playsms-icon glyphicon glyphicon-thumbs-down' alt='"._('Ban')."' title='"._('Ban')."'></span>";
$icon_config['unban']		= "<span class='playsms-icon glyphicon glyphicon-thumbs-up' alt='"._('Unban')."' title='"._('Unban')."'></span>";


// menu structure

// menu tabs
$core_config['menutab'] = array(
	'home' => _('Home'),
	'my_account' => _('My Account'),
	'tools' => _('Tools'),
	'feature' => _('Feature'),
	'administration' => _('Administration'),
);

// my account tab
$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab] = array(
	array('index.php?app=main&inc=send_sms&op=send_sms', _('Send message'), 1),
	array('index.php?app=main&inc=user_inbox&op=user_inbox', _('Inbox'), 1),
	array('index.php?app=main&inc=user_incoming&op=user_incoming', _('Incoming messages'), 1),
	array('index.php?app=main&inc=user_outgoing&op=user_outgoing', _('Outgoing messages'), 1),
);

// only if logged in user is an admin then load administration tab
if (auth_isadmin()) {
	// administrator menus
	$menutab = $core_config['menutab']['administration'];
	$menu_config[$menutab] = array(
		array('index.php?app=main&inc=all_inbox&op=all_inbox', _('All inbox'), 1),
		array('index.php?app=main&inc=all_incoming&op=all_incoming', _('All incoming messages'), 1),
		array('index.php?app=main&inc=all_outgoing&op=all_outgoing', _('All outgoing messages'), 1),
		array('index.php?app=main&inc=sandbox&op=sandbox', _('Sandbox'), 1),
		array('index.php?app=main&inc=user_mgmnt&op=user_list', _('Manage user'), 2),
		array('index.php?app=main&inc=main_config&op=main_config', _('Main configuration'), 2),
	);
}
