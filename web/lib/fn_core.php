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
defined('_SECURE_') or die('Forbidden');

/**
 * Protection againts SQL injection especially when magic_quotes_gpc set to "Off"
 *
 * @param array $array
 *        $_POST or $_GET
 * @return array
 */
function core_array_addslashes($array) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$new_arr[$key] = addslashes($value);
			}
			if (is_array($value)) {
				$new_arr[$key] = core_array_addslashes($value);
			}
		}
	}
	return $new_arr;
}

/**
 * Protection againts SQL injection especially when magic_quotes_gpc set to "Off"
 *
 * @param mixed $data
 *        simple variable or array of variables
 * @return mixed
 */
function core_addslashes($data) {
	if (is_array($data)) {
		$data = core_array_addslashes($data);
	} else {
		$data = addslashes($data);
	}
	return $data;
}

/**
 * Protection againts XSS
 *
 * @param array $array
 *        $_POST or $_GET
 * @return array
 */
function core_array_htmlspecialchars($array) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$new_arr[$key] = htmlspecialchars($value);
			}
			if (is_array($value)) {
				$new_arr[$key] = core_array_htmlspecialchars($value);
			}
		}
	}
	return $new_arr;
}

/**
 * Protection againts XSS
 *
 * @param mixed $data
 *        simple variable or array of variables
 * @return mixed
 */
function core_htmlspecialchars($data) {
	if (is_array($data)) {
		$data = core_array_htmlspecialchars($data);
	} else {
		$data = htmlspecialchars($data);
	}
	return $data;
}

/**
 * Display untrusted user input, protection againts XSS using HTMLPurifier()
 *
 * @param mixed $data
 *        untrusted inputs
 * @return mixed
 */
function core_sanitize_inputs($data) {
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Attr.EnableID', TRUE);
	$config->set('HTML.SafeObject', TRUE);
	$config->set('HTML.SafeEmbed', TRUE);
	$config->set('Output.FlashCompat', TRUE);
	$config->set('HTML.SafeIframe', TRUE);
	$config->set('URI.SafeIframeRegexp', '%^https://(www.youtube.com/embed/|player.vimeo.com/video/)%');
	$config->set('HTML.Allowed', '*[style|class],p,ol,li,ul,b,u,strike,strong,blockquote,em,br,span,div,a[href|title|target|rel],img[src|alt|title|width|height|hspace|vspace],hr,font,pre,table[cellpadding|cellspacing],tr,td,th,tbody,thead,h1,h2,h3,h4,h5,iframe[src|width|height]');
	$hp = new HTMLPurifier($config);
	
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$ret[$key] = core_display_html($value);
			} else {
				$value = stripslashes($value);
				$value = $hp->purify($value);
				$value = addslashes($value);
				$ret[$key] = $value;
			}
		}
	} else {
		$data = stripslashes($data);
		$data = $hp->purify($data);
		$data = addslashes($data);
		$ret = $data;
	}
	
	return $ret;
}

/**
 * Set the language for the user, if it's no defined just leave it with the default
 *
 * @param string $var_username
 *        Username
 * @return boolean TRUE if valid
 */
function core_setuserlang($username = "") {
	global $core_config;
	$c_lang_module = core_lang_get();
	$db_query = "SELECT language_module FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if (trim($db_row['language_module'])) {
		$c_lang_module = $db_row['language_module'];
	}
	if (defined('LC_MESSAGES')) {
		
		// linux
		setlocale(LC_MESSAGES, $c_lang_module, $c_lang_module . '.utf8', $c_lang_module . '.utf-8', $c_lang_module . '.UTF8', $c_lang_module . '.UTF-8');
	} else {
		
		// windows
		putenv('LC_ALL={' . $c_lang_module . '}');
	}
}

// fixme anton
// enforced to declare function _() for gettext replacement if no PHP gettext extension found
// it is also possible to completely remove gettext and change multi-lang with translation array
if (!function_exists('_')) {

	function _($text) {
		return $text;
	}
}

function core_query_sanitize($var) {
	$var = str_replace("/", "", $var);
	$var = str_replace("|", "", $var);
	$var = str_replace("\\", "", $var);
	$var = str_replace("\"", "", $var);
	$var = str_replace('\'', "", $var);
	$var = str_replace("..", "", $var);
	$var = strip_tags($var);
	return $var;
}

function core_sanitize_path($var) {
	$var = str_replace("|", "", $var);
	$var = str_replace("..", "", $var);
	$var = strip_tags($var);
	return $var;
}

function core_hook($c_plugin, $c_function, $c_param = array()) {
	$c_fn = $c_plugin . '_hook_' . $c_function;
	if ($c_plugin && $c_function && function_exists($c_fn)) {
		return call_user_func_array($c_fn, $c_param);
	}
}

/**
 * Call function that hook caller function
 *
 * @global array $core_config
 * @param string $function_name        
 * @param array $arguments        
 * @return string
 */
function core_call_hook($function_name = '', $arguments = array()) {
	global $core_config;
	$ret = NULL;
	if (!$function_name) {
		if (_PHP_VER_ >= 50400) {
			$f = debug_backtrace(0, 2);
			
			// PHP 5.4.0 and above
		} else {
			$f = debug_backtrace();
			
			// PHP prior to 5.4.0
		}
		$function_name = $f[1]['function'];
		$arguments = $f[1]['args'];
	}
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		if ($ret = core_hook($core_config['featurelist'][$c], $function_name, $arguments)) {
			break;
		}
	}
	return $ret;
}

function playsmsd() {
	
	// plugin feature
	core_call_hook();
	
	// plugin gateway
	core_hook(core_gateway_get(), 'playsmsd');
	
	// plugin themes
	core_hook(core_themes_get(), 'playsmsd');
}

function playsmsd_once($param) {
	
	// plugin feature
	core_call_hook();
	
	// plugin gateway
	core_hook(core_gateway_get(), 'playsmsd_once', array(
		$param 
	));
	
	// plugin themes
	core_hook(core_themes_get(), 'playsmsd_once', array(
		$param 
	));
}

function core_str2hex($string) {
	$hex = '';
	$len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
	}
	return $hex;
}

/**
 * Display untrusted HTML data, protection againts XSS using HTMLPurifier()
 *
 * @param mixed $data
 *        untrusted inputs
 * @return mixed
 */
function core_display_html($data) {
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Attr.EnableID', TRUE);
	$config->set('HTML.SafeObject', TRUE);
	$config->set('HTML.SafeEmbed', TRUE);
	$config->set('Output.FlashCompat', TRUE);
	$config->set('HTML.SafeIframe', TRUE);
	$config->set('URI.SafeIframeRegexp', '%^https://(www.youtube.com/embed/|player.vimeo.com/video/)%');
	$config->set('HTML.Allowed', '*[style|class],p,ol,li,ul,b,u,strike,strong,blockquote,em,br,span,div,a[href|title|target|rel],img[src|alt|title|width|height|hspace|vspace],hr,font,pre,table[cellpadding|cellspacing],tr,td,th,tbody,thead,h1,h2,h3,h4,h5,iframe[src|width|height]');
	$hp = new HTMLPurifier($config);
	
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$ret[$key] = core_display_html($value);
			} else {
				$value = $hp->purify($value);
				$ret[$key] = $value;
			}
		}
	} else {
		$value = $hp->purify($data);
		$ret = $value;
	}
	
	return $ret;
}

/**
 * Format text for safe display on the web
 *
 * @param $text original
 *        text
 * @param $len length
 *        of text
 * @return formatted text
 */
function core_display_text($text, $len = 0) {
	$hp = new HTMLPurifier();
	
	if (is_array($text)) {
		foreach ($text as $item) {
			$ret[] = core_display_text((string) $item, $len);
		}
	} else {
		$text = $hp->purify($text);
		$text = strip_tags($text);
		$text = ($len > 0 ? substr($text, 0, $len) . '..' : $text);
	}
	
	return $text;
}

/*
 * Format $data for safe display on the web @param $data original $data @return formatted $data
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

/**
 * Convert timestamp to datetime in UTC
 *
 * @param $timestamp timestamp        
 * @return current date and time
 */
function core_convert_datetime($timestamp) {
	global $core_config;
	$tz = core_get_timezone();
	$ret = date($core_config['datetime']['format'], $timestamp);
	return $ret;
}

/**
 * Get current server date and time in GMT+0
 *
 * @return current date and time
 */
function core_get_datetime() {
	global $core_config;
	$tz = core_get_timezone();
	$dt = date($core_config['datetime']['format'], time());
	$ret = core_adjust_datetime($dt, $tz);
	return $ret;
}

/**
 * Get current server date in GMT+0
 *
 * @return current date
 */
function core_get_date() {
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[0];
	return $ret;
}

/**
 * Get current server time in GMT+0
 *
 * @return current time
 */
function core_get_time() {
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[1];
	return $ret;
}

/**
 * Get timezone
 *
 * @param $username username
 *        or empty for default timezone
 * @return timezone
 */
function core_get_timezone($username = '') {
	global $core_config;
	$ret = '';
	if ($username) {
		$list = dba_search(_DB_PREF_ . '_tblUser', 'datetime_timezone', array(
			'flag_deleted' => 0,
			'username' => $username 
		));
		$ret = $list[0]['datetime_timezone'];
	}
	if (!$ret) {
		$ret = $core_config['main']['gateway_timezone'];
	}
	return $ret;
}

/**
 * Calculate timezone string into number of seconds offset
 *
 * @param $tz timezone        
 * @return offset in number of seconds
 */
function core_datetime_offset($tz = 0) {
	$n = (int) $tz;
	$m = $n % 100;
	$h = ($n - $m) / 100;
	$num = ($h * 3600) + ($m * 60);
	return ($num ? $num : 0);
}

/**
 * Format and adjust date/time from GMT+0 to user's timezone for web display purposes
 *
 * @param $time date/time        
 * @param $tz timezone        
 * @return formatted date/time with adjusted timezone
 */
function core_display_datetime($time, $tz = 0) {
	global $core_config, $user_config;
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (!$tz) {
			if (!($tz = $user_config['datetime_timezone'])) {
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

/**
 * Format text to proper date/time format
 *
 * @param string $text        
 * @return string
 */
function core_format_datetime($text) {
	global $core_config;
	
	$ts = strtotime($text);
	$ret = date($core_config['datetime']['format'], $ts);
	
	return $ret;
}

/**
 * Format and adjust date/time to GMT+0 for log or incoming SMS saving purposes
 *
 * @param $time date/time        
 * @param $tz timezone        
 * @return formatted date/time with adjusted timezone
 */
function core_adjust_datetime($time, $tz = 0) {
	global $core_config, $user_config;
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (!$tz) {
			if (!($tz = $user_config['datetime_timezone'])) {
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
 */
function core_get_random_string($length = 8, $valid_chars = '') {
	$valid_chars = str_replace(' ', '', $valid_chars);
	if (!$valid_chars) {
		$valid_chars = "abcdefghjkmnpqrstuxyvwzABCDEFGHJKLMNPQRSTUXYVWZ@#$%&";
	}
	
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
 */
function core_sanitize_username($username) {
	$username = preg_replace("/[^A-Za-z0-9\.\-\_]/", '', $username);
	return $username;
}

/**
 * Sanitize to alpha-numeric only
 */
function core_sanitize_alphanumeric($text) {
	$text = preg_replace("/[^A-Za-z0-9]/", '', $text);
	return $text;
}

/**
 * Sanitize to alpha only
 */
function core_sanitize_alpha($text) {
	$text = preg_replace("/[^A-Za-z]/", '', $text);
	return $text;
}

/**
 * Sanitize to numeric only
 */
function core_sanitize_numeric($text) {
	$text = preg_replace("/[^0-9]/", '', $text);
	return $text;
}

/**
 * Sanitize SMS sender
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
 * Sanitize SMS footer
 */
function core_sanitize_footer($text) {
	$text = str_replace('"', "'", $text);
	if (strlen($text) > 30) {
		$text = substr($text, 0, 30);
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
	$network = trim($network);
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
			$network = @$asParts[0];
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
			return TRUE;
			
			// if *.*.*.*, then all, so matched
		}
	}
	
	//_p("from original network($orig_network), used network ($network) for ($ip)\n");
	

	$d = strpos($network, '-');
	if ($d === FALSE) {
		$ip_arr = explode('/', $network);
		if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)) {
			$ip_arr[0] .= ".0";
			
			// Alternate form 194.1.4/24
		}
		$network_long = ip2long($ip_arr[0]);
		$x = ip2long($ip_arr[1]);
		$mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
		$ip_long = ip2long($ip);
		return ($ip_long & $mask) == ($network_long & $mask);
	} else {
		$from = trim(ip2long(substr($network, 0, $d)));
		$to = trim(ip2long(substr($network, $d + 1)));
		$ip = ip2long($ip);
		return ($ip >= $from and $ip <= $to);
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
function core_string_to_gsm($string) {
	$dict = array(
		'@' => "\x00",
		'£' => "\x01",
		'$' => "\x02",
		'¥' => "\x03",
		'è' => "\x04",
		'é' => "\x05",
		'ù' => "\x06",
		'ì' => "\x07",
		'ò' => "\x08",
		'Ç' => "\x09",
		'Ø' => "\x0B",
		'ø' => "\x0C",
		'Å' => "\x0E",
		'å' => "\x0F",
		'Δ' => "\x10",
		'_' => "\x11",
		'Φ' => "\x12",
		'Γ' => "\x13",
		'Λ' => "\x14",
		'Ω' => "\x15",
		'Π' => "\x16",
		'Ψ' => "\x17",
		'Σ' => "\x18",
		'Θ' => "\x19",
		'Ξ' => "\x1A",
		'Æ' => "\x1C",
		'æ' => "\x1D",
		'ß' => "\x1E",
		'É' => "\x1F",
		
		// all \x2? removed
		// all \x3? removed
		// all \x4? removed
		'Ä' => "\x5B",
		'Ö' => "\x5C",
		'Ñ' => "\x5D",
		'Ü' => "\x5E",
		'§' => "\x5F",
		'¿' => "\x60",
		'ä' => "\x7B",
		'ö' => "\x7C",
		'ñ' => "\x7D",
		'ü' => "\x7E",
		'à' => "\x7F",
		'^' => "\x1B\x14",
		'{' => "\x1B\x28",
		'}' => "\x1B\x29",
		'\\' => "\x1B\x2F",
		'[' => "\x1B\x3C",
		'~' => "\x1B\x3D",
		']' => "\x1B\x3E",
		'|' => "\x1B\x40",
		'€' => "\x1B\x65" 
	);
	
	// '
	$converted = strtr($string, $dict);
	return $converted;
}

/**
 * Function: core_detect_unicode()
 * This function returns an boolean indicating if string needs to be converted to utf
 * to be send as an SMS
 *
 * @param $text string
 *        to check
 * @return int unicode
 */
function core_detect_unicode($text) {
	$unicode = 0;
	$textgsm = core_string_to_gsm($text);
	
	$match = preg_match_all('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m', $textgsm, $matches);
	if ($match !== FALSE) {
		if ($match == 0) {
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
function core_array_to_xml($arr = array(), SimpleXMLElement $xml) {
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
	$loaded = simplexml_load_string($xml);
	$json = json_encode($loaded);
	$var = json_decode($json, TRUE);
	return $var;
}

/**
 * Object to array
 */
function core_object_to_array($data) {
	if (is_object($data)) {
		$result = array();
		foreach ((array) $data as $key => $value) {
			$result[$key] = core_object_to_array($value);
		}
		return $result;
	}
	return $data;
}

/**
 * Convert array to CSV formatted string
 *
 * @param array $item        
 * @return string
 */
function core_csv_format($item) {
	$ret = '';
	
	foreach ($item as $row) {
		
		$entry = '';
		foreach ($row as $field) {
			
			$field = str_replace('"', "'", $field);
			$entry .= '"' . $field . '",';
		}
		$entry = substr($entry, 0, -1);
		
		$ret .= $entry . "\n";
	}
	
	return $ret;
}

/**
 * Download content as a file
 *
 * @param string $content        
 * @param string $fn        
 * @param string $content_type        
 * @param string $charset        
 */
function core_download($content, $fn = '', $content_type = '', $charset = '') {
	$fn = ($fn ? $fn : 'download.txt');
	$content_type = (trim($content_type) ? strtolower(trim($content_type)) : 'text/plain');
	$charset = strtolower(trim($charset));
	
	// fixme anton
	// seems to be good for Arabic, Chinese and Hebrew letters
	// but I'm not sure if this is the right way to do it though
	if ($content_type == 'text/csv') {
		// $charset = 'windows-1255';
		$charset = 'utf-8';
	}
	
	ob_end_clean();
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	if ($charset) {
		header('Content-Type: ' . $content_type . '; charset=' . $charset);
	} else {
		header('Content-Type: ' . $content_type);
	}
	header('Content-Disposition: attachment; filename=' . $fn);
	_p($content);
	die();
}

/**
 * Get default SMSC
 *
 * @global array $core_config
 * @return string
 */
function core_smsc_get() {
	global $core_config;
	
	$ret = core_call_hook();
	if (!$ret) {
		return $core_config['main']['gateway_module'];
	}
	
	return $ret;
}

/**
 * Get default gateway based on default SMSC
 *
 * @global array $core_config
 * @return string
 */
function core_gateway_get() {
	global $core_config;
	
	$ret = core_call_hook();
	if (!$ret) {
		$smsc = core_smsc_get();
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateway = $smsc_data['gateway'];
		return $gateway;
	}
	
	return $ret;
}

/**
 * Get active language
 *
 * @global array $core_config
 * @return string
 */
function core_lang_get() {
	global $core_config, $user_config;
	
	$ret = core_call_hook();
	if (!$ret) {
		return ($user_config['language_module'] ? $user_config['language_module'] : $core_config['main']['language_module']);
	}
	
	return $ret;
}

/**
 * Get active themes
 *
 * @global array $core_config
 * @return string
 */
function core_themes_get() {
	global $core_config;
	
	$ret = core_call_hook();
	if (!$ret) {
		return $core_config['main']['themes_module'];
	}
	
	return $ret;
}

/**
 * Get status of plugin, loaded or not
 *
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
	$fn_cnf = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/config.php';
	$fn_lib = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/fn.php';
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
 *
 * @param integer $uid        
 * @param string $plugin_category        
 * @param string $plugin_name        
 * @param boolean $plugin_status        
 * @return boolean
 */
function core_plugin_set_status($uid, $plugin_category, $plugin_name, $plugin_status) {
	$ret = FALSE;
	$status = core_plugin_get_status($uid, $plugin_category, $plugin_name);
	if ((($status == 2) && $plugin_status) || ($status == 1 && (!$plugin_status))) {
		$ret = TRUE;
	} else {
		$plugin_status = ($plugin_status ? 2 : 1);
		$items = array(
			'enabled' => $plugin_status 
		);
		if (registry_update($uid, $plugin_category, $plugin_name, $items)) {
			$ret = TRUE;
		}
	}
	return $ret;
}

/**
 * Set CSRF token value and form
 *
 * @return array array(value, form)
 */
function core_csrf_set() {
	$ret = array();
	$csrf_token = md5(_PID_ . mktime());
	if ($_SESSION['X-CSRF-Token'] = $csrf_token) {
		$ret['value'] = $csrf_token;
		$ret['form'] = '<input type="hidden" name="X-CSRF-Token" value="' . $csrf_token . '">';
	}
	
	//_log('token:'.$csrf_token, 3, 'core_csrf_set');
	return $ret;
}

/**
 * Set CSRF token
 *
 * @return string
 */
function core_csrf_set_token() {
	$csrf_token = md5(_PID_ . mktime());
	if ($_SESSION['X-CSRF-Token'] = $csrf_token) {
		$ret = $csrf_token;
	}
	
	//_log('token:'.$csrf_token, 3, 'core_csrf_set_token');
	return $ret;
}

/**
 * Get CSRF token value and form
 *
 * @return array array(value, form)
 */
function core_csrf_get() {
	$ret = array();
	if ($csrf_token = $_SESSION['X-CSRF-Token']) {
		$ret['value'] = $csrf_token;
		$ret['form'] = '<input type="hidden" name="X-CSRF-Token" value="' . $csrf_token . '">';
	}
	
	//_log('token:'.$csrf_token, 3, 'core_csrf_get');
	return $ret;
}

/**
 * Get CSRF token
 *
 * @return string token
 */
function core_csrf_get_token() {
	if ($csrf_token = $_SESSION['X-CSRF-Token']) {
		$ret = $csrf_token;
	}
	
	//_log('token:'.$csrf_token, 3, 'core_csrf_get_token');
	return $ret;
}

/**
 * Validate CSRF token
 *
 * @return boolean
 */
function core_csrf_validate() {
	$submitted_token = $_POST['X-CSRF-Token'];
	$token = core_csrf_get_token();
	
	//_log('token:'.$token.' submitted_token:'.$submitted_token, 3, 'core_csrf_validate');
	if ($token && $submitted_token && ($token == $submitted_token)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Get playSMS version
 *
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
 *
 * @return string
 */
function core_print($content) {
	global $core_config;
	echo $content;
}

/**
 * Check playSMS daemon timer
 *
 * Usage:
 * if (! core_playsmsd_timer(40)) {
 * return;
 * }
 *
 * // do below commands every 40 seconds
 * ...
 * ...
 *
 * @param integer $period
 *        Period between last event and now (in second)
 * @return boolean TRUE for period passed
 */
function core_playsmsd_timer($period = 60) {
	
	// default period is 60 seconds
	$period = ((int) $period <= 0 ? 60 : (int) $period);
	
	$now = mktime();
	$next = floor(($now / $period)) * $period + $period;
	if (($now + 1) < $next) {
		
		// it is not the time yet
		return FALSE;
	} else {
		
		// its passed the timer period
		return TRUE;
	}
}

/**
 * Get mobile format for matching purposes
 *
 * @param string $mobile        
 * @return mixed
 */
function core_mobile_matcher_format($mobile) {
	// sanitize for mobile numbers only
	$c_mobile = sendsms_getvalidnumber($mobile);
	
	if (strlen($c_mobile) >= 6) {
		// remove +
		$c_mobile = str_replace('+', '', $c_mobile);
		
		// remove first 3 digits if phone number length more than 7
		if (strlen($c_mobile) > 7) {
			$c_mobile = substr($c_mobile, 3);
		}
		
		$mobile = $c_mobile;
	}
	
	return $mobile;
}

/**
 * Get last submitted $_POST data
 *
 * @param string $key        
 * @return mixed
 */
function core_last_post_get($key = '') {
	$ret = '';
	
	$key = trim($key);
	if ($key) {
		$ret = $_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_ . _INC_))][$key];
	} else {
		$ret = $_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_ . _INC_))];
	}
	
	return $ret;
}

/**
 * Include composer based packages
 */
if (file_exists(_APPS_PATH_LIBS_ . '/composer/vendor/autoload.php')) {
	include_once _APPS_PATH_LIBS_ . '/composer/vendor/autoload.php';
} else {
	die(_('FATAL ERROR') . ' : ' . _('Unable to find composer files') . ' ' . _('Please run composer.phar update'));
	exit();
}

/**
 * Include core functions on plugin core
 */

$pc = 'core';

$dir = _APPS_PATH_PLUG_ . '/' . $pc . '/';
unset($core_config[$pc . 'list']);
unset($tmp_core_config[$pc . 'list']);
$fd = opendir($dir);
$pc_names = array();
while (false !== ($pl_name = readdir($fd))) {
	
	// plugin's dir prefixed with dot or underscore will not be loaded
	if (substr($pl_name, 0, 1) != "." && substr($pl_name, 0, 1) != "_") {
		$pc_names[] = $pl_name;
	}
}
closedir();

sort($pc_names);
for ($j = 0; $j < count($pc_names); $j++) {
	if (is_dir($dir . $pc_names[$j])) {
		$core_config[$pc . 'list'][] = $pc_names[$j];
	}
}

foreach ($core_config[$pc . 'list'] as $pl) {
	$c_fn1 = $dir . '/' . $pl . '/config.php';
	$c_fn2 = $dir . '/' . $pl . '/fn.php';
	if (file_exists($c_fn1) && file_exists($c_fn2)) {
		if (function_exists('bindtextdomain') && file_exists($dir . '/' . $pl . '/language')) {
			bindtextdomain('messages', $pl_dir . '/language/');
			bind_textdomain_codeset('messages', 'UTF-8');
			textdomain('messages');
		}
		
		// config.php
		include $c_fn1;
		
		// fn.php
		include_once $c_fn2;
	}
}

// load shortcuts
include_once $core_config['apps_path']['libs'] . "/fn_shortcuts.php";
