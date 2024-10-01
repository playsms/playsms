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
 * Replacement for addslashes()
 *
 * @param string|array $data simple variable or array of variables
 * @return string|array
 */
function core_addslashes($data)
{
	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_addslashes($val);
		}
	} else {
		$data = addslashes($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Replacement for stripslashes()
 *
 * @param string|array $data simple variable or array of variables
 * @return string|array
 */
function core_stripslashes($data)
{
	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_stripslashes($val);
		}
	} else {
		$data = stripslashes($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Replacement for htmlspecialchars()
 *
 * @param string|array $data simple variable or array of variables
 * @return string|array
 */
function core_htmlspecialchars($data)
{
	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_htmlspecialchars($val);
		}
	} else {
		$data = htmlspecialchars($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Replacement for htmlspecialchars_decode()
 *
 * @param string|array $data simple variable or array of variables
 * @return string|array
 */
function core_htmlspecialchars_decode($data)
{
	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_htmlspecialchars_decode($val);
		}
	} else {
		$data = htmlspecialchars_decode($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Replacement for trim()
 *
 * @param string|array $data simple variable or array of variables
 * @return string|array
 */
function core_trim($data)
{
	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_trim($val);
		}
	} else {
		$data = trim($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Replacement for hash()
 * 
 * @param string $string text
 * @param string $algo selected hashing algorithm. see: https://www.php.net/manual/en/function.hash-algos.php
 * @return null|string hashed text
 */
function core_hash($string, $algo = 'sha256')
{
	$algo = trim($algo) ? strtolower(trim($algo)) : 'sha256';

	if ($algo == 'md5' || $algo == 'sha1' || $algo == 'sha256') {

		return hash($algo, $string);
	}

	foreach ( hash_algos() as $supported_algo ) {
		if ($algo && $algo == $supported_algo) {

			return hash($algo, $string);
		}
	}

	return null;
}

/**
 * Set the language for the user
 *
 * @param string $username username
 */
function core_setuserlang($username = '')
{
	global $core_config;

	$db_query = "SELECT language_module FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username=?";
	$db_result = dba_query($db_query, [$username]);
	$db_row = dba_fetch_array($db_result);
	$lang = isset($db_row['language_module']) && trim($db_row['language_module']) ? trim($db_row['language_module']) : core_lang_get();

	// alphanumeric and underscore only
	$lang = preg_replace('/[\W]+/', '', $lang);

	if (defined('LC_MESSAGES')) {

		// linux
		setlocale(LC_MESSAGES, $lang, $lang . '.utf8', $lang . '.utf-8', $lang . '.UTF8', $lang . '.UTF-8');
	} else {

		// windows
		putenv('LC_ALL={' . $lang . '}');
	}
}

/**
 * Run hook function if exists
 * 
 * @param string $plugin plugin
 * @param string $function function
 * @param array $param function arguments
 * @return mixed
 */
function core_hook($plugin, $function, $param = [])
{
	$fn = $plugin . '_hook_' . $function;
	if ($plugin && $function && function_exists($fn)) {

		return call_user_func_array($fn, $param);
	}

	return null;
}

/**
 * Call function that hook caller function.
 * Caller function must be feature plugin.
 *
 * @global array $core_config
 * @param string $function        
 * @param array $param        
 * @return mixed
 */
function core_call_hook($function = '', $param = [])
{
	global $core_config;

	$ret = null;

	if (!$function) {
		if (_PHP_VER_ >= 50400) {
			$f = debug_backtrace(0, 2);

			// PHP 5.4.0 and above
		} else {
			$f = debug_backtrace();

			// PHP prior to 5.4.0
		}
		$function = $f[1]['function'];
		$param = $f[1]['args'];
	}

	if (isset($core_config['plugins']['list']['feature']) && is_array($core_config['plugins']['list']['feature'])) {
		foreach ( $core_config['plugins']['list']['feature'] as $plugin ) {
			if ($plugin && $ret = core_hook($plugin, $function, $param)) {

				break;
			}
		}
	}

	return $ret;
}

/**
 * Call playsmsd hook
 * 
 * @return void
 */
function playsmsd()
{
	$gateways = [];

	// plugin feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	$gateways = array_unique($gateways);
	foreach ( $gateways as $gateway ) {
		core_hook($gateway, 'playsmsd');
	}

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd');
}

/**
 * Call playsmsd_once hook
 * 
 * @param mixed $param
 * @return void
 */
function playsmsd_once($param)
{
	$gateways = [];

	$param = isset($param) ? $param : [];
	$param = is_array($param) ? $param : [$param];

	// plugin feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	$gateways = array_unique($gateways);
	foreach ( $gateways as $gateway ) {
		core_hook($gateway, 'playsmsd_once', $param);
	}

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd_once', $param);
}

/**
 * String to hex string
 * 
 * @param string $string
 * @return string
 */
function core_str2hex($string)
{
	$hex = '';

	$len = strlen(trim($string));
	for ($i = 0; $i < $len; $i++) {
		$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
	}

	return $hex;
}

/**
 * Purify input
 * 
 * @param string|array $data input
 * @param string $type text or HTML
 * @return string|array purified input
 */
function core_purify($data, $type = 'text')
{
	// type of output, text or other
	$type = strtolower(trim($type));

	if (is_array($data)) {
		$ret = [];
		foreach ( $data as $key => $val ) {
			$ret[$key] = core_purify($val, $type);
		}
	} else {

		// trim
		$data = trim((string) $data);

		// stripslashes
		$data = stripslashes($data);

		// decode HTML special chars
		$data = htmlspecialchars_decode($data);

		// remove php tags
		$data = str_ireplace('<?php', '', $data);
		$data = str_ireplace('<?', '', $data);
		$data = str_ireplace('?>', '', $data);
		$data = str_ireplace('`', '', $data);

		// purify it
		$config = HTMLPurifier_Config::createDefault();

		// if folder exists and writable then setup cache
		$cache_dir = _APPS_PATH_STORAGE_ . '/cache/htmlpurifier';
		if (file_exists($cache_dir) && is_writable($cache_dir)) {
			$config->set('Cache.DefinitionImpl', 'Serializer');
			$config->set('Cache.SerializerPath', $cache_dir);
		} else {
			$config->set('Cache.DefinitionImpl', null);
			$config->set('Cache.SerializerPath', null);
		}

		if ($type == 'text') {

			// if type is text then do not allow any HTML tags
			// for non-text type default purifier config will be used
			$config->set('HTML.AllowedElements', '');
			$config->set('HTML.AllowedAttributes', '');
		}

		$hp = new HTMLPurifier($config);

		$data = $hp->purify($data);

		$ret = $data;
	}

	return $ret;
}

/**
 * Format input for safe HTML display on the web
 *
 * @param string|array $data HTML input
 * @return string|array safe HTML
 */
function core_display_html($data)
{
	$data = core_purify($data, 'html');

	return $data;
}

/**
 * Format text for safe display on the web
 *
 * @param string|array $data original text
 * @param int $len length of text
 * @return string|array safe text
 */
function core_display_text($data, $len = 0)
{
	$data = core_purify($data, 'text');

	$data = $len > 0 ? substr($data, 0, $len - 1) : $data;

	$data = core_htmlspecialchars($data);

	return $data;
}

/**
 * Format $data for safe display on the web without purification
 * Assumed that $data already purified by other means
 * 
 * @param string|array $data original $data
 * @return string|array formatted $data
 */
function core_display_data($data)
{
	$data = core_stripslashes($data);

	$data = core_htmlspecialchars($data);

	return $data;
}

/**
 * Fetch $_POST, $_GET, $_COOKIE or $_REQUEST safe HTML value for selected key
 * 
 * @param string $key
 * @return mixed
 */
function core_safe_html($key, $type = 'post')
{
	$type = strtolower(trim($type));

	switch ($type) {
		case 'post':
			return isset($_POST[_SAFE_HTML_KEY_]) && isset($_POST[_SAFE_HTML_KEY_][$key]) ? $_POST[_SAFE_HTML_KEY_][$key] : null;
		case 'get':
			return isset($_GET[_SAFE_HTML_KEY_]) && isset($_GET[_SAFE_HTML_KEY_][$key]) ? $_GET[_SAFE_HTML_KEY_][$key] : null;
		case 'cookie':
			return isset($_COOKIE[_SAFE_HTML_KEY_]) && isset($_COOKIE[_SAFE_HTML_KEY_][$key]) ? $_COOKIE[_SAFE_HTML_KEY_][$key] : null;
		case 'request':
			return isset($_REQUEST[_SAFE_HTML_KEY_]) && isset($_REQUEST[_SAFE_HTML_KEY_][$key]) ? $_REQUEST[_SAFE_HTML_KEY_][$key] : null;
		default:
			return null;
	}
}

/**
 * Fetch $_POST safe HTML value for selected key
 * 
 * @param string $key
 * @return mixed
 */
function core_safe_html_post($key)
{
	return core_safe_html($key, 'post');
}

/**
 * Convert timestamp to datetime in UTC
 *
 * @param $timestamp timestamp        
 * @return string current date and time
 */
function core_convert_datetime($timestamp)
{
	global $core_config;
	$tz = core_get_timezone();
	$ret = date($core_config['datetime']['format'], $timestamp);
	return $ret;
}

/**
 * Get current server date and time in GMT+0
 *
 * @return string current date and time
 */
function core_get_datetime()
{
	global $core_config;
	$tz = core_get_timezone();
	$dt = date($core_config['datetime']['format'], time());
	$ret = core_adjust_datetime($dt, $tz);
	return $ret;
}

/**
 * Get current server date in GMT+0
 *
 * @return string current date
 */
function core_get_date()
{
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[0];
	return $ret;
}

/**
 * Get current server time in GMT+0
 *
 * @return string current time
 */
function core_get_time()
{
	$ret = core_get_datetime();
	$arr = explode(' ', $ret);
	$ret = $arr[1];
	return $ret;
}

/**
 * Get timezone
 *
 * @param $username username or empty for default timezone
 * @return string timezone
 */
function core_get_timezone($username = '')
{
	global $core_config;

	$ret = '';

	if ($username) {
		$list = dba_search(
			_DB_PREF_ . '_tblUser',
			'datetime_timezone',
			[
				'flag_deleted' => 0,
				'username' => $username
			]
		);
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
 * @return int offset in number of seconds
 */
function core_datetime_offset($tz = 0)
{
	$n = (int) $tz;
	$m = $n % 100;
	$h = ($n - $m) / 100;
	$num = ($h * 3600) + ($m * 60);

	return (int) $num >= 0 ? (int) $num : 0;
}

/**
 * Format and adjust date/time from GMT+0 to user's timezone for web display purposes
 *
 * @param $time date/time        
 * @param $tz timezone        
 * @return string formatted date/time with adjusted timezone
 */
function core_display_datetime($time, $tz = 0)
{
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
function core_format_datetime($text)
{
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
 * @return string formatted date/time with adjusted timezone
 */
function core_adjust_datetime($time, $tz = 0)
{
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
 * Format float to proper credit format
 *
 * @param float|int|string $credit credit
 * @return string
 */
function core_display_credit($credit)
{
	return number_format((float) $credit, 2, '.', '');
}

/**
 * Generate new random string
 * 
 * @param int $length length of string, default 16, min. 4
 * @param string $chars string of valid characters, min. 10 chars
 * @return string
 */
function core_get_random_string($length = 16, $chars = '')
{
	$result = '';

	$default_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()';

	$chars = preg_replace('/\s+/', '', trim($chars));
	$chars = strlen($chars) >= 10 ? $chars : $default_chars;

	$char_len = strlen($chars);

	$length = (int) $length >= 4 ? (int) $length : 4;

	for ($i = 0; $i < $length; $i++) {
		if (function_exists('random_int')) {
			$index = random_int(0, $char_len - 1);
		} else {
			$index = mt_rand(0, $char_len - 1);
		}

		$result .= $chars[$index];
	}

	return $result;
}

/**
 * Generate random hex string
 * 
 * @param string $algo selected hashing algorithm. see: https://www.php.net/manual/en/function.hash-algos.php
 * @return string
 */
function core_random($algo = 'sha256')
{
	$result = '';

	$algo = trim($algo) ? strtolower(trim($algo)) : 'sha256';
	$len = 32;

	if (function_exists('random_bytes')) {
		$result = bin2hex(random_bytes($len));
	} else if (function_exists('openssl_random_pseudo_bytes')) {
		$result = bin2hex(openssl_random_pseudo_bytes($len));
	} else {
		$result = core_get_random_string($len);
	}

	$result = core_hash($result, $algo);

	return $result;
}

/**
 * Sanitize untrusted user input from web
 *
 * @param string|array $data untrusted user input
 * @param string $type text or html format
 * @return string|array safe user input
 */
function core_sanitize_inputs($data, $type = 'text')
{
	$type = strtolower(trim($type));

	$data = core_purify($data, $type);

	// fixme anton - only until every queries using PDO
	$data = core_addslashes($data);

	return $data;
}

/**
 * Sanitize file/folder path
 * 
 * @param string $string
 * @return string
 */
function core_sanitize_path($string)
{
	$string = trim($string);

	$string = str_replace("|", "", $string);
	$string = str_replace(">", "", $string);
	$string = str_replace("<", "", $string);
	$string = str_replace("..", "", $string);

	return $string;
}

/**
 * Sanitize file/folder name
 * 
 * @param string $string file/folder name
 * @return string
 */
function core_sanitize_filename($string)
{
	$string = core_sanitize_path($string);

	$string = trim(preg_replace('/[^\p{L}\p{N}\s\.\-_]+/u', '', $string));

	return $string;
}

/**
 * Sanitize username
 * 
 * @param string $string username
 * @return string
 */
function core_sanitize_username($string)
{
	$string = strtolower(trim($string));

	$string = preg_replace("/[^a-z\d\-\._]/i", '', $string);

	return $string;
}

/**
 * Sanitize to alphanumeric text
 * 
 * @param string $string text
 * @return string alphanumeric text
 */
function core_sanitize_alphanumeric($string)
{
	$string = trim($string);

	$string = preg_replace('/[^\p{L}\p{N}]+/u', '', $string);

	return $string;
}

/**
 * Sanitize to alpha text
 * 
 * @param string $string text
 * @return string alpha text
 */
function core_sanitize_alpha($string)
{
	$string = trim($string);

	$string = preg_replace('/[^\p{L}]+/u', '', trim($string));

	return $string;
}

/**
 * Sanitize to numeric text
 * 
 * @param string $string text
 * @return string numeric text
 */
function core_sanitize_numeric($string)
{
	$string = trim($string);

	$string = preg_replace('/[^\p{N}]+/u', '', trim($string));

	return $string;
}

/**
 * Sanitize HTML and PHP tags
 * 
 * @param string $string text
 * @return string text without HTML and PHP tags
 */
function core_sanitize_string($string)
{
	$string = trim($string);

	$string = stripslashes($string);
	$string = htmlspecialchars_decode($string);
	$string = strip_tags($string);
	$string = preg_replace('/[^\p{L}\p{N}\s\.\-\[\]():=,_@#]+/u', '', $string);

	return $string;
}

/**
 * Sanitize SMS sender
 * 
 * @param string $string sender ID
 * @return string
 */
function core_sanitize_sender($string)
{
	$string = trim($string);

	// allows alphanumeric, space, plus, dash, underscore
	$string = preg_replace('/[^\p{L}\p{N}\s\+\-_]+/u', '', $string);
	$string = substr($string, 0, 16);

	// check if contains alpha
	if (preg_match('/[\p{L}]+/u', $string)) {
		$string = substr($string, 0, 11);
	}

	return $string;
}

/**
 * Sanitize mobile phone number
 * 
 * @param string $string mobile phone number
 * @return string
 */
function core_sanitize_mobile($string)
{
	$string = trim($string);

	$string = preg_replace('/[^\d\+]+/', '', $string);
	$string = substr($string, 0, 15);

	return $string;
}

/**
 * Sanitize SMS footer
 * 
 * @param string $string SMS footer text
 * @return string
 */
function core_sanitize_footer($string)
{
	$string = trim($string);

	$string = preg_replace('/[^\p{L}\p{N}\s\+\-_@]+/u', '', $string);
	$string = substr($string, 0, 30);

	return $string;
}

/**
 * Sanitize SMS keyword
 * 
 * @param string $string SMS keyword
 * @return string
 */
function core_sanitize_keyword($string)
{
	$string = strtoupper(trim($string));

	$string = preg_replace('/[^\p{L}\p{N}\.\-_@]+/u', '', $string);

	return $string;
}

/**
 * Function: core_net_match()
 * ref: https://github.com/mlocati/ip-lib
 *
 * This function returns a bool value.
 * Usage: core_net_match("IP RANGE", "IP ADDRESS")
 * 
 * @param string $network Network
 * @param string $ip IP to be checked within network
 * @param bool $quiet true for no logging, default is false
 * @return bool
 */
function core_net_match($network, $ip, $quiet = false)
{
	$network = trim($network);
	$ip = trim($ip);

	if ($network && $ip && class_exists('\IPLib\Factory')) {

		// don't match with network that starts with asterisk or 0
		// to prevent matches with *.*.*.* or 0.0.0.0
		if (preg_match('/^[\*0]/', $network)) {
			_log('match all range is not allowed network:' . $network . ' ip:' . $ip, 2, 'core_net_match', $quiet);

			return false;
		}

		try {
			$address = \IPLib\Factory::parseAddressString($ip);
			$range = \IPLib\Factory::parseRangeString($network);

			if (!is_object($address)) {
				_log('invalid remote network:' . $network . ' ip:' . $ip, 3, 'core_net_match', $quiet);

				return false;
			}

			if (!is_object($range)) {
				_log('invalid range network:' . $network . ' ip:' . $ip, 3, 'core_net_match', $quiet);

				return false;
			}

			if ($address->matches($range)) {
				_log('found match remote is in range network:' . $network . ' ip:' . $ip, 3, 'core_net_match', $quiet);

				return true;
			} else {
				_log('match not found remote is not in range network:' . $network . ' ip:' . $ip, 3, 'core_net_match', $quiet);

				return false;
			}
		} catch (Exception $e) {
			_log('exception network:' . $network . ' ip:' . $ip . ' error:' . $e->getMessage(), 2, 'core_net_match', $quiet);

			return false;
		}
	} else {

		return false;
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
 * This function returns an bool indicating if string needs to be converted to utf
 * to be send as an SMS
 *
 * @param $text string to check
 * @return int unicode
 */
function core_detect_unicode($text)
{
	$unicode = 0;

	$textgsm = core_string_to_gsm($text);

	$match = preg_match_all('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m', $textgsm, $matches);
	if ($match !== false) {
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
 * SMS strlen() based on unicode status
 *
 * @param string $text        
 * @param string $encoding        
 * @return int length of text
 */
function core_smslen($text, $encoding = '')
{
	if (function_exists('mb_strlen') && core_detect_unicode($text)) {
		if ($encoding = trim($encoding)) {
			$len = mb_strlen($text, $encoding);
		} else {
			$len = mb_strlen($text, 'UTF-8');
		}
	} else if (core_detect_unicode($text)) {
		// $len = strlen(utf8_decode($text)); -- deprecated in PHP 8.2
		$len = strlen(mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'));
	} else {
		$len = strlen($text);
	}

	return (int) $len;
}

/**
 * Function: array_to_xml()
 * ref: http://stackoverflow.com/a/3289602 (onokazu)
 *
 * This function returns an xml format of an array
 * Usage: core_array_to_xml(ARRAY, SimpleXMLElement OBJECT)
 * @param array $arr
 * @param SimpleXMLElement $xml
 * @return SimpleXMLElement
 */
function core_array_to_xml($arr, SimpleXMLElement $xml)
{
	foreach ( $arr as $k => $v ) {
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
function core_xml_to_array($xml)
{
	$loaded = simplexml_load_string($xml);
	$json = json_encode($loaded);
	$var = json_decode($json, true);

	return $var;
}

/**
 * Object to array
 */
function core_object_to_array($data)
{
	if (is_object($data)) {
		$result = [];
		foreach ( (array) $data as $key => $value ) {
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
function core_csv_format($item)
{
	$ret = '';

	if (!is_array($item)) {

		return $ret;
	}

	foreach ( $item as $row ) {

		$entry = '';
		foreach ( $row as $field ) {
			if (strstr($field, '"')) {	// field value contains double-quote ?
				if (strstr($field, ',')) {	// ok, check if it also contains comma
					$field = str_replace('"', "'", $field); // ok, replace double-quote with single-quote
					$entry .= '"' . $field . '",'; // quote it and add comma delimeter at the end
				} else {
					$entry .= $field . ','; // ok, no comma, then no need to quote it just add comma delimeter at the end
				}
			} else {
				$entry .= '"' . $field . '",'; // quote it and add comma delimeter at the end
			}
		}
		$entry = preg_replace('/,$/', '', $entry); // we have comma at the end, remove it

		if ($entry) {
			$ret .= $entry . PHP_EOL;
		}
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
 * @param string $content_encoding        
 * @param string $convert_encoding_to        
 */
function core_download($content, $fn = '', $content_type = '', $charset = '', $content_encoding = '', $convert_encoding_to = '')
{
	$fn = trim($fn) ? trim($fn) : 'download.txt';
	$content_type = trim($content_type) ? strtolower(trim($content_type)) : 'text/plain';
	$charset = strtolower(trim($charset));

	ob_end_clean();
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	if ($content_encoding) {
		header('Content-Encoding: ' . $content_encoding);
	}
	if ($charset) {
		header('Content-Type: ' . $content_type . '; charset=' . $charset);
	} else {
		header('Content-Type: ' . $content_type);
	}
	header('Content-Disposition: attachment; filename=' . $fn);

	if ($convert_encoding_to) {
		if (function_exists('iconv')) {
			$content = iconv($convert_encoding_to, $content_encoding, $content);
		} else if (function_exists('mb_convert_encoding')) {
			$content = mb_convert_encoding($content, $convert_encoding_to, $content_encoding);
		}
	}

	_p($content);
	die();
}

/**
 * Get default SMSC
 *
 * @global array $core_config
 * @return string
 */
function core_smsc_get()
{
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
function core_gateway_get()
{
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
function core_lang_get()
{
	global $core_config, $user_config;

	$ret = core_call_hook();
	if (!$ret) {
		return $user_config['language_module'] ? $user_config['language_module'] : $core_config['main']['language_module'];
	}

	return $ret;
}

/**
 * Get active themes
 *
 * @global array $core_config
 * @return string
 */
function core_themes_get()
{
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
 * @param int $uid        
 * @param string $plugin_category        
 * @param string $plugin_name        
 * @return bool
 */
function core_plugin_get_status($uid, $plugin_category, $plugin_name)
{
	$ret = false;

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
			$ret = true;
		}
	}

	return $ret;
}

/**
 * Set status of plugin
 *
 * @param int $uid        
 * @param string $plugin_category        
 * @param string $plugin_name        
 * @param bool $plugin_status        
 * @return bool
 */
function core_plugin_set_status($uid, $plugin_category, $plugin_name, $plugin_status)
{
	$ret = false;

	$status = core_plugin_get_status($uid, $plugin_category, $plugin_name);
	if ((($status == 2) && $plugin_status) || ($status == 1 && (!$plugin_status))) {
		$ret = true;
	} else {
		$plugin_status = ($plugin_status ? 2 : 1);
		$items = array(
			'enabled' => $plugin_status
		);
		if (registry_update($uid, $plugin_category, $plugin_name, $items)) {
			$ret = true;
		}
	}

	return $ret;
}

/**
 * Set CSRF token value and form
 *
 * @return array array(value, form)
 */
function core_csrf_set()
{
	$ret = [];

	$csrf_token = _hash(core_get_random_string());
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
function core_csrf_set_token()
{
	$ret = '';

	$csrf_token = _hash(core_get_random_string());
	if ($csrf_token && $_SESSION['X-CSRF-Token'] = $csrf_token) {
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
function core_csrf_get()
{
	$ret = [];

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
function core_csrf_get_token()
{
	$ret = '';

	if ($csrf_token = $_SESSION['X-CSRF-Token']) {
		$ret = $csrf_token;
	}

	//_log('token:'.$csrf_token, 3, 'core_csrf_get_token');
	return $ret;
}

/**
 * Validate CSRF token
 *
 * @return bool
 */
function core_csrf_validate()
{
	$submitted_token = $_POST['X-CSRF-Token'];
	$token = core_csrf_get_token();

	//_log('token:'.$token.' submitted_token:'.$submitted_token, 3, 'core_csrf_validate');
	if ($token && $submitted_token && ($token == $submitted_token)) {

		return true;
	} else {

		return false;
	}
}

/**
 * Get playSMS version
 *
 * @return string
 */
function core_get_version()
{
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
function core_print($string)
{
	global $core_config;

	$string = trim($string);

	echo $string;

	return $string;
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
 * @param int $period
 *        Period between last event and now (in second)
 * @return bool true for period passed
 */
function core_playsmsd_timer($period = 60)
{

	// default period is 60 seconds
	$period = ((int) $period <= 0 ? 60 : (int) $period);

	$now = time();
	$next = floor(($now / $period)) * $period + $period;
	if (($now + 1) < $next) {

		// it is not the time yet
		return false;
	} else {

		// its passed the timer period
		return true;
	}
}

/**
 * Get mobile format for matching purposes
 *
 * @param string $mobile        
 * @return string
 */
function core_mobile_matcher_format($mobile)
{
	// sanitize for mobile numbers only
	$mobile = core_sanitize_mobile($mobile);

	if (strlen($mobile) >= 6) {
		// remove +
		$mobile = str_replace('+', '', $mobile);

		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) {
			$mobile = substr($mobile, 3);
		}

	}

	return $mobile;
}

/**
 * Get last submitted $_POST data
 *
 * @param string $key        
 * @return mixed
 */
function core_last_post_get($key = '')
{
	$ret = '';

	$key = trim($key);
	if ($key) {
		$ret = $_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_))][$key];
	} else {
		$ret = $_SESSION['tmp']['last_post'][md5(trim(_APP_ . _INC_ . _ROUTE_))];
	}

	return $ret;
}

/**
 * Empty last submitted $_POST data
 *
 * @return bool true
 */
function core_last_post_empty()
{
	$_SESSION['tmp']['last_post'] = [];

	return true;
}

/**
 * Check if ID is exists on certain DB table
 *
 * @param int $id ID value
 * @param string $db_table DB table name
 * @param string $field_name DB field name
 * @return bool true if exists
 */
function core_check_id($id, $db_table, $field_name)
{
	global $user_config;

	$id = (int) $id;
	$db_table = trim($db_table);
	$field_name = trim($field_name);

	if (!($id > 0 && $db_table && $field_name)) {

		return false;
	}

	if (preg_match('/[^a-z\d\-_]+/i', $db_table)) {

		return false;
	}

	if (preg_match('/[^a-z\d\-_]+/i', $field_name)) {

		return false;
	}

	$conditions = [
		$field_name => $id
	];
	if (!auth_isadmin()) {
		$conditions['uid'] = $user_config['uid'];
	}
	$list = dba_search($db_table, $field_name, $conditions);
	$db_id = isset($list[0][$field_name]) ? (int) $list[0][$field_name] : 0;
	if ($db_id === $id) {

		return true;
	}

	return false;
}

// --------------------------------------------------------------------------------------------

// fixme anton
// enforced to declare function _() for gettext replacement if no PHP gettext extension found
// it is also possible to completely remove gettext and change multi-lang with translation array
if (!function_exists('_')) {

	function _($text)
	{
		return $text;
	}
}

/**
 * Include composer based packages
 */
if (file_exists(_APPS_PATH_LIBS_ . '/composer/vendor/autoload.php')) {
	include_once _APPS_PATH_LIBS_ . '/composer/vendor/autoload.php';
} else {
	die(_('FATAL ERROR') . ' : ' . _('Unable to find composer files') . ' ' . _('Please run composer.phar update'));
}

/**
 * Include core functions on plugin core
 */

$pc = 'core';
$core_dir = _APPS_PATH_PLUG_ . '/' . $pc . '/';

unset($core_config['plugins']['list'][$pc]);
unset($tmp_core_config['plugins']['list'][$pc]);

$core_config['plugins']['list'][$pc] = [];
$fd = opendir($core_dir);
while (false !== ($plugin = readdir($fd))) {

	// plugin's dir prefixed with dot or underscore will not be loaded
	if (!(substr($plugin, 0, 1) == "." || substr($plugin, 0, 1) == "_")) {
		if (file_exists($core_dir . $plugin . '/config.php') && file_exists($core_dir . $plugin . '/fn.php')) {
			$core_config['plugins']['list'][$pc][] = $plugin;
		}
	}
}
closedir();
sort($core_config['plugins']['list'][$pc]);

foreach ( $core_config['plugins']['list'][$pc] as $plugin ) {
	// config.php
	include $core_dir . $plugin . '/config.php';

	// fn.php
	include_once $core_dir . $plugin . '/fn.php';
}

// load shortcuts
include_once $core_config['apps_path']['libs'] . "/fn_shortcuts.php";
