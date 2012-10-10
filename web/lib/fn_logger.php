<?php
defined('_SECURE_') or die('Forbidden');

function logger_print($log,$level=1,$label="default") {
	global $core_config;
	$logfile = ( $core_config['main']['logfile'] ? $core_config['main']['logfile'] : 'playsms.log' );
	if (logger_get_level() >= (int)$level) {
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

function logger_set_error_string($error_string) {
	$db_query = "INSERT INTO "._DB_PREF_."_tblErrorString (error_string) VALUES ('$error_string')";
	$id = @dba_insert_id($db_query);
	return $id;
}

function logger_get_error_string($id, $nodel=false) {
	$ret_string = "";
	$db_query = "SELECT error_string FROM "._DB_PREF_."_tblErrorString WHERE id='$id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret_string = $db_row['error_string'];
		if (!($nodel)) {
			$db_query = "DELETE FROM "._DB_PREF_."_tblErrorString WHERE id='$id'";
			$db_result = @dba_affected_rows($db_query);
		}
	}
	return $ret_string;
}

?>