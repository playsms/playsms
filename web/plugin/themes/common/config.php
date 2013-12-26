<?php
defined('_SECURE_') or die('Forbidden');

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// common action icons
$core_config['icon']['add']		= "<span class='playsms-icon glyphicon glyphicon-plus' alt='"._('Add')."' title='"._('Add')."'></span>";
$core_config['icon']['edit']		= "<span class='playsms-icon glyphicon glyphicon-cog' alt='"._('Edit')."' title='"._('Edit')."'></span>";
$core_config['icon']['delete']		= "<span class='playsms-icon glyphicon glyphicon-trash' alt='"._('Delete')."' title='"._('Delete')."'></span>";
$core_config['icon']['view']		= "<span class='playsms-icon glyphicon glyphicon-eye-open' alt='"._('View')."' title='"._('View')."'></span>";
$core_config['icon']['manage']		= "<span class='playsms-icon glyphicon glyphicon-folder-open' alt='"._('Manage')."' title='"._('Manage')."'></span>";
$core_config['icon']['forward']		= "<span class='playsms-icon glyphicon glyphicon-new-window' alt='"._('Forward')."' title='"._('Forward')."'></span>";
$core_config['icon']['reply']		= "<span class='playsms-icon glyphicon glyphicon-log-out' alt='"._('Reply')."' title='"._('Reply')."'></span>";
$core_config['icon']['resend']		= "<span class='playsms-icon glyphicon glyphicon-log-in' alt='"._('Resend')."' title='"._('Resend')."'></span>";
$core_config['icon']['user_pref'] 	= "<span class='playsms-icon glyphicon glyphicon-user' alt='"._('User preference')."' title='"._('User preference')."'></span>";
$core_config['icon']['user_config'] 	= "<span class='playsms-icon glyphicon glyphicon-wrench' alt='"._('User configuration')."' title='"._('User configuration')."'></span>";
$core_config['icon']['user_delete'] 	= "<span class='playsms-icon glyphicon glyphicon-trash' alt='"._('Delete user')."' title='"._('Delete user')."'></span>";
$core_config['icon']['admin']		= "<span class='playsms-icon glyphicon glyphicon-certificate' alt='"._('This user is an administrator')."' title='"._('This user is an administrator')."'></span>";
$core_config['icon']['export']		= "<span class='playsms-icon glyphicon glyphicon-export' alt='"._('Export')."' title='"._('Export')."'></span>";
$core_config['icon']['import']		= "<span class='playsms-icon glyphicon glyphicon-import' alt='"._('Import')."' title='"._('Import')."'></span>";
$core_config['icon']['group']		= "<span class='playsms-icon glyphicon glyphicon-briefcase' alt='"._('Group')."' title='"._('Group')."'></span>";
$core_config['icon']['move']		= "<span class='playsms-icon glyphicon glyphicon glyphicon-move' alt='"._('Move')."' title='"._('Move')."'></span>";
$core_config['icon']['go']		= "<span class='playsms-icon glyphicon glyphicon-cog' alt='"._('Go')."' title='"._('Go')."'></span>";
