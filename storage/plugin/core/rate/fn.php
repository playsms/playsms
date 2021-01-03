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

function rate_getbyprefix($sms_to) {
	return core_call_hook();
}

/**
 * Calculate user's credit balance and save it in user's credit field
 */
function rate_update() {
	return core_call_hook();
}

function rate_getusercredit($username) {
	return core_call_hook();
}

function rate_addusercredit($uid, $amount = 0) {
	return core_call_hook();
}

function rate_deductusercredit($uid, $amount = 0) {
	return core_call_hook();
}

function rate_getcharges($uid, $sms_len, $unicode, $sms_to) {
	$ret = core_call_hook();

	// fixme anton - we'll calculate count, rate and charge if no hook found	
	// count, rate, charge must exists (can be zero)
	if (!(isset($ret[0]) && isset($ret[1]) && isset($ret[2]))) {
		// default length per SMS
		$length = ($unicode ? 70 : 160);

		// connector pdu length
		$minus = ($unicode ? 3 : 7);

		// count unicodes as normal SMS
		$user = user_getdatabyuid($uid);
		if ($unicode && $user['opt']['enable_credit_unicode']) {
			$length = 140;
		}

		// get sms count
		$count = 1;
		if ($sms_len > $length) {
			$count = ceil($sms_len / ($length - $minus));
		}

		// calculate charges
		$rate = 0;
		$charge = 0;

		_log('uid:' . $uid . ' u:' . $user['username'] . ' len:' . $sms_len . ' unicode:' . $unicode . ' to:' . $sms_to . ' enable_credit_unicode:' . (int) $user['opt']['enable_credit_unicode'] . ' count:' . $count . ' rate:' . $rate . ' charge:' . $charge, 3, 'rate_getcharges');

		$ret = array(
			$count,
			$rate,
			$charge
		);
	}
	
	return $ret;
}

function rate_cansend($username, $sms_len, $unicode, $sms_to) {
	return core_call_hook();
}

function rate_deduct($smslog_id) {
	return core_call_hook();
}

function rate_refund($smslog_id) {
	return core_call_hook();
}
