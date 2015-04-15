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

// QUERY
$query = trim($_REQUEST['query']);

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
	'602' => 'sender id or message is empty',
	'603' => 'account addition failed due to missing data',
	'604' => 'fail to add account',
	'605' => 'account removal failed due to unknown username',
	'606' => 'fail to remove account',
	'607' => 'set parent failed due to unknown username',
	'608' => 'fail to set parent',
	'609' => 'get parent failed due to unknown username',
	'610' => 'fail to get parent',
	'611' => 'account ban failed due to unknown username',
	'612' => 'fail to ban account',
	'613' => 'account unban failed due to unknown username',
	'614' => 'fail to unban account',
	'615' => 'editing account preferences failed due to missing data',
	'616' => 'fail to edit account preferences',
	'617' => 'editing account configuration failed due to missing data',
	'618' => 'fail to edit account configuration',
	'619' => 'viewing credit failed due to missing data',
	'620' => 'fail to view credit',
	'621' => 'adding credit failed due to missing data',
	'622' => 'fail to add credit',
	'623' => 'deducting credit failed due to missing data',
	'624' => 'fail to deduct credit',
	'625' => 'setting login key failed due to missing data',
	'626' => 'fail to set login key' 
);

if (_OP_) {
	switch (strtoupper(_OP_)) {
		
		// ---------------------- ADMIN TASKS ---------------------- //
		case "INJECT":
			if ($u = webservices_validate_admin($h, $u)) {
				$json = webservices_inject($u, $from, $msg, $recvnum, $smsc);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTADD":
			if ($u = webservices_validate_admin($h, $u)) {
				$data = array();
				foreach ($_REQUEST as $key => $value) {
					switch ($key) {
						case 'data_status':
						case 'data_parent':
						case 'data_username':
						case 'data_password':
						case 'data_name':
						case 'data_email':
						case 'data_mobile':
						case 'data_footer':
						case 'data_datetime_timezone':
						case 'data_language_module':
							$key_name = str_replace('data_', '', $key);
							$data[$key_name] = $value;
							break;
					}
				}
				if ($data['parent']) {
					$data['parent_uid'] = (int) user_username2uid($data['parent']);
					unset($data['parent']);
				}
				if ($data['status'] && $data['username'] && $data['password'] && $data['name'] && $data['email']) {
					$json = webservices_account_add($data);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '603';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTREMOVE":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				if ($data_uid) {
					$json = webservices_account_remove($data_uid);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '605';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "PARENTSET":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				$data_parent_uid = (int) user_username2uid($_REQUEST['data_parent']);
				if ($data_uid && $data_parent_uid) {
					$json = webservices_parent_set($data_uid, $data_parent_uid);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '607';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "PARENTGET":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				if ($data_uid) {
					$json = webservices_parent_get($data_uid);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '609';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTBAN":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				if ($data_uid) {
					$json = webservices_account_ban($data_uid);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '611';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTUNBAN":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				if ($data_uid) {
					$json = webservices_account_unban($data_uid);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '613';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTPREF":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				$fields = array(
					'name',
					'email',
					'mobile',
					'address',
					'city',
					'state',
					'country',
					'password',
					'zipcode' 
				);
				$data = array();
				foreach ($fields as $field) {
					if ($c_data = trim($_REQUEST['data_' . $field])) {
						$data[$field] = $c_data;
					}
				}
				if ($data_uid && count($data)) {
					$json = webservices_account_pref($data_uid, $data);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '615';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "ACCOUNTCONF":
			if ($u = webservices_validate_admin($h, $u)) {
				$data_uid = (int) user_username2uid($_REQUEST['data_username']);
				$fields = array(
					'footer',
					'datetime_timezone',
					'language_module',
					'fwd_to_inbox',
					'fwd_to_email',
					'fwd_to_mobile',
					'local_length',
					'replace_zero',
					'sender' 
				);
				$data = array();
				foreach ($fields as $field) {
					if (strlen(trim($_REQUEST['data_' . $field]))) {
						$data[$field] = trim($_REQUEST['data_' . $field]);
					}
				}
				if ($data_uid && count($data)) {
					$json = webservices_account_conf($data_uid, $data);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '617';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "CREDITVIEW":
			if ($u = webservices_validate_admin($h, $u)) {
				if ($data_username = $_REQUEST['data_username']) {
					$json = webservices_credit_view($data_username);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '619';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "CREDITADD":
			if ($u = webservices_validate_admin($h, $u)) {
				if ($data_username = $_REQUEST['data_username']) {
					$data_amount = (float) $_REQUEST['data_amount'];
					$json = webservices_credit_add($data_username, $data_amount);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '621';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "CREDITDEDUCT":
			if ($u = webservices_validate_admin($h, $u)) {
				if ($data_username = $_REQUEST['data_username']) {
					$data_amount = (float) $_REQUEST['data_amount'];
					$json = webservices_credit_deduct($data_username, $data_amount);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '623';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		case "LOGINKEYSET":
			if ($u = webservices_validate_admin($h, $u)) {
				if ($data_username = trim($_REQUEST['data_username'])) {
					$json = webservices_login_key_set($data_username);
				} else {
					$json['status'] = 'ERR';
					$json['error'] = '625';
				}
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '600';
			}
			$log_this = TRUE;
			break;
		
		// ------------------- INDIVIDUAL TASKS ------------------- //
		case "PV":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_pv($u, $to, $msg, $type, $unicode, $nofooter, $footer, $from, $schedule);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
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
			$user = array();
			
			if (preg_match('/^(.+)@(.+)\.(.+)$/', $u)) {
				if (auth_validate_email($u, $p)) {
					$u = user_email2username($u);
					$user = user_getdatabyusername($u);
				}
			} else {
				if (auth_validate_login($u, $p)) {
					$user = user_getdatabyusername($u);
				}
			}
			
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
						'flag_deleted' => 0,
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
		
		case "WS_LOGIN":
			$user = user_getdatabyusername($u);
			if ($c_uid = $user['uid']) {
				
				// supplied login key
				$login_key = trim($_REQUEST['login_key']);
				
				// saved login key
				$reg = registry_search($c_uid, 'core', 'webservices', 'login_key');
				$c_login_key = trim($reg['core']['webservices']['login_key']);
				
				// immediately remove saved login key, only proceed upon successful removal
				if (registry_remove($c_uid, 'core', 'webservices', 'login_key')) {
					
					// auth by comparing login keys
					if ($login_key && $c_login_key && ($login_key == $c_login_key)) {
						
						// setup login session
						auth_session_setup($c_uid);
						
						_log("webservices logged in u:" . $u . " ip:" . $_SERVER['REMOTE_ADDR'] . " op:" . _OP_, 3, "webservices");
					} else {
						_log("webservices invalid login u:" . $u . " ip:" . $_SERVER['REMOTE_ADDR'] . " op:" . _OP_, 3, "webservices");
					}
				} else {
					_log("webservices error unable to remove registry u:" . $u . " ip:" . $_SERVER['REMOTE_ADDR'] . " op:" . _OP_, 3, "webservices");
				}
			} else {
				_log("webservices invalid user u:" . $u . " ip:" . $_SERVER['REMOTE_ADDR'] . " op:" . _OP_, 3, "webservices");
			}
			
			// redirect to index.php no matter what
			header('Location: index.php');
			exit();
			break;
		
		case "QUERY":
			if ($u = webservices_validate($h, $u)) {
				$json = webservices_query($u);
			} else {
				$json['status'] = 'ERR';
				$json['error'] = '100';
			}
			$log_this = FALSE;
			break;
		
		default :
			if (_OP_) {
				
				// output do not require valid login
				// output must not be empty
				$ret = webservices_output(_OP_, $_REQUEST, $returns);
				
				if ($ret['modified'] && $ret['param']['content']) {
					ob_end_clean();
					if ($ret['param']['content-type'] && $ret['param']['charset']) {
						header('Content-type: ' . $ret['param']['content-type'] . '; charset=' . $ret['param']['charset']);
					}
					_p($ret['param']['content']);
				}
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
