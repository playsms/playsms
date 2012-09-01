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

if ($u && $p) {
	if (validatelogin($u,$p)) {
		$username = $u;
		$uid = username2uid($username);
		$core_config['user'] = user_getdatabyuid($uid);
		$core_config['user']['opt']['sms_footer_length'] = ( strlen($footer) > 0 ? strlen($footer) + 1 : 0 );
		$core_config['user']['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
		$core_config['user']['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
		$core_config['user']['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
		$core_config['user']['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
	}
}

if ($op) { $ta = $op; };
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

?>