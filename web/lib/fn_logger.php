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

?>