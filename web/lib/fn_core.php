<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

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

function x_hook($c_plugin, $c_function, $c_param=array()) {
	$c_fn = $c_plugin.'_hook_'.$c_function;
	if ($c_plugin && $c_function && function_exists($c_fn)) {
		return call_user_func_array($c_fn, $c_param);
	}
}

function getsmsinbox() {
	global $gateway_module;
	x_hook($gateway_module,'getsmsinbox');
}

function getsmsstatus() {
	global $gateway_module;
	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='0' AND p_gateway='$gateway_module'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$smslog_id = $db_row['smslog_id'];
		$p_datetime = $db_row['p_datetime'];
		$p_update = $db_row['p_update'];
		$gpid = $db_row['p_gpid'];
		x_hook($gateway_module,'getsmsstatus',array($gpid,$uid,$smslog_id,$p_datetime,$p_update));
	}
}

function getsmsoutgoing($smslog_id) {
	$data = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$data = $db_row;
	}
	return $data;
}

function execcommoncustomcmd() {
	global $apps_path;
	@include $apps_path['incs']."/common/customcmd.php";
}


function playsmsd() {
	global $core_config, $gateway_module;
	// plugin tools
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		x_hook($core_config['toolslist'][$c],'playsmsd');
	}
	// plugin feature
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
		x_hook($core_config['featurelist'][$c],'playsmsd');
	}
	// plugin gateway
	x_hook($gateway_module,'playsmsd');
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
 *    max. length of word in $text, split if more than $len
 * @return
 *    formatted text
 */
function core_display_text($text, $len=0) {
	$text = htmlspecialchars($text);
	if ($len && (strlen($text) > $len)) {
		$arr = explode(" ",$text);
		for ($i=0;$i<count($arr);$i++) {
			if (strlen($arr[$i]) > $len) {
				$arr2 = str_split($arr[$i], $len);
				$arr[$i] = '';
				for ($j=0;$j<count($arr2);$j++) {
					$arr[$i] .= $arr2[$j]."\n";
				}
			}
		}
		$text = implode(" ",$arr);
	}
	return $text;
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
	if (! $tz) {
		if (! ($tz = $core_config['user']['datetime_timezone'])) {
			$tz = $core_config['main']['cfg_datetime_timezone'];
		}
	}
	$time = strtotime($time);
	$off = core_datetime_offset($tz);
	// the difference between core_display_datetime() and core_adjust_datetime()
	// core_display_datetime() will set to user's timezone (+offset)
	$ret = $time + $off;
	$ret = date($core_config['datetime']['format'], $ret);
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
	$gateway_module = $core_config['module']['gateway'];
	if (! $tz) {
		if (! ($tz = $core_config['plugin'][$gateway_module]['datetime_timezone'])) {
			$tz = $core_config['main']['cfg_datetime_timezone'];
		}
	}
	$time = strtotime($time);
	$off = core_datetime_offset($tz);
	// the difference between core_display_datetime() and core_adjust_datetime()
	// core_adjust_datetime() will set to GTM+0 (-offset)
	$ret = $time - $off;
	$ret = date($core_config['datetime']['format'], $ret);
	return $ret;
}

?>