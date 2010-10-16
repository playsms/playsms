<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function webservices_pv($c_username,$to,$msg,$type='text',$unicode=0) {
    if ($c_username && $to && $msg) {
	// websend2pv($c_username,$sms_to,$message,$sms_type='text',$unicode=0)
	list($ok,$to,$smslog_id) = websend2pv($c_username,$to,$msg,$type,$unicode);
	if ($ok[0] && $smslog_id[0]) {
	    $ret = "OK ".$smslog_id[0];
	} else {
	    $ret = "ERR 200";
	}
    } else {
	$ret = "ERR 201";
    }
    return $ret;
}

function webservices_bc($c_username,$c_gcode,$msg,$type='text',$unicode=0) {
    if ($c_username && $c_gcode && $msg) {
	$c_gpid = phonebook_c_gcode2id($c_username,$c_gcode);
	// websend2group($c_username,$c_gpid,$message,$sms_type='text',$unicode=0)
	list($ok,$to,$smslog_id) = websend2group($c_username,$c_gpid,$msg,$type,$unicode);
	if ($ok[0]) {
	    $ret = "OK";
	} else {
	    $ret = "ERR 300";
	}
    } else {
	$ret = "ERR 301";
    }
    return $ret;
}

function webservices_ds_slid($c_username, $slid) {
    $ret = "ERR 101";
    $uid = username2uid($c_username);
    $content = "";
    if ($slid) {
	$db_query = "SELECT p_status FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND smslog_id='$slid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
	    $p_status = $db_row['p_status'];
	    $ret = $p_status;
	} else {
	    $ret = "ERR 400";
	}
    }
    return $ret;
}

function webservices_ds_count($c_username,$c=100,$last=false) {
    $ret = "ERR 101";
    $uid = username2uid($c_username);
    if ($c) {
        $query_limit = " LIMIT 0,$c";
    } else {
        $query_limit = " LIMIT 0,100";
    }
    if ($last) {
        $query_last = "AND smslog_id>$last";
    }
    $content_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $content_csv = "";
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' $query_last ORDER BY p_datetime DESC $query_limit";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result)) {
	$smslog_id = $db_row['smslog_id'];
	$p_src = $db_row['p_src'];
	$p_dst = $db_row['p_dst'];
	$p_datetime = $db_row['p_datetime'];
	$p_update = $db_row['p_update'];
	$p_status = $db_row['p_status'];
	$content_xml .= "<ds id=\"".$smslog_id."\" src=\"".$p_src."\" dst=\"".$p_dst."\" datetime=\"".$p_datetime."\" update=\"".$p_update."\" status=\"".$p_status."\"></ds>\n";
	$content_csv .= "\"$smslog_id\";\"$p_src\";\"$p_dst\";\"$p_datetime\";\"$p_update\";\"$p_status\";\n";
    }
    // if DS available by checking content_csv
    if ($content_csv) {
	if ($form == "XML") {
	    header("Content-Type: text/xml");
	    $ret = $content_xml;
	} else {
	    $ret = $content_csv;
	}
    } else {
	$ret = "ERR 400";
    }
    return $ret;
}

function webservices_output($ta,$requests) {
    $ta = strtolower($ta);
    $ret = x_hook($ta,'webservices_output',array($ta,$requests));
    return $ret;
}

?>