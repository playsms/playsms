<?php
defined('_SECURE_') or die('Forbidden');

// this file loaded before plugins

$inc = q_sanitize($_REQUEST['inc']);
$op = q_sanitize($_REQUEST['op']);
$page = q_sanitize($_REQUEST['page']);
$nav = q_sanitize($_REQUEST['nav']);

// load user's data from user's DB table
if (valid()) {
	$username = $_SESSION['username'];
	$core_config['user'] = user_getdatabyusername($username);;
	$uid = $core_config['user']['uid'];
	$sender = core_sanitize_sender($core_config['user']['sender']);
	$footer = $core_config['user']['footer'];
	$mobile = $core_config['user']['mobile'];
	$email = $core_config['user']['email'];
	$name = $core_config['user']['name'];
	$status = $core_config['user']['status'];
	$userstatus = ( $status == 2 ? _('Administrator') : _('Normal User') );
	$core_config['user']['opt']['sms_footer_length'] = ( strlen($footer) > 0 ? strlen($footer) + 1 : 0 );
	$core_config['user']['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['gravatar'] = "https://www.gravatar.com/avatar/".md5(strtolower(trim($core_config['user']['email'])));
}

// reserved important keywords
$reserved_keywords = array ("BC");
$core_config['reserved_keywords'] = $reserved_keywords;

// action icons
$core_config['icon']['edit'] 		= "<img class=icon id=icon_edit src=\"".$http_path['themes']."/".$themes_module."/images/action_edit.png\" alt=\""._('Edit')."\" title=\""._('Edit')."\">";
$core_config['icon']['delete'] 		= "<img class=icon id=icon_delete src=\"".$http_path['themes']."/".$themes_module."/images/action_delete.png\" alt=\""._('Delete')."\" title=\""._('Delete')."\">";
$core_config['icon']['view'] 		= "<img class=icon id=icon_view src=\"".$http_path['themes']."/".$themes_module."/images/action_view.png\" alt=\""._('View')."\" title=\""._('View')."\">";
$core_config['icon']['manage'] 		= "<img class=icon id=icon_manage src=\"".$http_path['themes']."/".$themes_module."/images/action_manage.png\" alt=\""._('Manage')."\" title=\""._('Manage')."\">";
$core_config['icon']['reply'] 		= "<img class=icon id=icon_reply src=\"".$http_path['themes']."/".$themes_module."/images/action_reply.png\" alt=\""._('Reply')."\" title=\""._('Reply')."\">";
$core_config['icon']['forward'] 	= "<img class=icon id=icon_forward src=\"".$http_path['themes']."/".$themes_module."/images/action_forward.png\" alt=\""._('Forward')."\" title=\""._('Forward')."\">";
$core_config['icon']['resend'] 		= "<img class=icon id=icon_resend src=\"".$http_path['themes']."/".$themes_module."/images/action_resend.png\" alt=\""._('Resend')."\" title=\""._('Resend')."\">";
$core_config['icon']['sendsms'] 	= "<img class=icon id=icon_sendsms src=\"".$http_path['themes']."/".$themes_module."/images/action_sendsms.png\" alt=\""._('Send SMS')."\" title=\""._('Send SMS')."\">";
$core_config['icon']['export'] 		= "<img class=icon id=icon_export src=\"".$http_path['themes']."/".$themes_module."/images/action_export.png\" alt=\""._('Export')."\" title=\""._('Export')."\">";
$core_config['icon']['import'] 		= "<img class=icon id=icon_import src=\"".$http_path['themes']."/".$themes_module."/images/action_import.png\" alt=\""._('Import')."\" title=\""._('Import')."\">";
$core_config['icon']['publish'] 	= "<img class=icon id=icon_publish src=\"".$http_path['themes']."/".$themes_module."/images/action_publish.png\" alt=\""._('Publish')."\" title=\""._('Publish')."\">";
$core_config['icon']['unpublish'] 	= "<img class=icon id=icon_unpublish src=\"".$http_path['themes']."/".$themes_module."/images/action_unpublish.png\" alt=\""._('Unpublish')."\" title=\""._('Unpublish')."\">";
$core_config['icon']['user_pref'] 	= "<img class=icon id=icon_user_pref src=\"".$http_path['themes']."/".$themes_module."/images/action_user_pref.png\" alt=\""._('User preference')."\" title=\""._('User preference')."\">";
$core_config['icon']['user_config'] 	= "<img class=icon id=icon_user_config src=\"".$http_path['themes']."/".$themes_module."/images/action_user_config.png\" alt=\""._('User configuration')."\" title=\""._('User configuration')."\">";
$core_config['icon']['user_delete'] 	= "<img class=icon id=icon_user_delete src=\"".$http_path['themes']."/".$themes_module."/images/action_user_delete.png\" alt=\""._('Delete user')."\" title=\""._('Delete user')."\">";
$core_config['icon']['phonebook'] 	= "<img class=icon id=icon_phonebook src=\"".$http_path['themes']."/".$themes_module."/images/action_phonebook.png\" alt=\""._('Phonebook')."\" title=\""._('Phonebook')."\">";
$core_config['icon']['calendar'] 	= "<img class=icon id=icon_calendar src=\"".$http_path['themes']."/".$themes_module."/images/action_calendar.png\" alt=\""._('Pick date & time')."\" title=\""._('Pick date & time')."\">";

// menus
$core_config['menutab']['home'] = _('Home');
$core_config['menutab']['my_account'] = _('My Account');
$core_config['menutab']['tools'] = _('Tools');
$core_config['menutab']['feature'] = _('Feature');
$core_config['menutab']['gateway'] = _('Gateway');
$core_config['menutab']['administration'] = _('Administration');

$menutab_my_account = $core_config['menutab']['my_account'];
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=1", _('Send SMS'));
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_inbox&op=user_inbox", _('Inbox'));
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_incoming&op=user_incoming", _('Incoming SMS'));
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_outgoing&op=user_outgoing", _('Outgoing SMS'));
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_config&op=user_config", _('User configuration'));

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();
//print_r($menu_config); die();

?>