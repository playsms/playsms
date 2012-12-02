<?php
defined('_SECURE_') or die('Forbidden');

function logger_print($log, $level, $label) {
	global $core_config;
	$logfile = ( $core_config['main']['logfile'] ? $core_config['main']['logfile'] : 'playsms.log' );
	$level = (int) $level;
	if (logger_get_level() >= $level) {
		$type = 'L'.$level;
		$fn = $core_config['apps_path']['logs'].'/'.$logfile;
		if ($fd = fopen($fn, 'a+')) {
			$message = stripslashes($core_config['datetime']['now']." ".$type." ".$label." # ".$log);
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

?>