<?php
defined('_SECURE_') or die('Forbidden');

// parameters
$u 	 = trim($_REQUEST['u']);
$p 	 = trim($_REQUEST['p']);

// type of action (ta) or operation (op), ta = op
$ta	 = trim(strtoupper($_REQUEST['ta']));
$op	 = trim(strtoupper($_REQUEST['op']));

// send SMS specifics
$to 	 = trim(strtoupper($_REQUEST['to']));
$msg 	 = trim($_REQUEST['msg']);
$type 	 = ( trim($_REQUEST['type']) ? trim($_REQUEST['type']) : 'text' );
$unicode = ( trim($_REQUEST['unicode']) ? trim($_REQUEST['unicode']) : 0 );

// DS specifics
$slid	 = trim($_REQUEST['slid']);
$c	 = trim($_REQUEST['c']);
$last	 = trim($_REQUEST['last']);

// default error return
$ret = "ERR 102";

if ($op) { $ta = $op; };
if ($ta) {
	switch ($ta) {
		case "PV":
			if (validatelogin($u,$p)) {
				$ret = webservices_pv($u,$to,$msg,$type,$unicode);
			} else {
				$ret = "ERR 100";
			}
			break;
		case "BC":
			if (validatelogin($u,$p)) {
				$ret = webservices_bc($u,$to,$msg,$type,$unicode);
			} else {
				$ret = "ERR 100";
			}
			break;
		case "DS":
			if (validatelogin($u,$p)) {
				if ($slid) {
					$ret = webservices_ds_slid($u,$slid);
				} else {
					$ret = webservices_ds_count($u,$c,$last);
				}
			} else {
				$ret = "ERR 100";
			}
			break;
		case "CR":
			if (validatelogin($u,$p)) {
				$ret = webservices_cr($u);
			} else {
				$ret = "ERR 100";
			}
			break;
		default:
			// output do not require valid login
			$ret = webservices_output($ta,$_REQUEST);
	}
}

echo $ret;

?>