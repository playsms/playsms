<?php 
if(!(defined('_SECURE_'))){die('Intruder alert');};

$u 	 = trim($_REQUEST['u']);
$p 	 = trim($_REQUEST['p']);
$ta	 = trim(strtoupper($_REQUEST['ta']));
$last	 = trim($_REQUEST['last']);
$c	 = trim($_REQUEST['c']);
$slid	 = trim($_REQUEST['slid']);
$to 	 = trim(strtoupper($_REQUEST['to']));
$msg 	 = trim($_REQUEST['msg']);
$from	 = trim($_REQUEST['from']);
$type 	 = ( trim($_REQUEST['type']) ? trim($_REQUEST['type']) : 'text' );
$unicode = ( trim($_REQUEST['unicode']) ? trim($_REQUEST['unicode']) : 0 );
$form 	 = trim(strtoupper($_REQUEST['form']));

if ($u && $p) {
    if (!validatelogin($u,$p)) {
	echo "ERR 100";
	die();
    }
} else {
    echo "ERR 102";
    die();
}

if ($ta) {
    switch ($ta) {
	case "PV":
	    if ($to && $msg) {
		$transparent = false;
		if ($trn) {
		    $transparent = true;
		}
		// websend2pv($username,$sms_to,$message,$sms_type='text',$unicode=0)
		list($ok,$to,$smslog_id) = websend2pv($u,$to,$msg,$type,$unicode);
		if ($ok[0] && $smslog_id[0]) {
		    echo "OK ".$smslog_id[0];
		} else {
			echo "ERR 200";
		}
	    } else {
		echo "ERR 201";
	    }
	    die();
	    break;
	case "BC":
	    if ($to && $msg) {
		$transparent = false;
		if ($trn) {
		    $transparent = true;
		}
		$to_gpid = phonebook_groupcode2id($u,$to);
		// websend2group($username,$gpid,$message,$sms_type='text',$unicode=0)
		list($ok,$to,$smslog_id) = websend2group($u,$to_gpid,$msg,$type,$unicode);
		if ($ok[0]) {
		    echo "OK";
		} else {
		    echo "ERR 300";
		}
	    } else {
		echo "ERR 301";
	    }
	    die();
	    break;
	case "DS":
	    // output in CSV form:
	    // SMS log ID; Source number; Destination Number; Delivery Time; Update Pending Status Time; SMS Status
	    // SMS Status:
	    // 0 = pending
	    // 1 = sent
	    // 2 = failed
	    // 3 = delivered
	    $uid = username2uid($u);
	    $content = "";
	    if ($slid) {
		$db_query = "SELECT p_status FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND smslog_id='$slid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
		    $p_status = $db_row['p_status'];
		    echo $p_status;
		} else {
		    echo "ERR 400";
		}
		die();
	    } else {
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
		if ($content_csv) {
		    if ($form == "XML") {
			header("Content-Type: text/xml");
			echo $content_xml;
		    } else {
			echo $content_csv;
		    }
		} else {
		    echo "ERR 400";
	        }
		die();
	    }
	    break;
    }
    echo "ERR 101";
    die();
}
echo "ERR 102";

?>