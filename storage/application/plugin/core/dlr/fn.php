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
			'c_timestamp' => time(),
			'flag_processed' => 1,
			'smslog_id' => $smslog_id,
			'p_status' => $p_status,
			'uid' => $uid 
		));
	} else {
		$c_isdlrd = 0;
		$ret = dba_add(_DB_PREF_ . '_tblDLR', array(
			'c_timestamp' => time(),
			'flag_processed' => 2,
			'smslog_id' => $smslog_id,
			'p_status' => $p_status,
			'uid' => $uid 
		));
		dlr_update($smslog_id, $uid, $p_status);
	}
	_log("isdlrd:" . $c_isdlrd . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlr");
	return $ret;
}

function dlr_daemon() {
	global $core_config;
	$core_config['dlrd_limit'] = ((int) $core_config['dlrd_limit'] ? (int) $core_config['dlrd_limit'] : 200);
	$list = dba_search(_DB_PREF_ . '_tblDLR', '*', array(
		'flag_processed' => 1 
	), '', array(
		'LIMIT' => $core_config['dlrd_limit'] 
	));
	$j = 0;
	$tmpCount = $list ? count($list) : 0;
	for ($j = 0; $j < $tmpCount; $j++) {
		if ($id = $list[$j]['id']) {
			$smslog_id = $list[$j]['smslog_id'];
			$p_status = $list[$j]['p_status'];
			$uid = $list[$j]['uid'];
			if (dba_update(_DB_PREF_ . '_tblDLR', array(
				'flag_processed' => 2 
			), array(
				'id' => $id 
			))) {
				// debug only, too noisy
				//_log("id:" . $id . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlr_daemon");
				dlr_update($smslog_id, $uid, $p_status);
			}
		}
	}
}

function dlr_update($smslog_id, $uid, $p_status) {
	global $core_config;
	// $p_status = 0 --> pending
	// $p_status = 1 --> sent
	// $p_status = 2 --> failed
	// $p_status = 3 --> delivered
	// _log("smslog_id:".$smslog_id." uid:".$uid." p_status:".$p_status, 2, "dlr_update");
	
	// fixme anton
	// dlr can be pushed by SMSC several times and sometime they're not in order
	// so here we add logic to make them in order

	switch ((int) $p_status) {
		case 0:

			return false;
			break;
		case 1:
			$db_query = "
				UPDATE " . _DB_PREF_ . "_tblSMSOutgoing 
				SET c_timestamp='" . time() . "',p_update='" . core_get_datetime() . "',p_status='" . $p_status . "' 
				WHERE smslog_id='" . $smslog_id . "' AND p_status=0";
			break;
		case 2:
			$db_query = "
				UPDATE " . _DB_PREF_ . "_tblSMSOutgoing 
				SET c_timestamp='" . time() . "',p_update='" . core_get_datetime() . "',p_status='" . $p_status . "' 
				WHERE smslog_id='" . $smslog_id . "' AND p_status<>2";
			break;
		case 3:
			$db_query = "
				UPDATE " . _DB_PREF_ . "_tblSMSOutgoing 
				SET c_timestamp='" . time() . "',p_update='" . core_get_datetime() . "',p_status='" . $p_status . "' 
				WHERE smslog_id='" . $smslog_id . "' AND (p_status=0 OR p_status=1)";
			break;
	}

	if (dba_affected_rows($db_query)) {	
		if ($p_status > 0) {
			_log("smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlr_update");
			$tmpCount = $core_config['plugins']['list']['feature'] ? count($core_config['plugins']['list']['feature']) : 0;
			for ($c = 0; $c < $tmpCount; $c++) {
				core_hook($core_config['plugins']['list']['feature'][$c], 'dlr_update', array(
					$smslog_id,
					$uid,
					$p_status 
				));
			}
		}
	} else {

		return false;
	}

	return true;
}

function dlr_fetch() {
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
			core_hook($gateway, 'dlr_fetch', array(
				$gpid,
				$uid,
				$smslog_id,
				$p_datetime,
				$p_update 
			));
		}
	}
}

function dlr_hook_playsmsd_loop($command, $command_param) {
	if ($command != 'dlrssmsd') {
	
		return;
	}
	
	dlr_daemon();
	dlr_fetch();
}
