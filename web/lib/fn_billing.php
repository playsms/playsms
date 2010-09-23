<?php

function billing_post($smslog_id,$rate,$credit) {
    global $core_config;
    $ok = false;
    if ($smslog_id) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if (x_hook($core_config['toolslist'][$c],'billing_post',array($smslog_id,$rate,$credit))) {
		$ok = true;
		break;
	    }
	}
    }
    return $ok;
}

function billing_roll($smslog_id) {
    global $core_config;
    $ret_array = array(0,0);
    if ($smslog_id) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($ret_array = x_hook($core_config['toolslist'][$c],'billing_roll',array($smslog_id))) {
		break;
	    }
	}
    }
    return $ret_array;
}

?>