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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

/**
 * Post billing statement
 * @param integer $smslog_id
 * @param float $rate
 * @param float $credit
 * @return boolean TRUE if posted
 */
function billing_post($smslog_id,$rate,$credit,$count,$charge) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Rollback a posted billing statement
 * @param integer $smslog_id SMS log ID
 * @return boolean TRUE if rollback succeeded
 */
function billing_rollback($smslog_id) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Set status that billing process is finalized, called from setsmsdeliverystatus
 * @param integer $smslog_id SMS log ID
 * @return boolean TRUE if finalization succeeded
 */
function billing_finalize($smslog_id) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get billing data or information for specific SMS log ID
 * @param integer $smslog_id SMS log ID
 * @return array Billing information
 */
function billing_getdata($smslog_id) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get all billing data from specific User ID
 * @param integer $uid User ID
 * @return array Billing information
 */
function billing_getdata_by_uid($uid) {
	$ret = core_call_hook();
	return $ret;
}
