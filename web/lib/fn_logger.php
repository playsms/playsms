<?php
defined('_SECURE_') or die('Forbidden');

function logger_print($log, $level, $label) {
	global $core_config;
	$logfile = ( $core_config['logfile'] ? $core_config['logfile'] : 'playsms.log' );
	$level = (int) $level;
	$username = ( $core_config['user']['username'] ? $core_config['user']['username'] : '-' );
	if (logger_get_level() >= $level) {
		$type = 'L'.$level;
		$fn = $core_config['apps_path']['logs'].'/'.$logfile;
		if ($fd = fopen($fn, 'a+')) {
			$dt = date($core_config['datetime']['format'], mktime());
			$message = stripslashes($dt." "._PID_." ".$username." ".$type." ".$label." # ".$log);
			$message = str_replace("\n", " ", $message);
			$message = str_replace("\r", " ", $message);
			$message .= "\n";
			fputs($fd, $message);
			fclose($fd);
		}
	}
}

function logger_get_level() {
	global $core_config;
	return $core_config['logstate'];
}

function logger_set_level($level=0) {
	global $core_config;
	$core_config['logstate'] = $level;
}

function logger_audit() {
	global $core_config;
	if ($core_config['logaudit']) {
		foreach ($_GET as $key => $val) {
			if(stristr($key, 'password') === FALSE) {
				$log .= $key.':'.$val.' ';
			} else {
				$log .= $key.':xxxxxx ';
			}
		}
		foreach ($_POST as $key => $val) {
			if(stristr($key, 'password') === FALSE) {
				$log .= $key.':'.$val.' ';
			} else {
				$log .= $key.':xxxxxx ';
			}
		}
		$log = trim($log);
		$logauditfile = ( $core_config['logauditfile'] ? $core_config['logauditfile'] : 'audit.log' );
		$username = ( $core_config['user']['username'] ? $core_config['user']['username'] : '-' );
		$ip = $_SERVER['REMOTE_ADDR'];
		$fn = $core_config['apps_path']['logs'].'/'.$logauditfile;
		if ($fd = fopen($fn, 'a+')) {
			$dt = date($core_config['datetime']['format'], mktime());
			$message = stripslashes($dt." "._PID_." ".$username." ip:".$ip." ".$log);
			$message = str_replace("\n", " ", $message);
			$message = str_replace("\r", " ", $message);
			$message .= "\n";
			fputs($fd, $message);
			fclose($fd);
		}
	}
}

?>