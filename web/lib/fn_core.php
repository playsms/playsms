<?php
defined('_SECURE_') or die('Forbidden');

function q_sanitize($var) {
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

function x_hook($c_plugin, $c_function, $c_param=array()) {
	$c_fn = $c_plugin.'_hook_'.$c_function;
	if ($c_plugin && $c_function && function_exists($c_fn)) {
		return call_user_func_array($c_fn, $c_param);
	}
}

function playsmsd() {
	global $core_config;
	// plugin tools
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		x_hook($core_config['toolslist'][$c],'playsmsd');
	}
	// plugin feature
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
		x_hook($core_config['featurelist'][$c],'playsmsd');
	}
	// plugin gateway
	$gw = core_gateway_get();
	x_hook($gw,'playsmsd');
}

function str2hex($string)  {
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
 * Get current date and time
 * @param $format
 *    output format 'date' for date only and 'time' for time only
 * @return
 *    current date and time
 */
function core_get_datetime($format='') {
	global $core_config;
	$ret = date($core_config['datetime']['format'], mktime());
	if (strtolower(trim($format)) == 'date') {
		$arr = explode(' ', $ret);
		$ret = $arr[0];
	}
	if (strtolower(trim($format)) == 'time') {
		$arr = explode(' ', $ret);
		$ret = $arr[1];
	}
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
		$gw = core_gateway_get();
		if (! ($ret = $core_config['plugin'][$gw]['datetime_timezone'])) {
			$ret = $core_config['main']['cfg_datetime_timezone'];
		}
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
	global $core_config;
	$gw = core_gateway_get();
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (! $tz) {
			if (! ($tz = $core_config['user']['datetime_timezone'])) {
				if (! ($tz = $core_config['plugin'][$gw]['datetime_timezone'])) {
					$tz = $core_config['main']['cfg_datetime_timezone'];
				}
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
	global $core_config;
	$gw = core_gateway_get();
	$time = trim($time);
	$ret = $time;
	if ($time && ($time != '0000-00-00 00:00:00')) {
		if (! $tz) {
			if (! ($tz = $core_config['user']['datetime_timezone'])) {
				if (! ($tz = $core_config['plugin'][$gw]['datetime_timezone'])) {
					$tz = $core_config['main']['cfg_datetime_timezone'];
				}
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
	$username = preg_replace("/[^A-Za-z0-9\.\-]/", '', $username);
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
		// echo "used network ($network) for ($ip)\n";
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

	// echo "from original network($orig_network), used network ($network) for ($ip)\n";

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

function core_detect_unicode($text) {
	$unicode = 0;
	if (function_exists('mb_detect_encoding')) {
		$encoding = mb_detect_encoding($text, 'auto');
		if ($encoding != 'ASCII') {
			$unicode = 1;
		}
	} else {
		$unicode = false;
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
 * Read documents
 */
function core_read_docs($base_dir, $doc) {
	global $core_config;
	$content = '';
	$fn = $base_dir."/docs/".$doc;
	if (file_exists($fn)) {
		$fd = @fopen($fn, "r");
		$fc = @fread($fd, filesize($fn));
		@fclose($fd);
		$fc = str_replace('{VERSION}', $core_config['version'], $fc);
		$fi = pathinfo($fn);
		if ($fi['extension'] == 'md') {
			$content .= Parsedown::instance()->parse($fc);
		} else if ($fi['extension'] == 'html') {
			$content .= $fc;
		} else {
			$content .= '<pre>'.htmlentities($fc).'</pre>';
		}
	}
	return $content;
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
	echo $content;
	die();
}

/**
 * Get active gateway plugin
 * @global array $core_config
 * @return string
 */
function core_gateway_get() {
	global $core_config;
	$ret = $core_config['module']['gateway'];
	return $ret;
}

?>