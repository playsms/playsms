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
 * Get mobile by id
 *
 * @param string $id        
 * @return mobile
 */
function stoplist_getip($id) {
	$condition = array(
		'id' => $id 
	);
	$row = dba_search(_DB_PREF_ . '_featureStoplist', 'mobile', $condition);
	$ret = $row[0]['mobile'];
	
	return $ret;
}

/**
 * Check if mobile deserved to get listed in blacklist, if deserved then blacklist_mobile_add()
 *
 * @param string $label
 *        single label, can be $username or $uid, its up to the implementator
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on checked (not necessarily added)
 */
function stoplist_hook_blacklist_checkip($label, $mobile) {
	global $core_config, $plugin_config;
	$ret = FALSE;
	
	if ((int) $core_config['main']['brute_force_detection']) {
		$hash = md5($label . $mobile);
		$data = registry_search(0, 'feature', 'stoplist');
		$login_attempt = $data['feature']['stoplist'][$hash];
		
		if ($login_attempt >= $plugin_config['stoplist']['login_attempt_limit']) {
			blacklist_mobile_add($label, $mobile);
		}
		
		$items[$hash] = $login_attempt ? $login_attempt + 1 : 1;
		if (registry_update(0, 'feature', 'stoplist', $items)) {
			$ret = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Reset mobile login attempt counter
 *
 * @param string $label
 *        single label, can be $username or $uid, its up to the implementator
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on resetted counter
 */
function stoplist_hook_blacklist_clearip($label, $mobile) {
	$ret = FALSE;
	
	$hash = md5($label . $mobile);
	if (registry_remove(0, 'feature', 'stoplist', $hash)) {
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Add mobile to blacklist
 *
 * @param string $label
 *        single label, can be $username or $uid, its up to the implementator
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on added
 */
function stoplist_hook_blacklist_mobile_add($label, $mobile) {
	$ret = FALSE;
	
	$uid = $label;
	$db_query = "
			INSERT INTO " . _DB_PREF_ . "_featureStoplist (uid, mobile)
			VALUES ('$uid', '$mobile')";
	if (!blacklist_mobile_isexists($label, $mobile)) {
		$new_ip = @dba_insert_id($db_query);
		if ($new_ip) {
			_log('add IP to blacklist ip:' . $new_ip . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_add');
			$ret = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Remove mobile from blacklist
 *
 * @param string $label
 *        single label, can be $username or $uid, its up to the implementator
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on removed
 */
function stoplist_hook_blacklist_removeip($label, $mobile) {
	$ret = FALSE;
	
	$c_uid = $label;
	$condition = array(
		'uid' => $c_uid,
		'mobile' => $mobile 
	);
	$removed = dba_remove(_DB_PREF_ . '_featureStoplist', $condition);
	if ($removed) {
		_log('remove IP from blacklist ip:' . $mobile . ' uid:' . $c_uid, 2, 'stoplist_hook_blacklist_removeip');
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Get mobile numbers from blacklist
 *
 * @return array mobile numbers
 */
function stoplist_hook_blacklist_getips() {
	$ret = dba_search(_DB_PREF_ . '_featureStoplist');
	
	return $ret;
}

/**
 * Check mobile is exists in blacklist
 *
 * @param string $label
 *        single label, can be $username or $uid, its up to the implementator
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE when found and FALSE if not found
 */
function stoplist_hook_blacklist_mobile_isexists($label, $mobile) {
	$ret = FALSE;
	
	$condition = array(
		'uid' => $label,
		'mobile' => $mobile 
	);
	$row = dba_search(_DB_PREF_ . '_featureStoplist', 'mobile', $condition);
	if (count($row) > 0) {
		$ret = TRUE;
	}
	
	return $ret;
}
