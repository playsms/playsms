<?php

function logger_print($log,$type='debug')
{
    global $apps_path, $datetime_now;
    if (logger_get_state()) {
	switch ($type) {
	    case "debug":
	    case "error":
	    case "warning":
	    case "info":
    	    case "notice":
		$fn = $apps_path['logs'].'/playsms.log';
		$fd = fopen($fn, 'a+');
		$message = $datetime_now." ".strtoupper($type)." ".$log."\n";
		fputs($fd, $message);
		fclose($fd);
		break;
	}
    }
}

function logger_get_state()
{
    global $core_config;
    return $core_config['logstate'];
}

function logger_set_state($status)
{
    global $core_config;
    $core_config['logstate'] = $status;
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