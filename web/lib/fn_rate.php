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

function rate_setusercredit($uid, $balance = 0) {
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
	return core_call_hook();
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
