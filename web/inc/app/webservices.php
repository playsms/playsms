<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

// all available parameters
$u 	 = trim($_REQUEST['u']);
$p 	 = trim($_REQUEST['p']);
$ta	 = trim(strtoupper($_REQUEST['ta']));
$op	 = trim(strtoupper($_REQUEST['op']));
$last	 = trim($_REQUEST['last']);
$c	 = trim($_REQUEST['c']);
$slid	 = trim($_REQUEST['slid']);
$to 	 = trim(strtoupper($_REQUEST['to']));
$msg 	 = trim($_REQUEST['msg']);
$from	 = trim($_REQUEST['from']);
$type 	 = ( trim($_REQUEST['type']) ? trim($_REQUEST['type']) : 'text' );
$unicode = ( trim($_REQUEST['unicode']) ? trim($_REQUEST['unicode']) : 0 );
$form 	 = trim(strtoupper($_REQUEST['form']));

$ret = "ERR 102";

if ($op) { $ta = $op; };
if ($ta) {
    switch ($ta) {
	case "PV":
	    if ($u && $p) {
		if (validatelogin($u,$p)) {
		    $ret = webservices_pv($u,$to,$msg,$type,$unicode);
		} else {
		    $ret = "ERR 100";
		}
	    }
	    break;
	case "BC":
	    if ($u && $p) {
		if (validatelogin($u,$p)) {
		    $ret = webservices_bc($u,$to,$msg,$type,$unicode);
		} else {
		    $ret = "ERR 100";
		}
	    }
	    break;
	case "DS":
	    if ($u && $p) {
		if (validatelogin($u,$p)) {
		    if ($slid) {
			$ret = webservices_ds_slid($u,$slid);
		    } else {
			$ret = webservices_ds_count($u,$c,$last);
		    }
		} else {
		    $ret = "ERR 100";
		}
	    }
	    break;
	default:
	    $ret = webservices_output($ta,$_REQUEST);
    }
}

echo $ret;
exit();

?>