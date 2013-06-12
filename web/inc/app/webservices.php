<?php
defined('_SECURE_') or die('Forbidden');

// parameters
$h	= trim($_REQUEST['h']);
$u	= trim($_REQUEST['u']);
$p	= trim($_REQUEST['p']);

// type of action (ta) or operation (op), ta = op
$ta	= trim(strtoupper($_REQUEST['ta']));
$op	= trim(strtoupper($_REQUEST['op']));

// output format
$format = trim(strtoupper($_REQUEST['format']));

// PV, BC
$to	= trim(strtoupper($_REQUEST['to']));
$msg	= trim($_REQUEST['msg']);
$type	= ( trim($_REQUEST['type']) ? trim($_REQUEST['type']) : 'text' );
$unicode= ( trim($_REQUEST['unicode']) ? trim($_REQUEST['unicode']) : 0 );

// DS, IN, GET_CONTACT, GET_CONTACT_GROUP
$src	= trim($_REQUEST['src']);
$dst	= trim($_REQUEST['dst']);
$dt	= trim($_REQUEST['dt']);
$c	= trim($_REQUEST['c']);
$last	= trim($_REQUEST['last']);

// DS
$queue	= trim($_REQUEST['queue']);
$slid	= trim($_REQUEST['slid']);

// IN, GET_CONTACT, GET_CONTACT_GROUP
$kwd	= trim($_REQUEST['kwd']);

if ($op) { $ta = $op; };
if ($ta) {
	switch ($ta) {
		case "PV":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_pv($u,$to,$msg,$type,$unicode);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "BC":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_bc($u,$to,$msg,$type,$unicode);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "DS":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_ds($u,$queue,$src,$dst,$dt,$slid,$c,$last);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "IN":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_in($u,$src,$dst,$kwd,$dt,$c,$last);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "CR":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_cr($u);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "GET_CONTACT":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_get_contact($c_uid, $kwd, $c);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "GET_CONTACT_GROUP":
			if ($u = webservices_validate($h,$u)) {
				list($ret,$json) = webservices_get_contact_group($c_uid, $kwd, $c);
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		case "GET_TOKEN":
			if (validatelogin($u,$p)) {
				$user = user_getdatabyusername($u);
				if ($user['uid']) {
					$continue = false;
					$ret = "ERR 106";
					$json['status'] = 'ERR';
					$json['error'] = '106';
					$ip = explode(',', $user['webservices_ip']);
					if (is_array($ip)) {
						foreach ($ip as $key => $net) {
							if (core_net_match($net, $_SERVER['REMOTE_ADDR'])) {
								$continue = true;
							}
						}
					}
					if ($continue) {
						$continue = false;
						if ($token = $user['token']) {
							$continue = true;
						} else {
							$ret = "ERR 104";
							$json['status'] = 'ERR';
							$json['error'] = '104';
						}
					}
					if ($continue) {
						if ($user['enable_webservices']) {
							$ret = "OK ".$token;
							$json['status'] = 'OK';
							$json['error'] = '0';
							$json['token'] = $token;
						} else {
							$ret = "ERR 105";
							$json['status'] = 'ERR';
							$json['error'] = '105';
						}
					}
				} else {
					$ret = "ERR 100";
					$json['status'] = 'ERR';
					$json['error'] = '100';
				}
			} else {
				$ret = "ERR 100";
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			break;
		default:
			if ($ta) {
				// output do not require valid login
				$ret = webservices_output($ta,$_REQUEST);
				echo $ret;
				exit();
			} else {
				// default error return
				$ret = "ERR 102";
				$json['status'] = 'ERR';
				$json['error'] = '102';
			}
	}
}

if ($format=='JSON') {
	echo json_encode($json);
} else if ($format=='SERIALIZE') {
	echo serialize($json);
} else if ($format=='XML') {
	$xml = core_array_to_xml($json, new SimpleXMLElement('<response/>'));
	ob_end_clean();
	header('Content-Type: text/xml');
	echo $xml->asXML();
} else if ($format=='' || $format=='PLAIN') {
	echo $ret;
}

?>