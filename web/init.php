<?php
include "config.php";

// security, checked by essential files under subdir
define('_SECURE_', 1);

// generate a unique Process ID
define('_PID_', uniqid('PID'));

$core_config['daemon_process'] = $DAEMON_PROCESS;

if (!$core_config['daemon_process']) {
	if (trim($SERVER_PROTOCOL)=="HTTP/1.1") {
		header ("Cache-Control: no-cache, must-revalidate");
	} else {
		header ("Pragma: no-cache");
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
$apps_path['base']        = dirname($c_script_filename);

// base application http path
$http_path['base']        = ( $core_config['ishttps'] ? "https://" : "http://" ).$c_http_host.( dirname($c_php_self)=='/' ? '/' : dirname($c_php_self) );

// libraries directory
$apps_path['libs']	= $apps_path['base'].'/lib';
$http_path['libs']	= $http_path['base'].'/lib';

// core plugins directories
$apps_path['incs']	= $apps_path['base'].'/inc';
$http_path['incs']	= $http_path['base'].'/inc';

// plugins directory
$apps_path['plug']	= $apps_path['base'].'/plugin';
$http_path['plug']	= $http_path['base'].'/plugin';

// themes directories
$apps_path['themes']	= $apps_path['plug'].'/themes';
$http_path['themes']	= $http_path['plug'].'/themes';

// insert to global config
$core_config['apps_path'] = $apps_path;
$core_config['http_path'] = $http_path;

// load init functions
include_once $apps_path['libs']."/fn_init.php";

// if magic quotes gps is set to Off (which is recommended) then addslashes all requests
if (! get_magic_quotes_gpc()) {
	foreach($_GET as $key => $val){$_GET[$key]=pl_addslashes($val);}
	foreach($_POST as $key => $val){$_POST[$key]=pl_addslashes($val);}
}

// too many codes using $_REQUEST, until we revise them all we use this as a workaround
empty($_REQUEST);
$_REQUEST = array_merge($_GET, $_POST);

// plugins category
$plugins_category = array('tools','feature','gateway','themes','language');
$core_config['plugins_category'] = $plugins_category;

// connect to database
$dba_object = dba_connect(_DB_USER_,_DB_PASS_,_DB_NAME_,_DB_HOST_,_DB_PORT_);

// set charset to UTF-8
dba_query("SET NAMES utf8");

// get main config
$db_query = "SELECT * FROM "._DB_PREF_."_tblConfig_main";
$db_result = dba_query($db_query);
$db_row = dba_fetch_array($db_result);
if (isset($db_row)) {
	$web_title = $db_row['cfg_web_title'];
	$email_service = $db_row['cfg_email_service'];
	$email_footer = $db_row['cfg_email_footer'];
	$gateway_number = core_sanitize_sender($db_row['cfg_gateway_number']);
	$gateway_timezone = $db_row['cfg_datetime_timezone'];
	$gateway_module = ( $db_row['cfg_gateway_module'] ? $db_row['cfg_gateway_module'] : 'smstools' );
	$themes_module = ( $db_row['cfg_themes_module'] ? $db_row['cfg_themes_module'] : 'default' );
	$language_module = ( $db_row['cfg_language_module'] ? $db_row['cfg_language_module'] : 'en_US' );
	$default_rate = $db_row['cfg_default_rate'];
	$sms_max_count = $db_row['cfg_sms_max_count'];
	$default_credit = $db_row['cfg_default_credit'];
	$enable_register = $db_row['cfg_enable_register'];
	$enable_forgot = $db_row['cfg_enable_forgot'];
	$core_config['main'] = $db_row;
}

// max sms text length
// single text sms can be 160 char instead of 1*153
$sms_max_count = ( (int)$sms_max_count < 1 ? 1 : (int)$sms_max_count );
$core_config['main']['cfg_sms_max_count'] = $sms_max_count;
$core_config['main']['per_sms_length'] = ( $core_config['main']['cfg_sms_max_count'] > 1 ? 153 : 160 );
$core_config['main']['per_sms_length_unicode'] = ( $core_config['main']['cfg_sms_max_count'] > 1 ? 63 : 70 );
$core_config['main']['max_sms_length'] = $core_config['main']['cfg_sms_max_count'] * $core_config['main']['per_sms_length'];
$core_config['main']['max_sms_length_unicode'] = $core_config['main']['cfg_sms_max_count'] * $core_config['main']['per_sms_length_unicode'];

// verify selected gateway_module exists
$fn1 = $apps_path['plug'].'/gateway/'.$gateway_module.'/config.php';
$fn2 = $apps_path['plug'].'/gateway/'.$gateway_module.'/fn.php';
if (file_exists($fn1) && file_exists($fn2)) {
	$core_config['module']['gateway'] = $gateway_module;
}

// verify selected themes_module exists
$fn1 = $apps_path['plug'].'/themes/'.$themes_module.'/config.php';
$fn2 = $apps_path['plug'].'/themes/'.$themes_module.'/fn.php';
if (file_exists($fn1) && file_exists($fn2)) {
	$core_config['module']['themes'] = $themes_module;
}

// verify selected language_module exists
$fn1 = $apps_path['plug'].'/language/'.$language_module.'/config.php';
$fn2 = $apps_path['plug'].'/language/'.$language_module.'/fn.php';
if (file_exists($fn1) && file_exists($fn2)) {
	$core_config['module']['language'] = $language_module;
}

if (valid()) {
	setuserlang($_SESSION['username']);
} else {
	setuserlang();
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// set global variable
$date_format		= "Y-m-d";
$time_format		= "H:i:s";
$datetime_format 	= $date_format." ".$time_format;
$date_now		= date($date_format, time());
$time_now		= date($time_format, time());
$datetime_now		= date($datetime_format, time());

$core_config['datetime']['date_now'] 	= $date_now;
$core_config['datetime']['time_now'] 	= $time_now;
$core_config['datetime']['now'] 	= $datetime_now;
$core_config['datetime']['format'] 	= $datetime_format;

$datetime_format_stamp	= "YmdHis";
$datetime_now_stamp	= date($datetime_format_stamp, time());

$core_config['datetime']['now_stamp']		= $datetime_now_stamp;
$core_config['datetime']['format_stamp']	= $datetime_format_stamp;

$nd 			= "<div class=required>(*)</div>";

if (! ($core_config['module']['gateway'] && $core_config['module']['themes'] && $core_config['module']['language'])) {
	logger_print("Fail to load gateway, themes or language module", 1, "init");
	ob_end_clean();
	die(_('FATAL ERROR').' : '._('Fail to load gateway, themes or language module'));
}

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();

?>