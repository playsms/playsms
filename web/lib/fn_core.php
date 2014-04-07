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

defined('_SECURE_') or die('Forbidden');

function core_query_sanitize($var) {
	$var = str_replace("/","",$var);
	$var = str_replace("|","",$var);
	$var = str_replace("\\","",$var);
	$var = str_replace("\"","",$var);
	$var = str_replace('\'',"",$var);
	$var = str_replace("..","",$var);
	$var = strip_tags($var);
	return $var;
}

function core_sanitize_path($var) {
	$var = str_replace("|","",$var);
	$var = str_replace("..","",$var);
	$var = strip_tags($var);
	return $var;
}

function core_hook($c_plugin, $c_function, $c_param=array()) {
	$c_fn = $c_plugin.'_hook_'.$c_function;
	if ($c_plugin && $c_function && function_exists($c_fn)) {
		return call_user_func_array($c_fn, $c_param);
	}
}

/**
 * Call function that hook caller function
 * @global array $core_config
 * @param string $function_name
 * @param array $arguments
 * @return string
 */
function core_call_hook($function_name='', $arguments=array()) {
	global $core_config;
	$ret = NULL;
	if (! $function_name) {
		if (_PHP_VER_ >= 50400) {
			$f = debug_backtrace(0, 2); // PHP 5.4.0 and above
		} else {
			$f = debug_backtrace(); // PHP prior to 5.4.0
		}
		$function_name = $f[1]['function'];
		$arguments = $f[1]['args'];
	}
	$found = FALSE;
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = core_hook($core_config['toolslist'][$c],$function_name,$arguments)) {
			$found = TRUE;
			break;
		}
	}
	if (! $found) {
		for ($c=0;$c<count($core_config['featurelist']);$c++) {
			if ($ret = core_hook($core_config['featurelist'][$c],$function_name,$arguments)) {
				break;
			}
		}
	}
	return $ret;
}

function playsmsd() {
	// tools and feature
	core_call_hook();

	// plugin gateway
	core_hook(core_gateway_get(), 'playsmsd');

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd');
}

function core_str2hex($string)  {
	$hex = '';
	$len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
	}
	return $hex;
}

/*
 * Format text for safe display on the web
 * @param $text
 *    original text
 * @param $len
 *    length of text
 * @return
 *    formatted text
 */
function core_display_text($text, $len=0) {
	$text = strip_tags($text);
	if ($len) {
		$text = substr($text, 0, $len).'..';
	}
	return $text;
}

/*
 * Format $data for safe display on the web
 * @param $data
 *    original $data
 * @return
 *    formatted $data
 */
function core_display_data($data) {
	if (is_array($data)) {
		foreach ($data as $key => $val) {
			$data[$key] = core_display_text($val);
		}
	} else {
		$data = core_display_text($data);
	}
	return $data;
}

/*
 * Get current server date and time in GMT+0
 * @return
 *    current date and time
 */
function core_get_datetime() {
	global $core_config;
	$tz = core_get_timezone();
	$dt = date($core_config['datetime']['format'], time());
	$ret = core_adjust_datetime($dt, $tz);
	return $ret;
}

/*
 * Get current server date in GMT+0
 * @return
 *    current date
 */
function core_get_date() {
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[0];
	return $ret;
}

/*
 * Get current server time in GMT+0
 * @return
 *    current time
 */
function core_get_time() {
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[1];
	return $ret;
}

/*
 * Get timezone
 * @param $username
 *    username or empty for default timezone
 * @return
 *    timezone
 */
function core_get_timezone($username='') {
	global $core_config;
	$ret = '';
	if ($username) {
		$list = dba_search(_DB_PREF_.'_tblUser', 'datetime_timezone', array('username' => $username));
		$ret  = $list[0]['datetime_timezone'];
	}
	if (! $ret) {
		$ret = $core_config['main']['gateway_timezone'];
	}
	return $ret;
}

/*
 * Calculate timezone string into number of seconds offset
 * @param $tz
 *    timezone
 * @return
 *    offset in number of seconds
 */
function core_datetime_offset($tz=0) {
	$n = (int)$tz;
	$m = $n % 100;
	$h = ($n-$m) / 100;
	$num = ($h * 3600) + ($m * 60);
	return ( $num ? $num : 0 );
}

/*
 * Format and adjust date/time from GMT+0 to user's timezone for web display purposes
 * @param $time
 *    date/time
 * @param $tz
 *    timezone
 * @return
 *    formatted date/time with adjusted timezone
 */
function core_display_datetime($time, $tz=0) {
	global $core_config, $user_config;
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (! $tz) {
			if (! ($tz = $user_config['datetime_timezone'])) {
				$tz = $core_config['main']['gateway_timezone'];
			}
		}
		$time = strtotime($time);
		$off = core_datetime_offset($tz);
		// the difference between core_display_datetime() and core_adjust_datetime()
		// core_display_datetime() will set to user's timezone (+offset)
		$ret = $time + $off;
		$ret = date($core_config['datetime']['format'], $ret);
	}
	return $ret;
}

/*
 * Format and adjust date/time to GMT+0 for log or incoming SMS saving purposes
 * @param $time
 *    date/time
 * @param $tz
 *    timezone
 * @return
 *    formatted date/time with adjusted timezone
 */
function core_adjust_datetime($time, $tz=0) {
	global $core_config, $user_config;
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (! $tz) {
			if (! ($tz = $user_config['datetime_timezone'])) {
				$tz = $core_config['main']['gateway_timezone'];
			}
		}
		$time = strtotime($time);
		$off = core_datetime_offset($tz);
		// the difference between core_display_datetime() and core_adjust_datetime()
		// core_adjust_datetime() will set to GTM+0 (-offset)
		$ret = $time - $off;
		$ret = date($core_config['datetime']['format'], $ret);
	}
	return $ret;
}

/**
 * Generates a new string, for example a new password
 *
 */
function core_get_random_string($length = 8) {

    $valid_chars = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ+-*#&@!?";
    $valid_char_len = strlen($valid_chars);
    $result = "";
    for ($i = 0; $i < $length; $i++) {
        $index = mt_rand(0, $valid_char_len - 1);
        $result .= $valid_chars[$index];
    }
    return $result;
}

/**
 * Sanitize username
 *
 */
function core_sanitize_username($username) {
	$username = preg_replace("/[^A-Za-z0-9\.\-\_]/", '', $username);
	return $username;
}

/**
 * Sanitize to alpha-numeric only
 *
 */
function core_sanitize_alphanumeric($text) {
	$text = preg_replace("/[^A-Za-z0-9]/", '', $text);
	return $text;
}

/**
 * Sanitize to alpha only
 *
 */
function core_sanitize_alpha($text) {
	$text = preg_replace("/[^A-Za-z]/", '', $text);
	return $text;
}

/**
 * Sanitize to numeric only
 *
 */
function core_sanitize_numeric($text) {
	$text = preg_replace("/[^0-9]/", '', $text);
	return $text;
}

/**
 * Sanitize SMS sender
 *
 */
function core_sanitize_sender($text) {
	$text = core_sanitize_alphanumeric($text);
	$text = substr($text, 0, 16);
	if (preg_match("/^[A-Za-z]/", $text) == TRUE) {
		$text = substr($text, 0, 11);
	}
	return $text;
}

/**
 * Function: core_net_match()
 * ref: http://stackoverflow.com/a/10422605 (Volomike)
 *
 * This function returns a boolean value.
 * Usage: core_net_match("IP RANGE", "IP ADDRESS")
 */
function core_net_match($network, $ip) {
	$network=trim($network);
	$orig_network = $network;
	$ip = trim($ip);
	if ($ip == $network) {
		//_p("used network ($network) for ($ip)\n");
		return TRUE;
	}
	$network = str_replace(' ', '', $network);
	if (strpos($network, '*') !== FALSE) {
		if (strpos($network, '/') !== FALSE) {
			$asParts = explode('/', $network);
			$network = @ $asParts[0];
		}
		$nCount = substr_count($network, '*');
		$network = str_replace('*', '0', $network);
		if ($nCount == 1) {
			$network .= '/24';
		} else if ($nCount == 2) {
			$network .= '/16';
		} else if ($nCount == 3) {
			$network .= '/8';
		} else if ($nCount > 3) {
			return TRUE; // if *.*.*.*, then all, so matched
		}
	}

	//_p("from original network($orig_network), used network ($network) for ($ip)\n");

	$d = strpos($network, '-');
	if ($d === FALSE) {
		$ip_arr = explode('/', $network);
		if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)) {
			$ip_arr[0].=".0";// Alternate form 194.1.4/24
		}
		$network_long = ip2long($ip_arr[0]);
		$x = ip2long($ip_arr[1]);
		$mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
		$ip_long = ip2long($ip);
		return ($ip_long & $mask) == ($network_long & $mask);
	} else {
		$from = trim(ip2long(substr($network, 0, $d)));
		$to = trim(ip2long(substr($network, $d+1)));
		$ip = ip2long($ip);
		return ($ip>=$from and $ip<=$to);
	}
}


/**
 * Function: core_string_to_gsm()
 * This function encodes an UTF-8 string into GSM 03.38
 * Since UTF-8 is largely ASCII compatible, and GSM 03.38 is somewhat compatible, unnecessary conversions are removed.
 * Specials chars such as € can be encoded by using an escape char \x1B in front of a backwards compatible (similar) char.
 * UTF-8 chars which doesn't have a GSM 03.38 equivalent is replaced with a question mark.
 * UTF-8 continuation bytes (\x08-\xBF) are replaced when encountered in their valid places, but
 * any continuation bytes outside of a valid UTF-8 sequence is not processed.
 * Based on https://github.com/onlinecity/php-smpp
 *
 * @param string $string
 * @return string
 */
function core_string_to_gsm($string)
{
    $dict = array(
        '@' => "\x00", '£' => "\x01", '$' => "\x02", '¥' => "\x03", 'è' => "\x04", 'é' => "\x05", 'ù' => "\x06", 'ì' => "\x07", 'ò' => "\x08", 'Ç' => "\x09", 'Ø' => "\x0B", 'ø' => "\x0C", 'Å' => "\x0E", 'å' => "\x0F",
        'Δ' => "\x10", '_' => "\x11", 'Φ' => "\x12", 'Γ' => "\x13", 'Λ' => "\x14", 'Ω' => "\x15", 'Π' => "\x16", 'Ψ' => "\x17", 'Σ' => "\x18", 'Θ' => "\x19", 'Ξ' => "\x1A", 'Æ' => "\x1C", 'æ' => "\x1D", 'ß' => "\x1E", 'É' => "\x1F",
        // all \x2? removed
        // all \x3? removed
        // all \x4? removed
        'Ä' => "\x5B", 'Ö' => "\x5C", 'Ñ' => "\x5D", 'Ü' => "\x5E", '§' => "\x5F",
        '¿' => "\x60",
        'ä' => "\x7B", 'ö' => "\x7C", 'ñ' => "\x7D", 'ü' => "\x7E", 'à' => "\x7F",
        '^' => "\x1B\x14", '{' => "\x1B\x28", '}' => "\x1B\x29", '\\' => "\x1B\x2F", '[' => "\x1B\x3C", '~' => "\x1B\x3D", ']' => "\x1B\x3E", '|' => "\x1B\x40", '€' => "\x1B\x65"
    );
    $converted = strtr($string, $dict);
    return $converted;
}

/**
 * Function: core_detect_unicode()
 * This function returns an boolean indicating if string needs to be converted to utf
 *  to be send as an SMS
 * @param $text
 *      string to check
 * @return int unicode
 */
function core_detect_unicode($text) {
	$unicode = 0;
    $textgsm=core_string_to_gsm($text);

    $match=preg_match_all('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m',$textgsm,$matches);
    if ($match!==FALSE) {
        if ($match==0) {
            $unicode = 0;
        } else {
            $unicode = 1;
        }
    } else {
        //TODO broken regexp in this case, warn user
    }
	return $unicode;
}


/**
 * Function: array_to_xml()
 * ref: http://stackoverflow.com/a/3289602 (onokazu)
 *
 * This function returns an xml format of an array
 * Usage: core_array_to_xml(ARRAY, SimpleXMLElement OBJECT)
 */
function core_array_to_xml($arr=array(), SimpleXMLElement $xml) {
	foreach ($arr as $k => $v) {
		if (is_numeric($k)) {
			$k = 'item';
		}
		if (is_array($v)) {
			core_array_to_xml($v, $xml->addChild($k));
		} else {
			$xml->addChild($k, $v);
		}
	}
	return $xml;
}

/**
 * XML to array using SimpleXML
 */
function core_xml_to_array($xml) {
	$var = core_object_to_array(simplexml_load_string($xml));
	return $var;
}

/**
 * Object to array
 */
function core_object_to_array($data) {
	if (is_object($data)) {
		$result = array();
		foreach ((array)$data as $key => $value) {
			$result[$key] = core_object_to_array($value);
		}
		return $result;
	}
	return $data;
}

/**
 * Convert array to CSV formatted string
 * @param array $item
 * @return string
 */
function core_csv_format($item) {
	if (is_array($item)) {
		$ret = '';
		for ($i=0;$i<count($item);$i++) {
			foreach ($item[$i] as $key => $val) {
				$val = str_replace('"', "'", $val);
				$ret .= '"'.$val.'",';
			}
			$ret = substr($ret, 0, -1);
			$ret .= "\n";
		}
	}
	return $ret;
}

/**
 * Download content as a file
 * @param string $content
 * @param string $fn
 * @param string $content_type
 */
function core_download($content, $fn='', $content_type='') {
	$fn = ( $fn ? $fn : 'download.txt' );
	$content_type = ( $content_type ? $content_type : 'text/plain' );
	ob_end_clean();
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: '.$content_type);
	header('Content-Disposition: attachment; filename='.$fn);
	_p($content);
	die();
}

/**
 * Get active gateway plugin
 * @global array $core_config
 * @return string
 */
function core_gateway_get() {
	global $core_config;
	return $core_config['main']['gateway_module'];
}

/**
 * Get active language
 * @global array $core_config
 * @return string
 */
function core_lang_get() {
	global $core_config;
	return $core_config['main']['language_module'];
}

/**
 * Get active themes
 * @global array $core_config
 * @return string
 */
function core_themes_get() {
	global $core_config;
	return $core_config['main']['themes_module'];
}

/**
 * Get status of plugin, loaded or not
 * @param integer $uid
 * @param string $plugin_category
 * @param string $plugin_name
 * @return boolean
 */
function core_plugin_get_status($uid, $plugin_category, $plugin_name) {
	$ret = FALSE;
	// check config.php and fn.php
	$plugin_category = core_sanitize_path($plugin_category);
	$plugin_name = core_sanitize_path($plugin_name);
	$fn_cnf = _APPS_PATH_PLUG_.'/'.$plugin_category.'/'.$plugin_name.'/config.php';
	$fn_lib = _APPS_PATH_PLUG_.'/'.$plugin_category.'/'.$plugin_name.'/fn.php';
	if (file_exists($fn_cnf) && $fn_lib) {
		// check plugin_status registry
		$status = registry_search($uid, $plugin_category, $plugin_name, 'enabled');
		// $status = 1 for disabled
		// $status = 2 for enabled
		if ($status == 2) {
			$ret = TRUE;
		}
	}
	return $ret;
}

/**
 * Set status of plugin
 * @param integer $uid
 * @param string $plugin_category
 * @param string $plugin_name
 * @param boolean $plugin_status
 * @return boolean
 */
function core_plugin_set_status($uid, $plugin_category, $plugin_name, $plugin_status) {
	$ret = FALSE;
	$status = core_plugin_get_status($uid, $plugin_category, $plugin_name);
	if ((($status==2) && $plugin_status) || ($status==1 && (! $plugin_status))) {
		$ret = TRUE;
	} else {
		$plugin_status = ( $plugin_status ? 2 : 1 );
		$items = array('enabled' => $plugin_status);
		if (registry_update($uid, $plugin_category, $plugin_name, $items)) {
			$ret = TRUE;
		}
	}
	return $ret;
}

/**
 * Set CSRF token value and form
 * @return array array(value, form)
 */
function core_csrf_set() {
	$ret = array();
	$csrf_token = md5(_PID_.mktime());
	if ($_SESSION['X-CSRF-Token'] = $csrf_token) {
		$ret['value'] = $csrf_token;
		$ret['form'] = '<input type="hidden" name="X-CSRF-Token" value="'.$csrf_token.'">';
	}
	return $ret;
}

/**
 * Set CSRF token
 * @return string
 */
function core_csrf_set_token() {
	$csrf_token = md5(_PID_.mktime());
	if ($_SESSION['X-CSRF-Token'] = $csrf_token) {
		$ret = $csrf_token;
	}
	return $ret;
}

/**
 * Get CSRF token value and form
 * @return array array(value, form)
 */
function core_csrf_get() {
	$ret = array();
	if ($csrf_token = $_SESSION['X-CSRF-Token']) {
		$ret['value'] = $csrf_token;
		$ret['form'] = '<input type="hidden" name="X-CSRF-Token" value="'.$csrf_token.'">';
	}
	return $ret;
}

/**
 * Get CSRF token
 * @return string token
 */
function core_csrf_get_token() {
	if ($csrf_token = $_SESSION['X-CSRF-Token']) {
		$ret = $csrf_token;
	}
	return $ret;
}

/**
 * Validate CSRF token
 * @return boolean
 */
function core_csrf_validate() {
	$submitted_token = $_POST['X-CSRF-Token'];
	$token = core_csrf_get_token();
	if ($token && $submitted_token && ($token == $submitted_token)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Get playSMS version
 * @return string
 */
function core_get_version() {
	$version = registry_search(1, 'core', 'config', 'playsms_version');
	if ($version = $version['core']['config']['playsms_version']) {
		return $version;
	} else {
		return '';
	}	
}

/**
 * Print output
 * @return string
 */
function core_print($content) {
	global $core_config;
	echo $content;
}
