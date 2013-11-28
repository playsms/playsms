<?php
defined('_SECURE_') or die('Forbidden');

// main functions
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_billing.php";
include $apps_path['libs']."/fn_dlr.php";
include $apps_path['libs']."/fn_recvsms.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_themes.php";
include $apps_path['libs']."/fn_tpl.php";
include $apps_path['libs']."/fn_webservices.php";

// init global variables

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
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=1", _('Send SMS'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_inbox&op=user_inbox", _('Inbox'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_incoming&op=user_incoming", _('Incoming SMS'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_outgoing&op=user_outgoing", _('Outgoing SMS'), 1);

if (isadmin()) {
	// administrator menus
	$menutab_administration = $core_config['menutab']['administration'];
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_inbox&op=all_inbox", _('All inbox'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_incoming&op=all_incoming", _('All incoming SMS'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_outgoing&op=all_outgoing", _('All outgoing SMS'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=user_mgmnt&op=user_list", _('Manage user'), 2);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=main_config&op=main_config", _('Main configuration'), 2);
	//ksort($menu_config[$menutab_administration]);
}

// fixme anton - uncomment this if you want to know what are available in global config arrays
//print_r($menu_config); die();
//print_r($core_config); die();

// end of init global variables

// load plugin's config and libraries
for ($i=0;$i<count($plugins_category);$i++) {
	if ($pc = $plugins_category[$i]) {
		// get plugins
		$dir = $apps_path['plug'].'/'.$pc.'/';
		unset($core_config[$pc.'list']);
		unset($tmp_core_config[$pc.'list']);
		$fd = opendir($dir);
		$pc_names = array();
		while(false !== ($pl_name = readdir($fd))) {
			// plugin's dir prefixed with dot or underscore will not be loaded
			if (substr($pl_name, 0, 1) != "." && substr($pl_name, 0, 1) != "_" ) {
				// exeptions for themes/common
				if (! (($pc == 'themes') && ($pl_name == 'common'))) {
					$pc_names[] = $pl_name;
				}
			}
		}
		closedir();
		sort($pc_names);
		for ($j=0;$j<count($pc_names);$j++) {
			if (is_dir($dir.$pc_names[$j])) {
				$core_config[$pc.'list'][] = $pc_names[$j];
			}
		}
	}
}

// load each plugin's config
$dir = $apps_path['plug'].'/';
$pcs = array('themes', 'language', 'gateway', 'feature', 'tools');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/config.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load each plugin's libs
$dir = $apps_path['plug'].'/';
$pcs = array('feature', 'tools');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/fn.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load active themes libs
$dir = $apps_path['plug'].'/';
$pc = 'themes';
$pl = core_themes_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/fn.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
}

// load common items for themes
$c_fn1 = $apps_path['plug'].'/themes/common/config.php';
if (file_exists($c_fn1)) {
	include $c_fn1;
	$c_fn2 = $apps_path['plug'].'/themes/common/fn.php';
	if (file_exists($c_fn2)) {
		include $c_fn2;
	}
}

// themes icons
$icons = $core_config['plugin'][core_themes_get()]['icon'];
if (is_array($icons)) {
	foreach ($icons as $icon_action => $icon_url) {
		if ($icon_action && $icon_url) {
			$core_config['icon'][$icon_action] = $icon_url;
		}
	}
}
	
// load active gateway libs
$dir = $apps_path['plug'].'/';
$pc = 'gateway';
$pl = core_gateway_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/fn.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// init global variables after plugins

// load menus into core_config
$core_config['menu'] = $menu_config;

// fixme anton - uncomment this if you want to know what are available in global config arrays
//print_r($menu_config); die();
//print_r($core_config); die();

// end of global variables after plugins

?>