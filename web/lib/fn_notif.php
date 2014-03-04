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
 * Add notification
 * @param integer $uid User ID
 * @param string $subject Notification subject
 * @param string $body Notification body
 * @return boolean
 */
function notif_add($uid, $subject, $body, $flag=0) {
	$ret = FALSE;
	if ($id = md5(_PID_.$uid.$subject.$body)) {
		$items = array(
		    'dt' => core_get_datetime(),
		    'subject' => $subject,
		    'body' => $body,
		    'flag_unread' => 1);
		if ($ret = registry_update($uid, 'notif', $id, $items)) {
			logger_print('uid:'.$uid.' id:'.$id.' subject:'.$subject, 2, 'notif_add');
		}
	}
	return $ret;
}

/**
 * Remove notification
 * @param integer $uid User ID
 * @param string $id Notification ID
 * @return boolean
 */
function notif_remove($uid, $id) {
	$ret = FALSE;
	if ($ret = registry_remove($uid, 'notif', $id)) {
		logger_print('uid:'.$uid.' id:'.$id, 2, 'notif_remove');
	}
	return $ret;
}

/**
 * Update notification
 * @param integer $uid User ID
 * @param string $id Notification ID
 * @param array $data Data
 * @return boolean
 */
function notif_update($uid, $id, $data) {
	$ret = FALSE;
	$replaced = '';
	$result = registry_search($uid, 'notif', $id);
	$current_data = $result['notif'][$id];
	foreach ($data as $key => $val) {
		$current_data[$key] = $val;
		$replaced = $key.':'.$val.' ';
	}
	if ($ret = registry_update($uid, 'notif', $id, $current_data)) {
		logger_print('uid:'.$uid.' id:'.$id.' '.trim($replaced), 2, 'notif_update');
	}
	return $ret;
}
