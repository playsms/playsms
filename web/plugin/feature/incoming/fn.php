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

/*
 * intercept on after-process stage for incoming sms and forward it to selected user's inbox
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @param $sms_receiver
 *   receiver number that is receiving incoming SMS
 * @param $feature
 *   feature managed to hook current incoming SMS
 * @param $status
 *   recvsms() status, 0 or FALSE for unhandled
 * @param $uid
 *   keyword owner
 * @return
 *   array $ret
*/
function incoming_hook_recvsms_intercept_after($sms_datetime, $sms_sender, $message, $sms_receiver, $feature, $status, $uid) {
	global $core_config;
	
	$ret = array();
	$users = array();
	
	if (!$status) {
		$data = registry_search(1, 'feature', 'incoming', 'sandbox_forward_to');
		$sandbox_forward_to = array_unique(unserialize($data['feature']['incoming']['sandbox_forward_to']));
		foreach ($sandbox_forward_to as $uid) {
			$c_username = user_uid2username($uid);
			if ($c_username) {
				$users[] = $c_username;
			}
		}
		
		foreach ($users as $username) {
			logger_print("start u:" . $username . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:" . $message, 3, "incoming");
			recvsms_inbox_add($sms_datetime, $sms_sender, $username, $message, $sms_receiver);
			logger_print("end", 3, "incoming");
		}
		
		$ret['feature'] = 'incoming';
		$ret['status'] = TRUE;
		$ret['uid'] = 1;
		$ret['hooked'] = TRUE;
	}
	
	return $ret;
}
