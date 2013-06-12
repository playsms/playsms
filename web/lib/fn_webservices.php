<?php
defined('_SECURE_') or die('Forbidden');

/**
 * Validate webservices token, with or without username
 * @param $h
 *     Webservices token
 * @param $u
 *     Username
 * @return boolean FALSE if invalid, string username if valid
 */
function webservices_validate($h,$u) {
	global $core_config;
	$ret = false;
	if ($c_uid = validatetoken($h)) {
		$c_u = uid2username($c_uid);
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

function webservices_pv($c_username,$to,$msg,$type='text',$unicode=0) {
	$ret = '';
	$arr_to = explode(',', $to);
	if ($c_username && $arr_to[1] && $msg) {
		// multiple destination
		list($ok,$to,$smslog_id,$queue_code) = sendsms($c_username,$arr_to,$msg,$type,$unicode);
		for ($i=0;$i<count($to);$i++) {
			if (($ok[$i]==1 || $ok[$i]==true) && $to[$i] && ($queue_code[$i] || $smslog_id[$i])) {
				$ret .= "OK ".$smslog_id[$i].",".$queue_code[$i].",".$to[$i]."\n";
				$json['data'][$i]['status'] = 'OK';
				$json['data'][$i]['error'] = '0';
				$json['data'][$i]['slid'] = $smslog_id[$i];
				$json['data'][$i]['queue'] = $queue_code[$i];
				$json['data'][$i]['to'] = $to[$i];
			} elseif ($ok[$i]==2) { // this doesn't work, but not much an issue now
				$ret .= "ERR 103 ".$arr_to[$i]."\n";
				$json['data'][$i]['status'] = 'ERR';
				$json['data'][$i]['error'] = '103';
				$json['data'][$i]['slid'] = '';
				$json['data'][$i]['queue'] = '';
				$json['data'][$i]['to'] = $to[$i];
			} else {
				$ret .= "ERR 200 ".$arr_to[$i]."\n";
				$json['data'][$i]['status'] = 'ERR';
				$json['data'][$i]['error'] = '200';
				$json['data'][$i]['slid'] = '';
				$json['data'][$i]['queue'] = '';
				$json['data'][$i]['to'] = $to[$i];
			}
		}
		$json['multi'] = true;
	} elseif ($c_username && $to && $msg) {
		// single destination
		list($ok,$to,$smslog_id,$queue_code) = sendsms($c_username,$to,$msg,$type,$unicode);
		if ($ok[0]==1) {
			$ret = "OK ".$smslog_id[0].",".$queue_code[0].",".$to[0];
			$json['status'] = 'OK';
			$json['error'] = '0';
			$json['slid'] = $smslog_id[0];
			$json['queue'] = $queue_code[0];
			$json['to'] = $to[0];
		} elseif ($ok[0]==2) {
			$ret = "ERR 103";
			$json['status'] = 'ERR';
			$json['error'] = '103';
		} else {
			$ret = "ERR 200";
			$json['status'] = 'ERR';
			$json['error'] = '200';
		}
		logger_print("returns:".$ret." to:".$to[0]." smslog_id:".$smslog_id[0]." queue_code:".$queue_code[0], 2, "webservices_pv");
	} else {
		$ret = "ERR 201";
		$json['status'] = 'ERR';
		$json['error'] = '201';
	}
	return array($ret, $json);
}

function webservices_bc($c_username,$c_gcode,$msg,$type='text',$unicode=0) {
	if (($c_uid = username2uid($c_username)) && $c_gcode && $msg) {
		$c_gpid = phonebook_groupcode2id($c_uid,$c_gcode);
		// sendsms_bc($c_username,$c_gpid,$message,$sms_type='text',$unicode=0)
		list($ok,$to,$smslog_id,$queue_code) = sendsms_bc($c_username,$c_gpid,$msg,$type,$unicode);
		if ($ok[0]) {
			$ret = "OK ".$queue_code[0];
			$json['status'] = 'OK';
			$json['error'] = '0';
			$json['queue'] = $queue_code[0];
		} else {
			$ret = "ERR 300";
			$json['status'] = 'ERR';
			$json['error'] = '300';
		}
	} else {
		$ret = "ERR 301";
		$json['status'] = 'ERR';
		$json['error'] = '301';
	}
	return array($ret, $json);
}

function webservices_ds($c_username,$queue_code='',$src='',$dst='',$datetime='',$slid=0,$c=100,$last=false) {
	$ret = "ERR 101";
	$json['status'] = 'ERR';
	$json['error'] = '101';
	$uid = username2uid($c_username);
	$conditions['flag_deleted'] = 0;
	if ($slid) {
		$conditions['smslog_id'] = $slid;
	}
	if ($queue_code) {
		$conditions['queue_code'] = $queue_code;
	}
	if ($src) {
		$conditions['p_src'] = $src;
	}
	if ($dst) {
		if ($dst[0]=='0') {
			$c_dst = substr($dst, 1);
		} else {
			$c_dst = substr($dst, 3);
		}
		$keywords['p_dst'] = '%'.$c_dst;
	}
	if ($datetime) {
		$keywords['p_datetime'] = '%'.$datetime.'%';
	}
	if ($last) {
		$extras['AND smslog_id'] = '>'.$last;
	}
	$extras['ORDER BY'] = 'p_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$content = '';
		$j = 0;
		$list = dba_search(_DB_PREF_.'_tblSMSOutgoing', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$smslog_id = $db_row['smslog_id'];
			$p_src = $db_row['p_src'];
			$p_dst = $db_row['p_dst'];
			$p_msg = str_replace('"', "'", $db_row['p_msg']);
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$p_status = $db_row['p_status'];
			$content .= "\"$smslog_id\";\"$p_src\";\"$p_dst\";\"$p_msg\";\"$p_datetime\";\"$p_update\";\"$p_status\"\n";
			$json['data'][$j]['slid'] = $smslog_id;
			$json['data'][$j]['src'] = $p_src;
			$json['data'][$j]['dst'] = $p_dst;
			$json['data'][$j]['msg'] = $p_msg;
			$json['data'][$j]['dt'] = $p_datetime;
			$json['data'][$j]['update'] = $p_update;
			$json['data'][$j]['status'] = $p_status;
			$j++;
		}
		// if DS available by checking content
		if ($content) {
			$ret = $content;
			unset($json['status']);
			unset($json['error']);
			$json['multi'] = true;
		} else {
			if (dba_search(_DB_PREF_.'_tblSMSOutgoing_queue', 'id', array('queue_code' => $queue_code, 'flag' => 0))) {
				// exists in queue but not yet processed
				$ret = "ERR 401";
				$json['status'] = 'ERR';
				$json['error'] = '401';
			} else if (dba_search(_DB_PREF_.'_tblSMSOutgoing_queue', 'id', array('queue_code' => $queue_code, 'flag' => 1))) {
				// exists in queue and have been processed
				$ret = "ERR 402";
				$json['status'] = 'ERR';
				$json['error'] = '402';
			} else {
				// not exists anywhere, wrong query
				$ret = "ERR 400";
				$json['status'] = 'ERR';
				$json['error'] = '400';
			}
		}
	}
	return array($ret, $json);
}

function webservices_in($c_username,$src='',$dst='',$kwd='',$datetime='',$c=100,$last=false) {
	$ret = "ERR 101";
	$json['status'] = 'ERR';
	$json['error'] = '101';
	$uid = username2uid($c_username);
	$conditions['flag_deleted'] = 0;
	if ($src) {
		if ($src[0]=='0') {
			$c_src = substr($src, 1);
		} else {
			$c_src = substr($src, 3);
		}
		$keywords['in_sender'] = '%'.$c_src;
	}
	if ($dst) {
		$conditions['in_receiver'] = $dst;
	}
	if ($kwd) {
		$conditions['in_keyword'] = $kwd;
	}
	if ($datetime) {
		$keywords['in_datetime'] = '%'.$datetime.'%';
	}
	if ($last) {
		$extras['AND in_id'] = '>'.$last;
	}
	$extras['ORDER BY'] = 'in_datetime DESC';
	if ($c) {
		$extras['LIMIT'] = $c;
	} else {
		$extras['LIMIT'] = 100;
	}
	if ($uid) {
		$content = '';
		$j = 0;
		$list = dba_search(_DB_PREF_.'_tblSMSIncoming', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$id = $db_row['in_id'];
			$src = $db_row['in_sender'];
			$dst = $db_row['in_receiver'];
			$kwd = $db_row['in_keyword'];
			$message = str_replace('"', "'", $db_row['in_message']);
			$datetime = $db_row['in_datetime'];
			$status = $db_row['in_status'];
			$content .= "\"$id\";\"$src\";\"$dst\";\"$kwd\";\"$message\";\"$datetime\";\"$status\"\n";
			$json['data'][$j]['id'] = $id;
			$json['data'][$j]['src'] = $src;
			$json['data'][$j]['dst'] = $dst;
			$json['data'][$j]['kwd'] = $kwd;
			$json['data'][$j]['msg'] = $message;
			$json['data'][$j]['dt'] = $datetime;
			$json['data'][$j]['status'] = $status;
			$j++;
		}
		// if DS available by checking content
		if ($content) {
			$ret = $content;
			unset($json['status']);
			unset($json['error']);
			$json['multi'] = true;
		}
	}
	return array($ret, $json);
}

function webservices_cr($c_username) {
	$credit = rate_getusercredit($c_username);
	$credit = ( $credit ? $credit : '0' );
	$ret = "OK ".$credit;
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['credit'] = $credit;
	return array($ret, $json);
}

function webservices_get_contact($c_uid, $name, $count) {
	$ret = '';
	$list = phonebook_search($c_uid, $name, $count);
	foreach ($list as $db_row) {
		if ($db_row['pid']) {
			$ret .= '"'.$db_row['pid'].'",';
			$ret .= '"'.$db_row['gpid'].'",';
			$ret .= '"'.$db_row['p_desc'].'",';
			$ret .= '"'.$db_row['p_num'].'",';
			$ret .= '"'.$db_row['email'].'",';
			$ret .= '"'.$db_row['group_name'].'",';
			$ret .= '"'.$db_row['code'].'"'."\n";
		}
	}
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['data'] = $list;
	$json['multi'] = true;
	return array($ret, $json);
}

function webservices_get_contact_group($c_uid, $name, $count) {
	$ret = '';
	$list = phonebook_search_group($c_uid, $name, $count);
	foreach ($list as $db_row) {
		if ($db_row['gpid']) {
			$ret .= '"'.$db_row['gpid'].'",';
			$ret .= '"'.$db_row['group_name'].'",';
			$ret .= '"'.$db_row['code'].'"'."\n";
		}
	}
	$json['status'] = 'OK';
	$json['error'] = '0';
	$json['data'] = $list;
	$json['multi'] = true;
	return array($ret, $json);
}

function webservices_output($ta,$requests) {
	$ta = strtolower($ta);
	$ret = x_hook($ta,'webservices_output',array($ta,$requests));
	return $ret;
}

?>