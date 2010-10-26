<?php
include "config.php";

// security, checked by essential files under subdir
define('_SECURE_', 1);

$core_config['daemon_process'] = $DAEMON_PROCESS;

if (!$core_config['daemon_process']) {
    if (trim($SERVER_PROTOCOL)=="HTTP/1.1") {
	header ("Cache-Control: no-cache, must-revalidate");
    } else {
	header ("Pragma: no-cache");
    }
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

/*
start init functions
protect from SQL injection when magic_quotes_gpc sets to "Off"
*/
function array_add_slashes($array) {
    if (is_array($array)) {
	foreach ($array as $key => $value) {
            if (!is_array($value)) {
        	$value = addslashes($value);
        	$key = addslashes($key);
        	$new_arr[$key] = $value;
    	    }
            if (is_array($value)) {
        	$new_arr[$key] = array_add_slashes($value);
            }
        }
    }
    return $new_arr;
}

function pl_addslashes($data) {
    global $core_config;
    if ($core_config['db']['type']=="mssql") {
	$data = str_replace("'", "''", $data); 
    } else {
	if (is_array($data)) {
	    $data = array_add_slashes($data);
	} else {
	    $data = addslashes($data);
	}
    }
    return $data; 
}

// fixme anton
// enforced to declare function _() for gettext replacement if no PHP gettext extension found
// it is also possible to completely remove gettext and change multi-lang with translation array
if (! function_exists('_')) {
    function _($text) {
	return $text;
    }
}
/*
end of init functions
*/

if (!get_magic_quotes_gpc()) {
    foreach($_GET as $key => $val){$_GET[$key]=pl_addslashes($_GET[$key]);}
    foreach($_POST as $key => $val){$_POST[$key]=pl_addslashes($_POST[$key]);}
    foreach($_COOKIE as $key => $val){$_COOKIE[$key]=pl_addslashes($_COOKIE[$key]);}
    foreach($_SERVER as $key => $val){$_SERVER[$key]=pl_addslashes($_SERVER[$key]);}
}

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

// plugins category
$plugins_category = array('tools','feature','gateway','themes','language');
$core_config['plugins_category'] = $plugins_category;

// include essential functions
include_once $apps_path['libs']."/dba.php";

// connect to database
$dba_object = dba_connect(_DB_USER_,_DB_PASS_,_DB_NAME_,_DB_HOST_,_DB_PORT_);

// set charset to UTF-8
dba_query("SET NAMES utf8");

// get main config
$db_query = "SELECT * FROM "._DB_PREF_."_tblConfig_main";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
    $web_title = $db_row['cfg_web_title'];
    $email_service = $db_row['cfg_email_service'];
    $email_footer = $db_row['cfg_email_footer'];
    $gateway_number = $db_row['cfg_gateway_number'];
    $default_rate = $db_row['cfg_default_rate'];
    $gateway_timezone = $db_row['cfg_datetime_timezone'];
    $tmp_gateway_module = $db_row['cfg_gateway_module'];
    $tmp_themes_module = $db_row['cfg_themes_module'];
    $tmp_language_module = $db_row['cfg_language_module'];
    $core_config['main'] = $db_row;
}

// verify selected gateway_module exists
$fn1 = $apps_path['plug'].'/gateway/'.$tmp_gateway_module.'/config.php';
$fn2 = $apps_path['plug'].'/gateway/'.$tmp_gateway_module.'/fn.php';
$gateway_module = 'smstools';
if (file_exists($fn1) && file_exists($fn2)) {
    $gateway_module = $tmp_gateway_module;
}
$core_config['module']['gateway'] = $gateway_module;

// verify selected themes_module exists
$fn1 = $apps_path['plug'].'/themes/'.$tmp_themes_module.'/config.php';
$fn2 = $apps_path['plug'].'/themes/'.$tmp_themes_module.'/fn.php';
$themes_module = 'default';
if (file_exists($fn1) && file_exists($fn2)) {
    $themes_module = $tmp_themes_module;
}
$core_config['module']['themes'] = $themes_module;

// verify selected language_module exists
$fn1 = $apps_path['plug'].'/language/'.$tmp_language_module.'/config.php';
$fn2 = $apps_path['plug'].'/language/'.$tmp_language_module.'/fn.php';
$language_module = 'en_US';
if (file_exists($fn1) && file_exists($fn2)) {
    $language_module = $tmp_language_module;
}
$core_config['module']['language'] = $language_module;

// multi-language init
// make sure that bindtextdomain is exists, server should have PHP gettext extension enabled
if (function_exists('bindtextdomain')) {
    bindtextdomain('messages', $apps_path['plug'].'/language/');
    textdomain('messages');
}
setlocale(LC_ALL, $language_module, $language_module.'.utf8', $language_module.'.utf-8', $language_module.'.UTF8', $language_module.'.UTF-8');

// set global variable
$date_format		= "Y-m-d";
$time_format		= "G:i:s";
$datetime_format 	= $date_format." ".$time_format;
$date_now		= date($date_format, time());
$time_now		= date($time_format, time());
$datetime_now		= date($datetime_format, time());
$nd 			= "<div class=required>(*)</div>";

$core_config['datetime']['date_now'] = $date_now;
$core_config['datetime']['time_now'] = $time_now;
$core_config['datetime']['datetime_now'] = $datetime_now;

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();

?>