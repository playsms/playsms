<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */
include 'config.php';

// security, checked by essential files under subdir
define('_SECURE_', 1);

// generate a unique Process ID
define('_PID_', uniqid('PID'));

// get PHP version
if (!defined('_PHP_VER_')) {
	$version = explode('.', PHP_VERSION);
	define('_PHP_VER_', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

// $DAEMON_PROCESS is special variable passed by daemon script
$core_config['daemon_process'] = $DAEMON_PROCESS;

if (!$core_config['daemon_process']) {
	if (trim($SERVER_PROTOCOL) == 'HTTP/1.1') {
		header('Cache-Control: no-cache, must-revalidate');
	} else {
		header('Pragma: no-cache');
	}
	@session_start();
	ob_start();
}

// DB config defines
define('_DB_TYPE_', $core_config['db']['type']);
define('_DB_HOST_', $core_config['db']['host']);
define('_DB_PORT_', $core_config['db']['port']);
define('_DB_USER_', $core_config['db']['user']);
define('_DB_PASS_', $core_config['db']['pass']);
define('_DB_NAME_', $core_config['db']['name']);

// defines DSN
define('_DB_DSN_', $core_config['db']['dsn']);
define('_DB_OPT_', $core_config['db']['options']);

$core_config['db']['pref'] = 'playsms';
define('_DB_PREF_', $core_config['db']['pref']);

// SMTP config defines
define('_SMTP_RELM_', $core_config['smtp']['relm']);
define('_SMTP_USER_', $core_config['smtp']['user']);
define('_SMTP_PASS_', $core_config['smtp']['pass']);
define('_SMTP_HOST_', $core_config['smtp']['host']);
define('_SMTP_PORT_', $core_config['smtp']['port']);

$c_script_filename = __FILE__;
$c_php_self = $_SERVER['PHP_SELF'];
$c_http_host = $_SERVER['HTTP_HOST'];

// base application directory
$core_config['apps_path']['base'] = dirname($c_script_filename);

// base application http path
$core_config['http_path']['base'] = ( $core_config['ishttps'] ? 'https://' : 'http://' ) . $c_http_host . ( dirname($c_php_self) == '/' ? '/' : dirname($c_php_self) );

// libraries directory
$core_config['apps_path']['libs'] = $core_config['apps_path']['base'] . '/lib';
$core_config['http_path']['libs'] = $core_config['http_path']['base'] . '/lib';

// core plugins directories
$core_config['apps_path']['incs'] = $core_config['apps_path']['base'] . '/inc';
$core_config['http_path']['incs'] = $core_config['http_path']['base'] . '/inc';

// plugins directory
$core_config['apps_path']['plug'] = $core_config['apps_path']['base'] . '/plugin';
$core_config['http_path']['plug'] = $core_config['http_path']['base'] . '/plugin';

// themes directories
$core_config['apps_path']['themes'] = $core_config['apps_path']['plug'] . '/themes';
$core_config['http_path']['themes'] = $core_config['http_path']['plug'] . '/themes';

// themes directories
$core_config['apps_path']['tpl'] = $core_config['apps_path']['themes'] . '/common/templates';
$core_config['http_path']['tpl'] = $core_config['http_path']['themes'] . '/common/templates';

// set defines
define('_APPS_PATH_BASE_', $core_config['apps_path']['base']);
define('_HTTP_PATH_BASE_', $core_config['http_path']['base']);

define('_APPS_PATH_LIBS_', $core_config['apps_path']['libs']);
define('_HTTP_PATH_LIBS_', $core_config['http_path']['libs']);

define('_APPS_PATH_INCS_', $core_config['apps_path']['incs']);
define('_HTTP_PATH_INCS_', $core_config['http_path']['incs']);

define('_APPS_PATH_PLUG_', $core_config['apps_path']['plug']);
define('_HTTP_PATH_PLUG_', $core_config['http_path']['plug']);

define('_APPS_PATH_THEMES_', $core_config['apps_path']['themes']);
define('_HTTP_PATH_THEMES_', $core_config['http_path']['themes']);

define('_APPS_PATH_TPL_', $core_config['apps_path']['tpl']);
define('_HTTP_PATH_TPL_', $core_config['http_path']['tpl']);

// load init functions
include_once _APPS_PATH_LIBS_ . '/fn_init.php';

// if magic quotes gps is set to Off (which is recommended) then addslashes all requests
if (!get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) {
		$_GET[$key] = pl_addslashes($val);
	}
	foreach ($_POST as $key => $val) {
		$_POST[$key] = pl_addslashes($val);
	}
}

// too many codes using $_REQUEST, until we revise them all we use this as a workaround
empty($_REQUEST);
$_REQUEST = array_merge($_GET, $_POST);

// global defines
define('_APP_', core_query_sanitize($_REQUEST['app']));
define('_INC_', core_query_sanitize($_REQUEST['inc']));
define('_OP_', core_query_sanitize($_REQUEST['op']));
define('_ROUTE_', core_query_sanitize($_REQUEST['route']));
define('_PAGE_', core_query_sanitize($_REQUEST['page']));
define('_NAV_', core_query_sanitize($_REQUEST['nav']));
define('_CAT_', core_query_sanitize($_REQUEST['cat']));
define('_PLUGIN_', core_query_sanitize($_REQUEST['plugin']));

// enable anti-CSRF for anything but webservices
if (!((_APP_ == 'ws') || (_APP_ == 'webservices'))) {
	// print_r($_POST); print_r($_SESSION);
	if ($_POST) {
		if (!core_csrf_validate()) {
			logger_print('WARNING: possible CSRF attack. sid:' . $_SESSION['sid'] . ' ip:' . $_SERVER['REMOTE_ADDR'], 2, 'init');
			auth_block();
		}
	}
	$csrf = core_csrf_set();
	define('_CSRF_TOKEN_', $csrf['value']);
	define('_CSRF_FORM_', $csrf['form']);
	unset($csrf);
}

// connect to database
if (!($dba_object = dba_connect(_DB_USER_, _DB_PASS_, _DB_NAME_, _DB_HOST_, _DB_PORT_))) {
	// logger_print('Fail to connect to database', 4, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to connect to database'));
}

// set charset to UTF-8
dba_query('SET NAMES utf8');

// get main config from registry and load it to $core_config['main']
$result = registry_search(1, 'core', 'main_config');
foreach ($result['core']['main_config'] as $key => $val) {
	${$key} = $val;
	$core_config['main'][$key] = $val;
}

if (!$core_config['main']) {
	logger_print('Fail to load main config from registry', 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load main config from registry'));
}

// default loaded page/plugin
$core_config['main']['default_inc'] = 'page_welcome';
$core_config['main']['default_op'] = 'page_welcome';

// set global date/time variables
$date_format = 'Y-m-d';
$time_format = 'H:i:s';
$datetime_format = $date_format . ' ' . $time_format;
$date_now = date($date_format, time());
$time_now = date($time_format, time());
$datetime_now = date($datetime_format, time());
$datetime_format_stamp = 'YmdHis';
$datetime_now_stamp = date($datetime_format_stamp, time());

$core_config['datetime']['format'] = $datetime_format;
$core_config['datetime']['now_stamp'] = $datetime_now_stamp;


// --- playSMS Specifics --- //


// plugins category
$plugins_category = array('tools', 'feature', 'gateway', 'themes', 'language');
$core_config['plugins_category'] = $plugins_category;

// max sms text length
// single text sms can be 160 char instead of 1*153
$sms_max_count = ( (int) $sms_max_count < 1 ? 1 : (int) $sms_max_count );
$core_config['main']['sms_max_count'] = $sms_max_count;
$core_config['main']['per_sms_length'] = ( $core_config['main']['sms_max_count'] > 1 ? 153 : 160 );
$core_config['main']['per_sms_length_unicode'] = ( $core_config['main']['sms_max_count'] > 1 ? 67 : 70 );
$core_config['main']['max_sms_length'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length'];
$core_config['main']['max_sms_length_unicode'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length_unicode'];

// reserved important keywords
$reserved_keywords = array('BC');
$core_config['reserved_keywords'] = $reserved_keywords;

// verify selected gateway_module exists
$continue = FALSE;
$fn1 = _APPS_PATH_PLUG_ . '/gateway/' . core_gateway_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/gateway/' . core_gateway_get() . '/fn.php';
if (file_exists($fn1) && file_exists($fn2)) {
	$continue = TRUE;
}

// verify selected themes_module exists
$fn1 = _APPS_PATH_PLUG_ . '/themes/' . core_themes_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/themes/' . core_themes_get() . '/fn.php';
if ($continue && file_exists($fn1) && file_exists($fn2)) {
	$continue = TRUE;
} else {
	$continue = FALSE;
}

// verify selected language_module exists
$fn1 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/fn.php';
if ($continue && file_exists($fn1) && file_exists($fn2)) {
	$continue = TRUE;
} else {
	$continue = FALSE;
}

if (! $continue) {
	logger_print('Fail to load gateway, themes or language module', 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load gateway, themes or language module'));
}

if (auth_isvalid()) {
	// load user's data from user's DB table
	$user_config = user_getdatabyusername($_SESSION['username']);
	$user_config['opt']['sms_footer_length'] = ( strlen($footer) > 0 ? strlen($footer) + 1 : 0 );
	$user_config['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['gravatar'] = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user_config['email'])));
	if (!$core_config['daemon_process']) {
		// save login session information
		user_session_set();
	}
	// set user lang
	setuserlang($_SESSION['username']);
} else {
	setuserlang();
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', _APPS_PATH_PLUG_ . '/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// menus
$core_config['menutab']['home'] = _('Home');
$core_config['menutab']['my_account'] = _('My Account');
$core_config['menutab']['tools'] = _('Tools');
$core_config['menutab']['feature'] = _('Feature');
$core_config['menutab']['administration'] = _('Administration');

$menutab_my_account = $core_config['menutab']['my_account'];
$menu_config[$menutab_my_account][] = array('index.php?app=main&inc=send_sms&op=send_sms', _('Send message'), 1);
$menu_config[$menutab_my_account][] = array('index.php?app=main&inc=user_inbox&op=user_inbox', _('Inbox'), 1);
$menu_config[$menutab_my_account][] = array('index.php?app=main&inc=user_incoming&op=user_incoming', _('Incoming messages'), 1);
$menu_config[$menutab_my_account][] = array('index.php?app=main&inc=user_outgoing&op=user_outgoing', _('Outgoing messages'), 1);

if (auth_isadmin()) {
	// administrator menus
	$menutab_administration = $core_config['menutab']['administration'];
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=all_inbox&op=all_inbox', _('All inbox'), 1);
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=all_incoming&op=all_incoming', _('All incoming messages'), 1);
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=all_outgoing&op=all_outgoing', _('All outgoing messages'), 1);
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=sandbox&op=sandbox', _('Sandbox'), 1);
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=user_mgmnt&op=user_list', _('Manage user'), 2);
	$menu_config[$menutab_administration][] = array('index.php?app=main&inc=main_config&op=main_config', _('Main configuration'), 2);
	//ksort($menu_config[$menutab_administration]);
}

// fixme anton - debug
//print_r($icon_config); die();
//print_r($menu_config); die();
//print_r($plugin_config); die();
//print_r($user_config); die();
//print_r($core_config); die();
//print_r($GLOBALS); die();
