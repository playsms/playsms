<?php
defined('_SECURE_') or die('Forbidden');

// this file loaded before plugins

// global variables
$app = q_sanitize($_REQUEST['app']);
$inc = q_sanitize($_REQUEST['inc']);
$op = q_sanitize($_REQUEST['op']);
$route = q_sanitize($_REQUEST['route']);
$page = q_sanitize($_REQUEST['page']);
$nav = q_sanitize($_REQUEST['nav']);

// global defines
define('_APP_', $app);
define('_INC_', $inc);
define('_OP_', $op);
define('_ROUTE_', $route);
define('_PAGE_', $page);
define('_NAV_', $nav);

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

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();
//print_r($menu_config); die();

?>