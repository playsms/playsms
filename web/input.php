<?php include "init.php"; 
include $apps_path['libs']."/function.php";

// -----------------------------------------------------------------------------
// query string: 
// u	: username
// p	: password
// ta	: type of action 
// 	pv = send private
// 	bc = send broadcast
// 	ds = delivery status
// last : last SMS log ID (this number not included on result)
// c	: number of delivery status retrived
// slid	: SMS Log ID (for ta=ds, when slid defined 'last' and 'c' has no effect)
// to	: destination number (for ta=pv) or destination group code (for ta=bc)
// msg	: message
// from	: sender mobile number
// type : message type (1=flash, 2=text)
// form : ds output format
// example: 
// http://x.com/input.php?u=admin&p=rahasia&ta=bc&to=TI&msg=meeting+at+15.00+today!
// -----------------------------------------------------------------------------
// if succeded returns: OK SMS_LOG_ID (eg: OK 754)
// if error occured returns:
// 	ERR 100	= authentication failed
//	ERR 101	= type of action not valid
//	ERR 102	= one or more field empty
//	ERR 200	= send private failed
//	ERR 201 = destination number or message is empty
//	ERR 300	= send broadcast failed
//	ERR 301 = destination group or message is empty
//	ERR 400 = no delivery status retrieved
// ----------------------------------------------------------------------------
// output delivery status (for ta=ds) in CSV form:
// SMS log ID; Source number; Destination Number; Message; Delivery Time; Update Pending Status Time; SMS Status
// SMS Status:
// 0 = pending
// 1 = sent
// 2 = failed
// 3 = delivered
// ----------------------------------------------------------------------------

$u 	= trim($_REQUEST['u']);
$p 	= trim($_REQUEST['p']);
$ta	= trim(strtoupper($_REQUEST['ta']));
$last	= trim($_REQUEST['last']);
$c	= trim($_REQUEST['c']);
$slid	= trim($_REQUEST['slid']);
$to 	= trim(strtoupper($_REQUEST['to']));
$msg 	= trim($_REQUEST['msg']);
$from	= trim($_REQUEST['from']);
$type 	= trim($_REQUEST['type']);
$form 	= trim(strtoupper($_REQUEST['form']));

if ($u && $p)
{
    if (!validatelogin($u,$p))
    {
	echo "ERR 100";
	die();
    }
}

if ($ta)
{
    switch ($ta)
    {
	case "PV":
	    if ($to && $msg)
	    {
		$transparent = false;
		if ($trn)
		{
		    $transparent = true;
		}
		list($ok,$to,$smslog_id) = websend2pv($u,$to,$msg);
		if ($ok[0] && $smslog_id[0])
		{
		    echo "OK ".$smslog_id[0];
		}
		else
		{
			echo "ERR 200";
		}
	    }
	    else
	    {
		echo "ERR 201";
	    }
	    die();
	    break;
	case "BC":
	    if ($to && $msg)
	    {
		$transparent = false;
		if ($trn)
		{
		    $transparent = true;
		}
		list($ok,$to,$smslog_id) = websend2group($u,$to,$msg);
		if ($ok[0])
		{
		    echo "OK";
		}
		else
		{
		    echo "ERR 300";
		}
	    }
	    else
	    {
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
	    if ($slid)
	    {
		$db_query = "SELECT p_status FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND smslog_id='$slid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result))
		{
		    $p_status = $db_row['p_status'];
		    echo $p_status;
		}
		else
		{
		    echo "ERR 400";
		}
		die();
	    }
	    else
	    {
		if ($c)
		{
		    $query_limit = " LIMIT 0,$c";
		}
		if ($last)
		{
		    $query_last = "AND smslog_id>$last";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' $query_last ORDER BY p_datetime DESC $query_limit";
		$db_result = dba_query($db_query);
		$content_xml = "<?phpxml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$content_csv = "";
		while ($db_row = dba_fetch_array($db_result))
		{
		    $smslog_id = $db_row['smslog_id'];
		    $p_src = $db_row['p_src'];
		    $p_dst = $db_row['p_dst'];
		    $p_datetime = $db_row['p_datetime'];
		    $p_update = $db_row['p_update'];
		    $p_status = $db_row['p_status'];
		    $content_xml .= "<ds id=\"".$smslog_id."\" src=\"".$p_src."\" dst=\"".$p_dst."\" datetime=\"".$p_datetime."\" update=\"".$p_update."\" status=\"".$p_status."\"></ds>\n";
		    $content_csv .= "\"$smslog_id\";\"$p_src\";\"$p_dst\";\"$p_datetime\";\"$p_update\";\"$p_status\";\n";
		}
		if ($content_csv)
		{
		    if ($form == "XML")
		    {
			header("Content-Type: text/xml");
			echo $content_xml;
		    }
		    else
		    {
			echo $content_csv;
		    }
		}
		else
		{
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