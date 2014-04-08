<?php
defined('_SECURE_') or die('Forbidden');

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $core_config['apps_path']['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// default loaded page/plugin
//$core_config['main']['default_inc']	= 'tools_report';
//$core_config['main']['default_op']	= 'report_user';
$core_config['main']['default_inc']	= 'page_welcome';
$core_config['main']['default_op']	= 'page_welcome';

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
