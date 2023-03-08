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

 // define global variables
$icon_config = [];
$menu_config = [];
$plugin_config = [];
$user_config = [];
$core_config = [];

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
$core_config['datetime']['now'] = $datetime_now;
$core_config['datetime']['now_date'] = $date_now;
$core_config['datetime']['now_time'] = $time_now;
$core_config['datetime']['now_stamp'] = $datetime_now_stamp;

// security, checked by essential files under subdir
define('_SECURE_', 1);

// generate a unique Process ID
define('_PID_', uniqid('PID'));

include 'appsetup.php';

// defines storage
$fn = $core_config['apps_path']['storage'];
if (is_dir($fn)) {
	define('_APPS_PATH_STORAGE_', $fn);
} else {
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate storage'));
}

// defines base
$fn = $core_config['apps_path']['base'];
if (is_dir($fn)) {
	define('_APPS_PATH_BASE_', $fn);
	define('_HTTP_PATH_BASE_', $core_config['http_path']['base']);
} else {
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate base'));
}

// defines custom/override
$fn = $core_config['apps_path']['custom'] = _APPS_PATH_STORAGE_ . '/custom/' . $core_config['application']['dir'];
if (is_dir($fn)) {
	define('_APPS_PATH_CUSTOM_', $core_config['apps_path']['custom']);
} else {
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate custom'));
}

// defines temporary
$core_config['apps_path']['tmp'] = _APPS_PATH_STORAGE_ . '/tmp/' . $core_config['application']['dir'];
if (is_dir($fn)) {
	define('_APPS_PATH_TMP_', $core_config['apps_path']['tmp']);
} else {
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate tmp'));
}

// defines application
$fn = _APPS_PATH_STORAGE_ . '/' . $core_config['application']['dir'];
if (is_dir($fn)) {
	$core_config['apps_path']['application'] = $fn;
	define('_APPS_PATH_APPLICATION_', $core_config['apps_path']['application']);
} else {
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate application'));
}

// load application config
$fn = _APPS_PATH_APPLICATION_ . '/config.php';
if (file_exists($fn)) {

	include $fn;
} else {

	$fn = _APPS_PATH_CUSTOM_ . '/configs/config.php';
	if (file_exists($fn)) {
		
		include $fn;
	} else {
		ob_end_clean();
		die(_('FATAL ERROR') . ' : ' . _('Fail to load application config'));
	}
}

// get PHP version
if (!defined('_PHP_VER_')) {
	$version = explode('.', PHP_VERSION);
	define('_PHP_VER_', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

// saves remote IP address from alternate source or server's REMOTE_ADDR
if ($c_remote_addr = trim($core_config['remote_addr'])) {
	define('_REMOTE_ADDR_', $c_remote_addr);
} else {
	define('_REMOTE_ADDR_', $_SERVER['REMOTE_ADDR']);
}
unset($c_remote_addr);

// (bool) $DAEMON_PROCESS is special variable passed by daemon script
if (isset($DAEMON_PROCESS) && $DAEMON_PROCESS) {
	$core_config['daemon_process'] = true;
} else {
	$core_config['daemon_process'] = false;
}

// do these when this script wasn't called from daemon script
if (!$core_config['daemon_process']) {
	ini_set('session.cookie_lifetime', 0);
	ini_set('session.cookie_samesite', 'Strict');
	ini_set('session.cache_limiter', 'nocache');
	ini_set('session.use_trans_sid', FALSE);
	ini_set('session.use_strict_mode', TRUE);
	ini_set('session.use_cookies', TRUE);
	ini_set('session.use_only_cookies', TRUE);
	ini_set('session.cookie_httponly', TRUE);

	// set only when using HTTPS	
	$session_cookie_secure = 0;
	if (isset($_SERVER['HTTPS'])) {
		if (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] == '1') {
			ini_set('session.cookie_secure', TRUE);
			$session_cookie_secure = 1;
		}
	}

	session_start([
		'cookie_lifetime' => 0,
		'cookie_samesite' => 'Strict',
		'cache_limiter' => 'nocache',
		'use_trans_sid' => 0,
		'use_strict_mode' => 1,
		'use_cookies' => 1,
		'cookie_httponly' => 1,
		'cookie_secure' => $session_cookie_secure,
	]);

	if (!isset($_SESSION['last_update'])) {
		$_SESSION['last_update'] =  time();
	}

	// regenerate session ID every 20 minutes
	if (time() >= ($_SESSION['last_update'] + (20 * 60))) {
		session_regenerate_id(TRUE);
		$_SESSION['last_update'] = time();
	}

	if (trim($_SERVER['SERVER_PROTOCOL']) == 'HTTP/1.1') {
		header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
	} else {
		header('Pragma: no-cache');
	}

	header('X-Frame-Options: SAMEORIGIN');	
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

// app directories
$core_config['apps_path']['app'] = $core_config['apps_path']['application'] . '/app';

// libraries directory
$core_config['apps_path']['libs'] = $core_config['apps_path']['application'] . '/lib';

// plugins directory
$core_config['apps_path']['plug'] = $core_config['apps_path']['application'] . '/plugin';
$core_config['http_path']['plug'] = $core_config['http_path']['base'] . '/plugin';

// themes directories
$core_config['apps_path']['themes'] = $core_config['apps_path']['plug'] . '/themes';
$core_config['http_path']['themes'] = $core_config['http_path']['base'] . '/plugin/themes';

// common template directory
$core_config['apps_path']['tpl'] = $core_config['apps_path']['themes'] . '/common/templates';

// set defines
define('_APPS_PATH_APP_', $core_config['apps_path']['app']);

define('_APPS_PATH_LIBS_', $core_config['apps_path']['libs']);

define('_APPS_PATH_PLUG_', $core_config['apps_path']['plug']);
define('_HTTP_PATH_PLUG_', $core_config['http_path']['plug']);

define('_APPS_PATH_THEMES_', $core_config['apps_path']['themes']);
define('_HTTP_PATH_THEMES_', $core_config['http_path']['themes']);

define('_APPS_PATH_TPL_', $core_config['apps_path']['tpl']);

// system sender ID
define('_SYSTEM_SENDER_ID_', '@admin');

// load init functions
include_once _APPS_PATH_LIBS_ . '/fn_core.php';

// sanitize user inputs
foreach ($_GET as $key => $val) {
	$_GET[$key] = core_addslashes(core_sanitize_inputs($val));
}
foreach ($_POST as $key => $val) {
	$_POST[$key] = core_addslashes(core_sanitize_inputs($val));
}

// too many codes using $_REQUEST, until we revise them all we use this as a workaround
$_REQUEST = [];
$_REQUEST = array_merge($_GET, $_POST);

// global defines
define('_APP_', core_sanitize_query($_REQUEST['app']));
define('_INC_', core_sanitize_query($_REQUEST['inc']));
define('_OP_', core_sanitize_query($_REQUEST['op']));
define('_ROUTE_', core_sanitize_query($_REQUEST['route']));
define('_PAGE_', core_sanitize_query($_REQUEST['page']));
define('_NAV_', core_sanitize_query($_REQUEST['nav']));
define('_CAT_', core_sanitize_query($_REQUEST['cat']));
define('_PLUGIN_', core_sanitize_query($_REQUEST['plugin']));

// additional global defines
// from _INC_ we get plugin category and plugin name
$c_plugin_category = "";
$c_plugin_name = "";
if (_INC_) {
	$p = explode('_', _INC_, 2);
	if (isset($p[0]) && isset($p[1])) {
		$c_plugin_category = $p[0];
		$c_plugin_name = $p[1];
	}
}
define('_INC_CAT_', $c_plugin_category);
define('_INC_PLUGIN_', $c_plugin_name);

// check and prepare anti-CSRF
if (!((_APP_ == 'ws') || (_APP_ == 'webservices') || (_APP_ == 'call') || ($core_config['init']['ignore_csrf']))) {
	
	// print_r($_POST); print_r($_SESSION);
	if ($_POST) {
		if (!core_csrf_validate()) {
			_log('WARNING: possible CSRF attack. sid:' . session_id() . ' ip:' . _REMOTE_ADDR_, 2, 'init');
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden, CSRF validation failed');
			die('CSRF validation failed');
		}
	}
	$csrf = core_csrf_set();
	define('_CSRF_TOKEN_', $csrf['value']);
	define('_CSRF_FORM_', $csrf['form']);
	unset($csrf);
}

// save last $_POST in $_SESSION
if (!empty($_POST)) {

	// fixme anton - clean last posts
	$c_last_post = array();
	foreach ($_POST as $key => $val) {
		$val = str_replace('{{', '', $val);
		$val = str_replace('}}', '', $val);
		$val = str_replace('|', '', $val);
		$val = str_replace('`', '', $val);
		$val = str_replace('..', '', $val);
		$c_last_post[$key] = $val;
	}
	
	$_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_ . _INC_))] = $c_last_post;
}

// connect to database
if (!($dba_object = dba_connect(_DB_USER_, _DB_PASS_, _DB_NAME_, _DB_HOST_, _DB_PORT_))) {
	
	// _log('Fail to connect to database', 4, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to connect to database'));
}

// set charset to UTF-8
dba_query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");

// get main config from registry and load it to $core_config['main']
$result = registry_search(1, 'core', 'main_config');
foreach ($result['core']['main_config'] as $key => $val) {
	$ {
		$key
	} = $val;
	$core_config['main'][$key] = $val;
}

if (!$core_config['main']) {
	_log('Fail to load main config from registry', 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load main config from registry'));
}

// --- playSMS Specifics --- //


// plugins category
$core_config['plugins']['category'] = array(
	'feature',
	'gateway',
	'themes',
	'language' 
);

// max sms text length
// single text sms can be 160 char instead of 1*153
$sms_max_count = ((int) $sms_max_count < 1 ? 1 : (int) $sms_max_count);
$core_config['main']['sms_max_count'] = $sms_max_count;
$core_config['main']['per_sms_length'] = ($core_config['main']['sms_max_count'] > 1 ? 153 : 160);
$core_config['main']['per_sms_length_unicode'] = ($core_config['main']['sms_max_count'] > 1 ? 67 : 70);
$core_config['main']['max_sms_length'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length'];
$core_config['main']['max_sms_length_unicode'] = $core_config['main']['sms_max_count'] * $core_config['main']['per_sms_length_unicode'];

// reserved important keywords
$core_config['reserved_keywords'] = array(
	'BC' 
);

if (auth_isvalid()) {
	
	// load user's data from user's DB table
	$user_config = user_getdatabyusername($_SESSION['username']);
	$user_config['opt']['sms_footer_length'] = (strlen($footer) > 0 ? strlen($footer) + 1 : 0);
	$user_config['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $user_config['opt']['sms_footer_length'];
	$user_config['opt']['gravatar'] = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user_config['email'])));
	
	// special setting to credit unicode SMS the same as normal SMS length
	// for example: 2 unicode SMS (140 chars length) will be deducted as 1 credit just like a normal SMS (160 chars length)
	$result = registry_search($user_config['uid'], 'core', 'user_config', 'enable_credit_unicode');
	$user_config['opt']['enable_credit_unicode'] = (int) $result['core']['user_config']['enable_credit_unicode'];
	if (!$user_config['opt']['enable_credit_unicode']) {
		// global config overriden by user config
		$user_config['opt']['enable_credit_unicode'] = (int) $core_config['main']['enable_credit_unicode'];
	}
	
	// update last_update
	if (!$core_config['daemon_process']) {

		// dont update on webservices call		
		if (!((_APP_ == 'ws') || (_APP_ == 'webservices'))) {
			// update session last_update
			user_session_update($_SESSION['uid'], [
				'last_update' => core_get_datetime(),
			]);
		}
	}
}

// override main config with site config for branding purposes distinguished by domain name
$site_config = array();
if ((!$core_config['daemon_process']) && $_SERVER['HTTP_HOST']) {
	$s = site_config_getbydomain($_SERVER['HTTP_HOST']);
	if ((int) $s[0]['uid']) {
		$c_site_config = site_config_get((int) $s[0]['uid']);
		if (strtolower($c_site_config['domain']) == strtolower($_SERVER['HTTP_HOST'])) {
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
	_log('Fail to load themes ' . core_themes_get(), 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load themes') . ' ' . core_themes_get());
}

// verify selected language_module exists
$fn1 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/config.php';
$fn2 = _APPS_PATH_PLUG_ . '/language/' . core_lang_get() . '/fn.php';
if (!(file_exists($fn1) && file_exists($fn2))) {
	_log('Fail to load language ' . core_lang_get(), 1, 'init');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to load language') . ' ' . core_lang_get());
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', _APPS_PATH_TMP_ . '/plugin/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

if (auth_isvalid()) {
	
	// set user lang
	core_setuserlang($_SESSION['username']);
} else {
	core_setuserlang();
}

// daemon's queue default values


// limit the number of DLR processed by dlrd in one time
$core_config['dlrd_limit'] = ($core_config['dlrd_limit'] ? $core_config['dlrd_limit'] : 10000);

// limit the number of incoming SMS processed by recvsmsd in one time
$core_config['recvsmsd_limit'] = ($core_config['recvsmsd_limit'] ? $core_config['recvsmsd_limit'] : 10000);

// limit the number of queue processed by sendsmsd in one time
$core_config['sendsmsd_queue'] = ($core_config['sendsmsd_queue'] ? $core_config['sendsmsd_queue'] : 20);

// limit the number of chunk per queue
$core_config['sendsmsd_chunk'] = ($core_config['sendsmsd_chunk'] ? $core_config['sendsmsd_chunk'] : 20);

// chunk size
$core_config['sendsmsd_chunk_size'] = ($core_config['sendsmsd_chunk_size'] ? $core_config['sendsmsd_chunk_size'] : 100);

// fixme anton - debug
//print_r($icon_config); die();
//print_r($menu_config); die();
//print_r($plugin_config); die();
//print_r($user_config); die();
//print_r($core_config); die();
//print_r($GLOBALS); die();
//print_r($_SESSION); die();
