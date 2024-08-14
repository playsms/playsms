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
 * Format prefix
 * 
 * @param string $prefix comma-separated prefixes
 * @return string formatted string
 */
function outgoing_prefix_format($prefix)
{
	$prefix = preg_replace('/[^\d,]+/', '', $prefix);
	$prefix = preg_replace('/,$/', '', $prefix);

	return $prefix;
}

/**
 * Get user ID
 * 
 * @param int $id
 * @return int
 */
function outgoing_getuid($id)
{
	$ret = 0;

	if ($id) {
		if ($row = dba_search(_DB_PREF_ . "_featureOutgoing", 'uid', ['id' => $id])) {
			$ret = $row[0]['uid'];
		}
	}

	return $ret;
}

/**
 * Get destination label
 * 
 * @param int $id
 * @return string
 */
function outgoing_getdst($id)
{
	$ret = '';

	if ($id) {
		if ($row = dba_search(_DB_PREF_ . "_featureOutgoing", 'dst', ['id' => $id])) {
			$ret = $row[0]['dst'];
		}
	}

	return $ret;
}

/**
 * Get prefix
 * 
 * @param int $id
 * @return string comma-separated prefixes
 */
function outgoing_getprefix($id)
{
	$ret = '';

	if ($id) {
		if ($row = dba_search(_DB_PREF_ . "_featureOutgoing", 'prefix', ['id' => $id])) {
			$prefixes = explode(',', $row[0]['prefix']);
			$prefixes = is_array($prefixes) ? $prefixes : [];
			foreach ( $prefixes as $prefix ) {
				if ($prefix = core_sanitize_numeric($prefix)) {
					$ret .= $prefix . ',';
				}
			}
			if ($ret) {
				$ret = substr($ret, 0, -1);
			}
		}
	}

	return $ret;
}

/**
 * Get SMSC
 * 
 * @param int $id
 * @return string
 */
function outgoing_getsmsc($id)
{
	$ret = '';

	if ($id) {
		if ($row = dba_search(_DB_PREF_ . "_featureOutgoing", 'smsc', ['id' => $id])) {
			$ret = $row[0]['smsc'];
		}
	}

	return $ret;
}

/**
 * Get list of SMSC by prefix
 * 
 * @param string $prefix
 * @param int $uid
 * @return array
 */
function outgoing_prefix2smsc($prefix, $uid = 0)
{
	$ret = [];

	if (!$uid) {

		return $ret;
	}

	$prefix = core_sanitize_numeric($prefix);
	if (strlen($prefix) > 8) {
		$prefix = substr($prefix, 0, 8);
	}

	$db_query = "SELECT smsc,prefix FROM " . _DB_PREF_ . "_featureOutgoing WHERE uid=0 OR uid=?";
	$db_result = dba_query($db_query, [$uid]);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_prefixes = explode(',', $db_row['prefix']);
		foreach ( $c_prefixes as $c_prefix ) {
			if ($c_prefix && $c_prefix == $prefix) {
				$ret[] = $db_row['smsc'];
			}
		}
	}

	if ($ret) {
		sort($ret);
		$ret = array_unique($ret);
	}

	return $ret;
}

/**
 * Get list of SMSC by mobile phone number
 * 
 * @param string $mobile
 * @param int $uid
 * @return array
 */
function outgoing_mobile2smsc($mobile, $uid = 0)
{
	$mobile = core_sanitize_numeric($mobile);
	if (strlen($mobile) > 8) {
		$prefix = substr($mobile, 0, 8);
	} else {
		$prefix = $mobile;
	}

	if (!$prefix) {

		return [];
	}

	for ($i = strlen($prefix); $i > 0; $i--) {
		$c_prefix = substr($mobile, 0, $i);
		if ($smsc = outgoing_prefix2smsc($c_prefix, $uid)) {

			return $smsc;
		}
	}

	return [];
}

/**
 * Modify SMSC value during SMS delivery
 * This function hooks sendsms_process()
 * 
 * @param mixed $sms_sender
 * @param mixed $sms_footer
 * @param mixed $sms_to
 * @param mixed $sms_msg
 * @param mixed $uid
 * @param mixed $gpid
 * @param mixed $sms_type
 * @param mixed $unicode
 * @param mixed $queue_code
 * @param mixed $smsc
 * @return array
 */
function outgoing_hook_sendsms_process_before($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid, $sms_type, $unicode, $queue_code, $smsc)
{
	$ret = [];

	// supplied smsc will be priority
	if ($smsc && (!($smsc == '_smsc_routed_' || $smsc == '_smsc_supplied_'))) {
		_log('using supplied smsc smsc:[' . $smsc . '] uid:' . $uid . ' from:' . $sms_sender . ' to:' . $sms_to, 3, 'outgoing_hook_sendsms_process_before');

		return $ret;
	}

	// if subuser then use parent_uid
	$the_uid = $uid;
	$parent_uid = 0;
	$user = user_getdatabyuid($uid);
	if ($user['status'] == 4) {
		$parent_uid = $user['parent_uid'];
		$the_uid = $parent_uid;
	}

	$smsc_list = outgoing_mobile2smsc($sms_to, $the_uid);
	$smsc_all = '';
	$smsc_found = [];
	foreach ( $smsc_list as $item_smsc ) {
		$smsc_all .= '[' . $item_smsc . '] ';
		$smsc_found[] = $item_smsc;
	}
	if (count($smsc_found) > 0) {
		$smsc_all = trim($smsc_all);
		shuffle($smsc_found);
		if ($smsc = $smsc_found[0]) {
			_log('found SMSCs:' . $smsc_all, 3, 'outgoing_hook_sendsms_process_before');
			_log('using prefix based smsc smsc:[' . $smsc . '] uid:' . $uid . ' parent_uid:' . $parent_uid . ' from:' . $sms_sender . ' to:' . $sms_to, 3, 'outgoing_hook_sendsms_process_before');

			$ret['modified'] = true;
			$ret['param']['smsc'] = $smsc;
		}
	}

	if (!$ret) {
		_log('no SMSC found uid:' . $uid . ' parent_uid:' . $parent_uid . ' from:' . $sms_sender . ' to:' . $sms_to, 3, 'outgoing_hook_sendsms_process_before');
	}

	return $ret;
}
