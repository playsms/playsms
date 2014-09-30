<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

// parameters
$h = trim($_REQUEST['h']);
$u = trim($_REQUEST['u']);
$p = trim($_REQUEST['p']);

// output format
$format = strtoupper(trim($_REQUEST['format']));

// PV
$to = trim($_REQUEST['to']);
$schedule = trim($_REQUEST['schedule']);
$footer = trim($_REQUEST['footer']);
$nofooter = (trim($_REQUEST['nofooter']) ? TRUE : FALSE);
$type = (trim($_REQUEST['type']) ? trim($_REQUEST['type']) : 'text');
$unicode = (trim($_REQUEST['unicode']) ? trim($_REQUEST['unicode']) : 0);

// PV, INJECT
$from = trim($_REQUEST['from']);
$msg = trim($_REQUEST['msg']);

// INJECT
$recvnum = trim($_REQUEST['recvnum']);
$smsc = trim($_REQUEST['smsc']);

// DS, IN, SX, IX, GET_CONTACT, GET_CONTACT_GROUP
$src = trim($_REQUEST['src']);
$dst = trim($_REQUEST['dst']);
$dt = trim($_REQUEST['dt']);
$c = trim($_REQUEST['c']);
$last = trim($_REQUEST['last']);

// DS
$queue = trim($_REQUEST['queue']);
$smslog_id = trim($_REQUEST['smslog_id']);

// IN, GET_CONTACT, GET_CONTACT_GROUP
$kwd = trim($_REQUEST['kwd']);

$log_this = FALSE;

$ws_error_string = array(
	'100' => 'authentication failed',
	'101' => 'type of action is invalid or unknown',
	'102' => 'one or more field empty',
	'103' => 'not enough credit for this operation',
	'104' => 'webservice token is not available',
	'105' => 'webservice token not enable for this user',
	'106' => 'webservice token not allowed from this IP address',
	'200' => 'send message failed',
	'201' => 'destination number or message is empty',
	'400' => 'no delivery status available',
	'401' => 'no delivery status retrieved and SMS still in queue',
	'402' => 'no delivery status retrieved and SMS has been processed from queue',
	'501' => 'no data returned or result is empty',
	'600' => 'admin level authentication failed',
	'601' => 'inject message failed',
	'602' => 'sender id or message is empty' 
);

if (_OP_) {
	switch (strtoupper(_OP_)) {
		case "PV":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_pv($u, $to, $msg, $type, $unicode, $nofooter, $footer, $from, $schedule);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "INJECT":
			if ($u = webservices_validate_admin($h, $u)) {
				$json = webservices_inject($u, $from, $msg, $recvnum, $smsc);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "DS":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_ds($u, $queue, $src, $dst, $dt, $smslog_id, $c, $last);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "IN":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_in($u, $src, $dst, $kwd, $dt, $c, $last);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "SX":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_sx($u, $src, $dst, $dt, $c, $last);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "IX":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_ix($u, $src, $dst, $dt, $c, $last);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "CR":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_cr($u);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "GET_CONTACT":
			if ($u = webservices_validate($h, $u)) {
				$c_uid = user_username2uid($u);
				$json = webservices_get_contact($c_uid, $kwd, $c);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "GET_CONTACT_GROUP":
			if ($u = webservices_validate($h, $u)) {
				$c_uid = user_username2uid($u);
				$json = webservices_get_contact_group($c_uid, $kwd, $c);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "GET_TOKEN":
			if (auth_validate_login($u, $p)) {
				$user = user_getdatabyusername($u);
				if ($user['uid']) {
					$continue = false;
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
							$json['status'] = 'ERR';
							$json['error'] = '104';
						}
					}
					if ($continue) {
						if ($user['enable_webservices']) {
							$json['status'] = 'OK';
							$json['error'] = '0';
							$json['token'] = $token;
						} else {
							$json['status'] = 'ERR';
							$json['error'] = '105';
						}
					}
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '100';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		case "SET_TOKEN":
			if ($u = webservices_validate($h, $u)) {
				$user = user_getdatabyusername($u);
				if ($c_uid = $user['uid']) {
					$token = md5($c_uid . _PID_);
					$items = array(
						'token' => $token 
					);
					$conditions = array(
						'uid' => $c_uid 
					);
					if (dba_update(_DB_PREF_ . '_tblUser', $items, $conditions)) {
						$json['status'] = 'OK';
						$json['error'] = '0';
						$json['token'] = $token;
					} else {
						$json['status'] = 'ERR';
						$json['error'] = '100';
					}
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '100';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = TRUE;
			break;
		
		default :
			if (_OP_) {
				
				// output do not require valid login
				$ret = webservices_output(_OP_, $_REQUEST);
				_p($ret);
				exit();
			} else {
				
				// default error return
				$json['status'] = 'ERR';
				$json['error'] = '102';
			}
	}
}

// add an error_string to json response
$json['error_string'] = $ws_error_string[$json['error']];

// add timestamp
$json['timestamp'] = mktime();

if ($log_this) {
	logger_print("u:" . $u . " ip:" . $_SERVER['REMOTE_ADDR'] . " op:" . _OP_ . ' timestamp:' . $json['timestamp'] . ' error_string:' . $json['error_string'], 3, "webservices");
}

if ($format == 'SERIALIZE') {
	ob_end_clean();
	header('Content-Type: text/plain');
	_p(serialize($json));
} else if ($format == 'XML') {
	$xml = core_array_to_xml($json, new SimpleXMLElement('<response/>'));
	ob_end_clean();
	header('Content-Type: text/xml');
	_p($xml->asXML());
} else {
	ob_end_clean();
	header('Content-Type: application/json');
	_p(json_encode($json));
}
