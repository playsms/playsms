<?php
defined('_SECURE_') or die('Forbidden');

function webservices_pv($c_username,$to,$msg,$type='text',$unicode=0) {
	$ret = '';
	$arr_to = explode(',', $to);
	if ($c_username && $arr_to[1] && $msg) {
		// multiple destination
		list($ok,$to,$smslog_id,$queue_code) = sendsms($c_username,$arr_to,$msg,$type,$unicode);
		for ($i=0;$i<count($arr_to);$i++) {
			if ($ok[$i]==1 && $to[$i] && $smslog_id[$i]) {
				$ret .= "OK ".$to[$i].",".$smslog_id[$i].",".$queue_code[$i]."\n";
			} elseif ($ok[$i]==2) {
				$ret .= "ERR 103 ".$arr_to[$i]."\n";
			} else {
				$ret .= "ERR 200 ".$arr_to[$i]."\n";
			}
		}
	} elseif ($c_username && $to && $msg) {
		// single destination
		list($ok,$to,$smslog_id,$queue_code) = sendsms($c_username,$to,$msg,$type,$unicode);
		if ($ok[0]==1) {
			$ret = "OK ".$smslog_id[0].",".$queue_code[0];
		} elseif ($ok[0]==2) {
			$ret = "ERR 103";
		} else {
			$ret = "ERR 200";
		}
		logger_print("returns:".$ret." to:".$to[0]." smslog_id:".$smslog_id[0]." queue_code:".$queue_code[0], 2, "webservices_pv");
	} else {
		$ret = "ERR 201";
	}
	return $ret;
}

function webservices_bc($c_username,$c_gcode,$msg,$type='text',$unicode=0) {
	if (($c_uid = username2uid($c_username)) && $c_gcode && $msg) {
		$c_gpid = phonebook_groupcode2id($c_uid,$c_gcode);
		// sendsms_bc($c_username,$c_gpid,$message,$sms_type='text',$unicode=0)
		list($ok,$to,$smslog_id,$queue_code) = sendsms_bc($c_username,$c_gpid,$msg,$type,$unicode);
		if ($ok[0]) {
			$ret = "OK ".$queue_code[0];
		} else {
			$ret = "ERR 300";
		}
	} else {
		$ret = "ERR 301";
	}
	return $ret;
}

function webservices_ds($c_username,$queue_code='',$src='',$dst='',$datetime='',$slid=0,$c=100,$last=false) {
	$ret = "ERR 101";
	$uid = username2uid($c_username);
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
		$conditions['p_dst'] = $dst;
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
		$content = "";
		$list = dba_search(_DB_PREF_.'_tblSMSOutgoing', '*', $conditions, $keywords, $extras);
		foreach ($list as $db_row) {
			$smslog_id = $db_row['smslog_id'];
			$p_src = $db_row['p_src'];
			$p_dst = $db_row['p_dst'];
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$p_status = $db_row['p_status'];
			$content .= "\"$smslog_id\";\"$p_src\";\"$p_dst\";\"$p_datetime\";\"$p_update\";\"$p_status\";\n";
		}
		// if DS available by checking content
		if ($content) {
			$ret = $content;
		} else {
			if (dba_search(_DB_PREF_.'_tblSMSOutgoing_queue', 'id', array('queue_code' => $queue_code, 'flag' => 0))) {
				// exists in queue but not yet processed
				$ret = "ERR 401";
			} else if (dba_search(_DB_PREF_.'_tblSMSOutgoing_queue', 'id', array('queue_code' => $queue_code, 'flag' => 1))) {
				// exists in queue and have been processed
				$ret = "ERR 402";
			} else {
				// not exists anywhere, wrong query
				$ret = "ERR 400";
			}
		}
	}
	return $ret;
}

function webservices_cr($c_username) {
        $credit = rate_getusercredit($c_username);
        $credit = ( $credit ? $credit : '0' );
        $ret = "OK ".$credit;
        return $ret;
}

function webservices_output($ta,$requests) {
	$ta = strtolower($ta);
	$ret = x_hook($ta,'webservices_output',array($ta,$requests));
	return $ret;
}

?>
