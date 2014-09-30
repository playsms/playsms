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

/**
 * Validate webservices token, with or without username
 * 
 * @param $h Webservices
 *        	token
 * @param $u Username        	
 * @return boolean FALSE if invalid, string username if valid
 */
function webservices_validate($h, $u) {
	global $core_config;
	$ret = false;
	if ($c_uid = auth_validate_token($h)) {
		$c_u = user_uid2username($c_uid);
		if ($core_config['webservices_username']) {
			if ($c_u && $u && ($c_u == $u)) {
				$ret = $c_u;
			}
		} else {
			$ret = $c_u;
		}
	}
	return $ret;
}

/**
 * Validate admin level webservices token, with or without username
 * 
 * @param $h Webservices
 *        	token (admin users only)
 * @param $u Username
 *        	(admin users only)
 * @return boolean FALSE if invalid, string username if valid
 */
function webservices_validate_admin($h, $u) {
	$ret = false;
	
	$c_u = webservices_validate($h, $u);
	if ($u) {
		$status = user_getfieldbyusername($c_u, 'status');
		if ($status == 2) {
			$ret = $c_u;
		}
	}
	
	return $ret;
}

function webservices_pv($c_username, $to, $msg, $type = 'text', $unicode = 0, $nofooter = FALSE, $footer = '', $from = '', $schedule = '') {
	$ret = '';
	if ($c_username && $to && $msg) {
		list($ok, $to, $smslog_id, $queue_code, $counts, $sms_count, $sms_failed) = sendsms_helper($c_username, $to, $msg, $type, $unicode, $nofooter, $footer, $from, $schedule);
		for ($i = 0; $i < count($to); $i++) {
			if (($ok[$i] == 1 || $ok[$i] == true) && $to[$i] && ($queue_code[$i] || $smslog_id[$i])) {
				$json['data'][$i]['status'] = 'OK';
				$json['data'][$i]['error'] = '0';
			} elseif ($ok[$i] == 2) {
				
				// this doesn't work, but not much an issue now
				$json['data'][$i]['status'] = 'ERR';
				$json['data'][$i]['error'] = '103';
			} else {
				$json['data'][$i]['status'] = 'ERR';
				$json['data'][$i]['error'] = '200';
			}
			$json['data'][$i]['smslog_id'] = $smslog_id[$i];
			$json['data'][$i]['queue'] = $queue_code[$i];
			$json['data'][$i]['to'] = $to[$i];
		}
	} else {
		$json['status'] = 'ERR';
		$json['error'] = '201';
	}
	return $json;
}

function webservices_inject($c_username, $from, $msg, $recvnum = '', $smsc = '') {
	$ret = '';
	if ($from && $msg) {
		if ($c_username) {
			
			// inject message
			$sms_datetime = core_display_datetime(core_get_datetime());
			recvsms($sms_datetime, $from, $msg, $recvnum, $smsc);
			
			$json['status'] = 'OK';
			$json['error'] = '0';
		} else {
			$json['status'] = 'ERR';
			$json['error'] = '601';
		}
	} else {
		$json['status'] = 'ERR';
		$json['error'] = '602';
	}
	return $json;
}

function webservices_ds($c_username, $queue_code = '', $src = '', $dst = '', $datetime = '', $smslog_id = 0, $c = 100, $last = false) {
	$json['status'] = 'ERR';
	$json['error'] = '501';
	if ($uid = user_username2uid($c_username)) {
		$conditions['uid'] = $uid;
	}
	$conditions['flag_deleted'] = 0;
	if ($smslog_id) {
		$conditions['smslog_id'] = $smslog_id;
	}
	if ($queue_code) {
		$conditions['queue_code'] = $queue_code;
	}
	if ($src) {
		$conditions['p_src'] = $src;
	}
	if ($dst) {
		if ($dst[0] == '0') {
			$c_dst = substr($dst, 1);
		} else {
			$c_dst = substr($dst, 3);
		}
		$keywords['p_dst'] = '%' . $c_dst;
	}
	if ($datetime) {
		$keywords['p_datetime'] = '%' . $datetime . '%';
	}
	if ($last) {
		$extras['AND smslog_id'] = '>' . $last;
	}
	$extras['ORDER BY'] = 'p_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$j = 0;
		$list = dba_search(_DB_PREF_ . '_tblSMSOutgoing', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$smslog_id = $db_row['smslog_id'];
			$p_src = $db_row['p_src'];
			$p_dst = $db_row['p_dst'];
			$p_msg = str_replace('"', "'", $db_row['p_msg']);
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$p_status = $db_row['p_status'];
			$json['data'][$j]['smslog_id'] = $smslog_id;
			$json['data'][$j]['src'] = $p_src;
			$json['data'][$j]['dst'] = $p_dst;
			$json['data'][$j]['msg'] = $p_msg;
			$json['data'][$j]['dt'] = $p_datetime;
			$json['data'][$j]['update'] = $p_update;
			$json['data'][$j]['status'] = $p_status;
			$j++;
		}
		if ($j > 0) {
			unset($json['status']);
			unset($json['error']);
		} else {
			if (dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id', array(
				'queue_code' => $queue_code,
				'flag' => 0 
			))) {
				
				// exists in queue but not yet processed
				$json['status'] = 'ERR';
				$json['error'] = '401';
			} else if (dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id', array(
				'queue_code' => $queue_code,
				'flag' => 1 
			))) {
				
				// exists in queue and have been processed
				$json['status'] = 'ERR';
				$json['error'] = '402';
			} else {
				
				// not exists anywhere, wrong query
				$json['status'] = 'ERR';
				$json['error'] = '400';
			}
		}
	}
	return $json;
}

function webservices_in($c_username, $src = '', $dst = '', $kwd = '', $datetime = '', $c = 100, $last = false) {
	$json['status'] = 'ERR';
	$json['error'] = '501';
	$conditions = array(
		'flag_deleted' => 0,
		'in_status' => 1 
	);
	if ($uid = user_username2uid($c_username)) {
		$conditions['in_uid'] = $uid;
	}
	if ($src) {
		if ($src[0] == '0') {
			$c_src = substr($src, 1);
		} else {
			$c_src = substr($src, 3);
		}
		$keywords['in_sender'] = '%' . $c_src;
	}
	if ($dst) {
		$conditions['in_receiver'] = $dst;
	}
	if ($kwd) {
		$conditions['in_keyword'] = $kwd;
	}
	if ($datetime) {
		$keywords['in_datetime'] = '%' . $datetime . '%';
	}
	if ($last) {
		$extras['AND in_id'] = '>' . $last;
	}
	$extras['AND in_keyword'] = '!= ""';
	$extras['ORDER BY'] = 'in_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$j = 0;
		$list = dba_search(_DB_PREF_ . '_tblSMSIncoming', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$id = $db_row['in_id'];
			$src = $db_row['in_sender'];
			$dst = $db_row['in_receiver'];
			$kwd = $db_row['in_keyword'];
			$message = str_replace('"', "'", $db_row['in_message']);
			$datetime = $db_row['in_datetime'];
			$status = $db_row['in_status'];
			$json['data'][$j]['id'] = $id;
			$json['data'][$j]['src'] = $src;
			$json['data'][$j]['dst'] = $dst;
			$json['data'][$j]['kwd'] = $kwd;
			$json['data'][$j]['msg'] = $message;
			$json['data'][$j]['dt'] = $datetime;
			$json['data'][$j]['status'] = $status;
			$j++;
		}
		if ($j > 0) {
			unset($json['status']);
			unset($json['error']);
		}
	}
	return $json;
}

function webservices_sx($c_username, $src = '', $dst = '', $datetime = '', $c = 100, $last = false) {
	$json['status'] = 'ERR';
	$json['error'] = '501';
	$u = user_getdatabyusername($c_username);
	if ($u['status'] != 2) {
		return $json;
	}
	$uid = $u['uid'];
	$conditions = array(
		'flag_deleted' => 0,
		'in_status' => 0 
	);
	if ($src) {
		if ($src[0] == '0') {
			$c_src = substr($src, 1);
		} else {
			$c_src = substr($src, 3);
		}
		$keywords['in_sender'] = '%' . $c_src;
	}
	if ($dst) {
		$conditions['in_receiver'] = $dst;
	}
	if ($datetime) {
		$keywords['in_datetime'] = '%' . $datetime . '%';
	}
	if ($last) {
		$extras['AND in_id'] = '>' . $last;
	}
	$extras['ORDER BY'] = 'in_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$j = 0;
		$list = dba_search(_DB_PREF_ . '_tblSMSIncoming', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$id = $db_row['in_id'];
			$src = $db_row['in_sender'];
			$dst = $db_row['in_receiver'];
			$message = str_replace('"', "'", $db_row['in_message']);
			$datetime = $db_row['in_datetime'];
			$status = $db_row['in_status'];
			$json['data'][$j]['id'] = $id;
			$json['data'][$j]['src'] = $src;
			$json['data'][$j]['dst'] = $dst;
			$json['data'][$j]['msg'] = $message;
			$json['data'][$j]['dt'] = $datetime;
			$j++;
		}
		if ($j > 0) {
			unset($json['status']);
			unset($json['error']);
		}
	}
	return $json;
}

function webservices_ix($c_username, $src = '', $dst = '', $datetime = '', $c = 100, $last = false) {
	$json['status'] = 'ERR';
	$json['error'] = '501';
	$conditions['flag_deleted'] = 0;
	if ($uid = user_username2uid($c_username)) {
		$conditions['in_uid'] = $uid;
	}
	if ($src) {
		if ($src[0] == '0') {
			$c_src = substr($src, 1);
		} else {
			$c_src = substr($src, 3);
		}
		$keywords['in_sender'] = '%' . $c_src;
	}
	if ($dst) {
		$conditions['in_receiver'] = $dst;
	}
	if ($datetime) {
		$keywords['in_datetime'] = '%' . $datetime . '%';
	}
	if ($last) {
		$extras['AND in_id'] = '>' . $last;
	}
	$extras['ORDER BY'] = 'in_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$j = 0;
		$list = dba_search(_DB_PREF_ . '_tblUser_inbox', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$id = $db_row['in_id'];
			$src = $db_row['in_sender'];
			$dst = $db_row['in_receiver'];
			$message = str_replace('"', "'", $db_row['in_msg']);
			$datetime = $db_row['in_datetime'];
			$json['data'][$j]['id'] = $id;
			$json['data'][$j]['src'] = $src;
			$json['data'][$j]['dst'] = $dst;
			$json['data'][$j]['msg'] = $message;
			$json['data'][$j]['dt'] = $datetime;
			$j++;
		}
		if ($j > 0) {
			unset($json['status']);
			unset($json['error']);
		}
	}
	return $json;
}

function webservices_cr($c_username) {
	$credit = rate_getusercredit($c_username);
	$credit = ($credit ? $credit : '0');
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['credit'] = $credit;
	return $json;
}

function webservices_get_contact($c_uid, $name, $count) {
	$list = phonebook_search($c_uid, $name, $count);
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['data'] = $list;
	return $json;
}

function webservices_get_contact_group($c_uid, $name, $count) {
	$list = phonebook_search_group($c_uid, $name, $count);
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['data'] = $list;
	return $json;
}

function webservices_output($operation, $requests) {
	$operation = strtolower($operation);
	$ret = core_hook($operation, 'webservices_output', array(
		$operation,
		$requests 
	));
	return $ret;
}
