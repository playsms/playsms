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
 * Set delivery report
 * 
 * @param int $smslog_id
 * @param int $uid
 * @param int $p_status
 * @return int tblDlr DB insert ID
 */
function dlr($smslog_id, $uid, $p_status)
{
	global $core_config;

	$ret = 0;

	if ($core_config['isdlrd']) {
		$c_isdlrd = 1;
		$ret = dba_add(
			_DB_PREF_ . '_tblDLR',
			[
				'c_timestamp' => time(),
				'flag_processed' => 1,
				'smslog_id' => $smslog_id,
				'p_status' => $p_status,
				'uid' => $uid
			]
		);
	} else {
		$c_isdlrd = 0;
		$ret = dba_add(
			_DB_PREF_ . '_tblDLR',
			[
				'c_timestamp' => time(),
				'flag_processed' => 2,
				'smslog_id' => $smslog_id,
				'p_status' => $p_status,
				'uid' => $uid
			]
		);
		setsmsdeliverystatus($smslog_id, $uid, $p_status);
	}

	_log("isdlrd:" . $c_isdlrd . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlr");

	return $ret;
}

function dlrd()
{
	global $core_config;

	$c_dlrd_limit = (int) $core_config['dlrd_limit'] > 0 ? (int) $core_config['dlrd_limit'] : 1000;
	$db_query = "SELECT id, smslog_id, p_status, uid FROM " . _DB_PREF_ . "_tblDLR WHERE flag_processed=1 LIMIT " . $c_dlrd_limit;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($id = $db_row['id']) {
			$smslog_id = $db_row['smslog_id'];
			$p_status = $db_row['p_status'];
			$uid = $db_row['uid'];
			if (dba_update(_DB_PREF_ . '_tblDLR', ['flag_processed' => 2], ['id' => $id])) {
				_log("id:" . $id . " smslog_id:" . $smslog_id . " p_status:" . $p_status . " uid:" . $uid, 3, "dlrd");
				setsmsdeliverystatus($smslog_id, $uid, $p_status);
			}
		}
	}
}

/**
 * Set delivery status for SMS outgoing and runs through hooks
 * 
 * @param int $smslog_id
 * @param int $uid
 * @param int $p_status
 * @return bool
 */
function setsmsdeliverystatus($smslog_id, $uid, $p_status)
{
	global $core_config;

	$ok = false;

	// $p_status = 0 --> pending
	// $p_status = 1 --> sent
	// $p_status = 2 --> failed
	// $p_status = 3 --> delivered
	// _log("smslog_id:".$smslog_id." uid:".$uid." p_status:".$p_status, 2, "setsmsdeliverystatus");

	$db_query = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing SET c_timestamp=?,p_update=?,p_status=? WHERE smslog_id=? AND uid=?";
	if (dba_affected_rows($db_query, [time(), core_get_datetime(), $p_status, $smslog_id, $uid])) {
		// _log("saved smslog_id:".$smslog_id, 2, "setsmsdeliverystatus");
		$ok = true;
		if ($p_status > 0) {
			if (isset($core_config['plugins']['list']['feature']) && is_array($core_config['plugins']['list']['feature'])) {
				for ($c = 0; $c < count($core_config['plugins']['list']['feature']); $c++) {
					core_hook(
						$core_config['plugins']['list']['feature'][$c],
						'setsmsdeliverystatus',
						[
							$smslog_id,
							$uid,
							$p_status
						]
					);
				}
			}
		}
	}

	return $ok;
}

function getsmsstatus()
{
	global $core_config;

	$c_dlrd_limit = (int) $core_config['dlrd_limit'] > 0 ? (int) $core_config['dlrd_limit'] : 1000;
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateway = $smsc_data['gateway'];
		$db_query = "SELECT uid, smslog_id, p_datetime, p_update, p_gpid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status=0 AND flag_deleted=0 AND p_smsc=? LIMIT " . $c_dlrd_limit;
		$db_result = dba_query($db_query, [$smsc]);
		while ($db_row = dba_fetch_array($db_result)) {
			$uid = $db_row['uid'];
			$smslog_id = $db_row['smslog_id'];
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$gpid = $db_row['p_gpid'];
			core_hook($gateway, 'getsmsstatus', [
				$gpid,
				$uid,
				$smslog_id,
				$p_datetime,
				$p_update
			]);
		}
	}
}
