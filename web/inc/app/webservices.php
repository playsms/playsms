<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

// parameters
$u 	 = trim($_REQUEST['u']);
$p 	 = trim($_REQUEST['p']);

// type of action (ta) or operation (op), ta = op
$ta	 = trim(strtoupper($_REQUEST['ta']));
$op	 = trim(strtoupper($_REQUEST['op']));

// PV and BC specifics
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

$is_valid = false;
if ($u && $p) {
	if (validatelogin($u,$p)) {
		$core_config['user'] = user_getdatabyusername($u);
		$is_valid = true;
	}
}

if ($op) { $ta = $op; };

$c_is_valid = ( $is_valid ? 1 : 0 );
logger_print("start ta:".$ta." u:".$u." valid:".$c_is_valid." ip:".$_SERVER['REMOTE_ADDR'], 3, "webservices");

if ($ta) {
	switch ($ta) {
		case "PV":
			if ($u && $p) {
				if ($core_config['user']['uid']) {
					$ret = webservices_pv($u,$to,$msg,$type,$unicode);
				} else {
					$ret = "ERR 100";
				}
			}
			break;
		case "BC":
			if ($u && $p) {
				if ($core_config['user']['uid']) {
					$ret = webservices_bc($u,$to,$msg,$type,$unicode);
				} else {
					$ret = "ERR 100";
				}
			}
			break;
		case "DS":
			if ($u && $p) {
				if ($core_config['user']['uid']) {
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
                case "CR":
                        if ($u && $p) {
                                if ($core_config['user']['uid']) {
                                        $ret = webservices_cr($u);
                                } else {
                                        $ret = "ERR 100";
                                }
                        }
                        break;
		default:
			// output do not require valid login
			$ret = webservices_output($ta,$_REQUEST);
	}
}

echo $ret;

logger_print("end ta:".$ta." u:".$u." valid:".$c_is_valid." ip:".$_SERVER['REMOTE_ADDR'], 3, "webservices");

?>