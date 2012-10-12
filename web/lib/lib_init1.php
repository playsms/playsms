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
	$sender = $core_config['user']['sender'];
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

}

// reserved important keywords
$reserved_keywords = array ("PV","BC");
$core_config['reserved_keywords'] = $reserved_keywords;

// action icon
$icon_edit = "<img src=\"".$http_path['themes']."/".$themes_module."/images/edit_action.gif\" alt=\""._('Edit')."\" title=\""._('Edit')."\" border=0>";
$icon_delete = "<img src=\"".$http_path['themes']."/".$themes_module."/images/delete_action.gif\" alt=\""._('Delete')."\" title=\""._('Delete')."\" border=0>";
$icon_reply = "<img src=\"".$http_path['themes']."/".$themes_module."/images/reply_action.gif\" alt=\""._('Reply')."\" title=\""._('Reply')."\" border=0>";
$icon_manage = "<img src=\"".$http_path['themes']."/".$themes_module."/images/manage_action.gif\" alt=\""._('Manage')."\" title=\""._('Manage')."\" border=0>";
$icon_view = "<img src=\"".$http_path['themes']."/".$themes_module."/images/view_action.gif\" alt=\""._('View')."\" title=\""._('View')."\" border=0>";
$icon_calendar = "<img src=\"".$http_path['themes']."/".$themes_module."/images/cal.gif\" alt=\""._('Pick Date & Time')."\" title=\""._('Pick Date & Time')."\" border=0>";
$icon_sendsms = "<img src=\"".$http_path['themes']."/".$themes_module."/images/sendsms.gif\" alt=\""._('Send SMS')."\" title=\""._('Send SMS')."\" border=0>";
$icon_phonebook = "<img src=\"".$http_path['themes']."/".$themes_module."/images/phonebook_action.gif\" alt=\""._('Phonebook')."\" title=\""._('Phonebook')."\" border=0>";

// menus
$core_config['menu']['main_tab']['home'] = _('Home');
$core_config['menu']['main_tab']['my_account'] = _('My Account');
$core_config['menu']['main_tab']['administration'] = _('Administration');
$core_config['menu']['main_tab']['feature'] = _('Feature');
$core_config['menu']['main_tab']['tools'] = _('Tools');
$core_config['menu']['main_tab']['gateway'] = _('Gateway');

$menutab_my_account = $core_config['menu']['main_tab']['my_account'];
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=sms_template&op=list", _('Message template'));
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=send_sms&op=sendsmstopv", _('Send SMS'));
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=user_inbox&op=user_inbox", _('Inbox'));
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=user_incoming&op=user_incoming", _('Incoming SMS'));
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=user_outgoing&op=user_outgoing", _('Outgoing SMS'));
$arr_menu[$menutab_my_account][] = array("index.php?app=menu&inc=user_pref&op=user_pref", _('Preferences'));
//ksort($arr_menu[$menutab_my_account]);

$menutab_administration = $core_config['menu']['main_tab']['administration'];
if (isadmin()) {
	// administrator menus
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_inbox&op=all_inbox", _('All inbox'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_incoming&op=all_incoming", _('All incoming SMS'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_outgoing&op=all_outgoing", _('All outgoing SMS'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=user_mgmnt&op=user_list", _('Manage user'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=main_config&op=main_config", _('Main configuration'));
	//ksort($arr_menu[$menutab_administration]);
}

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();

?>
