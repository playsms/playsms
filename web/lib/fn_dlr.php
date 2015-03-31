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

function dlr($smslog_id, $uid, $p_status) {
	global $core_config;
	if ($core_config['isdlrd']) {
		$c_isdlrd = 1;
		$ret = dba_add(_DB_PREF_ . '_tblDLR', array(
			'flag_processed' => 1,
			'smslog_id' => $smslog_id,
			'p_status' => $p_status,
			'uid' => $uid 
		));
	} else {
		$c_isdlrd = 0;
		$ret = dba_add(_DB_PREF_ . '_tblDLR', array(
			'flag_processed' => 2,
			'smslog_id' => $smslog_id,
			'p_status' => $p_status,
			'uid' => $uid 
		));
		setsmsdeliverystatus($smslog_id, $uid, $p_status);
	}
	logger_print("isdlrd:" . $c_isdlrd . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlr");
	return $ret;
}

function dlrd() {
	global $core_config;
	$core_config['dlrd_limit'] = ((int) $core_config['dlrd_limit'] ? (int) $core_config['dlrd_limit'] : 200);
	$list = dba_search(_DB_PREF_ . '_tblDLR', '*', array(
		'flag_processed' => 1 
	), '', array(
		'LIMIT' => $core_config['dlrd_limit'] 
	));
	$j = 0;
	for($j = 0; $j < count($list); $j++) {
		if ($id = $list[$j]['id']) {
			$smslog_id = $list[$j]['smslog_id'];
			$p_status = $list[$j]['p_status'];
			$uid = $list[$j]['uid'];
			if (dba_update(_DB_PREF_ . '_tblDLR', array(
				'flag_processed' => 2 
			), array(
				'id' => $id 
			))) {
				logger_print("id:" . $id . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlrd");
				setsmsdeliverystatus($smslog_id, $uid, $p_status);
			}
		}
	}
}

function setsmsdeliverystatus($smslog_id, $uid, $p_status) {
	global $core_config;
	// $p_status = 0 --> pending
	// $p_status = 1 --> sent
	// $p_status = 2 --> failed
	// $p_status = 3 --> delivered
	// logger_print("smslog_id:".$smslog_id." uid:".$uid." p_status:".$p_status, 2, "setsmsdeliverystatus");
	$ok = false;
	$db_query = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing SET c_timestamp='" . mktime() . "',p_update='" . core_get_datetime() . "',p_status='$p_status' WHERE smslog_id='$smslog_id' AND uid='$uid'";
	if ($aff_id = @dba_affected_rows($db_query)) {
		// logger_print("saved smslog_id:".$smslog_id, 2, "setsmsdeliverystatus");
		$ok = true;
		if ($p_status > 0) {
			for($c = 0; $c < count($core_config['featurelist']); $c++) {
				core_hook($core_config['featurelist'][$c], 'setsmsdeliverystatus', array(
					$smslog_id,
					$uid,
					$p_status 
				));
			}
		}
	}
	return $ok;
}

function getsmsstatus() {
	$smscs = gateway_getall_smsc_names();
	foreach ($smscs as $smsc) {	
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateway = $smsc_data['gateway'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status='0' AND p_smsc='$smsc' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$uid = $db_row['uid'];
			$smslog_id = $db_row['smslog_id'];
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$gpid = $db_row['p_gpid'];
			core_hook($gateway, 'getsmsstatus', array(
				$gpid,
				$uid,
				$smslog_id,
				$p_datetime,
				$p_update 
			));
		}
	}
}
