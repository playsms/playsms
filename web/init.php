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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
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

// do these when this script wasn't called from daemon script
if (!$core_config['daemon_process']) {
	if (trim($_SERVER['SERVER_PROTOCOL']) == 'HTTP/1.1') {
		header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
	} else {
		header('Pragma: no-cache');
	}
	header('X-Frame-Options: SAMEORIGIN');
	@session_start();
}

// output buffering starts even from daemon script
ob_start();

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
$core_config['http_path']['base'] = ($core_config['ishttps'] ? 'https://' : 'http://') . $c_http_host . (dirname($c_php_self) == '/' ? '/' : dirname($c_php_self));

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

// storage directories
$core_config['apps_path']['storage'] = $core_config['apps_path']['base'] . '/storage';
$core_config['http_path']['storage'] = $core_config['http_path']['base'] . '/storage';

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

define('_APPS_PATH_STORAGE_', $core_config['apps_path']['storage']);
define('_HTTP_PATH_STORAGE_', $core_config['http_path']['storage']);

// system sender ID
define('_SYSTEM_SENDER_ID_', '@admin');

// load init functions
include_once _APPS_PATH_LIBS_ . '/fn_core.php';

// if magic quotes gps is set to Off (which is recommended) then addslashes all requests
if (!get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) {
		$_GET[$key] = core_addslashes($val);
	}
	foreach ($_POST as $key => $val) {
		$_POST[$key] = core_addslashes($val);
	}
}

// sanitize user inputs
if ($_POST['X-CSRF-Token']) {
	foreach ($_POST as $key => $val) {
		$_POST[$key] = core_sanitize_inputs($val);
	}
}
foreach ($_GET as $key => $val) {
	$_GET[$key] = core_sanitize_inputs($val);
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

// save last $_POST in $_SESSION
if ($_POST['X-CSRF-Token']) {
	$_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_ . _INC_))] = $_POST;
}

// enable anti-CSRF for anything but webservices
if (!((_APP_ == 'ws') || (_APP_ == 'webservices') || ($core_config['init']['ignore_csrf']))) {
	
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
	$ {
		$key
	} = $val;
	$core_config['main'][$key] = $val;
}

if (!$core_config['main']) {
	logger_print('Fail to load main config from registry', 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load main config from registry'));
}

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
$plugins_category = array(
	'feature',
	'gateway',
	'themes',
	'language' 
);
$core_config['plugins_category'] = $plugins_category;

// max sms text length
// single text sms can be 160 char instead of 1*153
$sms_max_count = ((int) $sms_max_count < 1 ? 1 : (int) $sms_max_count);
$core_config['main']['sms_max_count'] = $sms_max_count;
$core_config['main']['per_sms_length'] = ($core_config['main']['sms_max_count'] > 1 ? 153 : 160);
$core_config['main']['per_sms_length_unicode'] = ($core_config['main']['sms_max_count'] > 1 ? 67 : 70);
$core_config['main']['max_sms_length'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length'];
$core_config['main']['max_sms_length_unicode'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length_unicode'];

// reserved important keywords
$reserved_keywords = array(
	'BC' 
);
$core_config['reserved_keywords'] = $reserved_keywords;

if (auth_isvalid()) {
	
	// load user's data from user's DB table
	$user_config = user_getdatabyusername($_SESSION['username']);
	$user_config['opt']['sms_footer_length'] = (strlen($footer) > 0 ? strlen($footer) + 1 : 0);
	$user_config['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['gravatar'] = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user_config['email'])));
	if (!$core_config['daemon_process']) {
		
		// save login session information
		user_session_set();
	}
	
	// special setting to credit unicode SMS the same as normal SMS length
	// for example: 2 unicode SMS (140 chars length) will be deducted as 1 credit just like a normal SMS (160 chars length)
	$result = registry_search($user_config['uid'], 'core', 'user_config', 'enable_credit_unicode');
	$user_config['opt']['enable_credit_unicode'] = (int) $result['core']['user_config']['enable_credit_unicode'];
	if (!$user_config['opt']['enable_credit_unicode']) {
		// global config overriden by user config
		$user_config['opt']['enable_credit_unicode'] = (int) $core_config['main']['enable_credit_unicode'];
	}
}

// override main config with site config for branding purposes distinguished by domain name
$site_config = array();
if ((!$core_config['daemon_process']) && $_SERVER['HTTP_HOST']) {
	$s = site_config_getbydomain($_SERVER['HTTP_HOST']);
	if ((int) $s[0]['uid']) {
		$c_site_config = site_config_get((int) $s[0]['uid']);
		if (strtolower($c_site_config['domain']) == strtoloweR($_SERVER['HTTP_HOST'])) {
			$site_config = array_merge($c_site_config, $s[0]);
		}
	}
}

if ((!$core_config['daemon_process']) && trim($_SERVER['HTTP_HOST']) && trim($site_config['domain']) && (strtolower(trim($_SERVER['HTTP_HOST'])) == strtolower(trim($site_config['domain'])))) {
	$core_config['main'] = array_merge($core_config['main'], $site_config);
}

// verify selected themes_module exists
$fn1 = _APPS_PATH_PLUG_ . '/themes/' . core_themes_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/themes/' . core_themes_get() . '/fn.php';
if (!(file_exists($fn1) && file_exists($fn2))) {
	logger_print('Fail to load themes ' . core_themes_get(), 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load themes') . ' ' . core_themes_get());
}

// verify selected language_module exists
$fn1 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/fn.php';
if (!(file_exists($fn1) && file_exists($fn2))) {
	logger_print('Fail to load language ' . core_lang_get(), 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load language') . ' ' . core_lang_get());
}

if (auth_isvalid()) {
	
	// set user lang
	core_setuserlang($_SESSION['username']);
} else {
	core_setuserlang();
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', _APPS_PATH_PLUG_ . '/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// fixme anton - debug
//print_r($icon_config); die();
//print_r($menu_config); die();
//print_r($plugin_config); die();
//print_r($user_config); die();
//print_r($core_config); die();
//print_r($GLOBALS); die();
