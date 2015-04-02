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
 * Add mobile to blacklist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on added
 */
function stoplist_hook_blacklist_mobile_add($uid, $mobile) {
	$ret = FALSE;
	
	$db_query = "
			INSERT INTO " . _DB_PREF_ . "_featureStoplist (uid, mobile)
			VALUES ('$uid', '$mobile')";
	if (!blacklist_mobile_isexists($label, $mobile)) {
		$new_id = @dba_insert_id($db_query);
		if ($new_id) {
			_log('add mobile to blacklist id:' . $new_id . ' mobile:' . $mobile . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_add');
			$ret = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Remove mobile from blacklist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE on removed
 */
function stoplist_hook_blacklist_mobile_remove($uid, $mobile) {
	$ret = FALSE;
	
	$condition = array(
		'uid' => $c_uid,
		'mobile' => $mobile 
	);
	$removed = dba_remove(_DB_PREF_ . '_featureStoplist', $condition);
	if ($removed) {
		_log('remove mobile from blacklist mobile:' . $mobile . ' uid:' . $c_uid, 2, 'stoplist_hook_blacklist_mobile_remove');
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Get all mobile numbers from blacklist
 *
 * @return array mobile numbers
 */
function stoplist_hook_blacklist_mobile_getall() {
	$ret = dba_search(_DB_PREF_ . '_featureStoplist', '*');
	
	return $ret;
}

/**
 * Get all mobile numbers from blacklist
 *
 * @param integer $uid
 *        User ID
 * @return array mobile numbers belongs to $uid
 */
function stoplist_hook_blacklist_mobile_get($uid) {
	$condition = array(
		'uid' => $uid 
	);
	$ret = dba_search(_DB_PREF_ . '_featureStoplist', '*', $condition);
	
	return $ret;
}

/**
 * Check mobile is exists in blacklist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile
 * @return boolean TRUE when found and FALSE if not found
 */
function stoplist_hook_blacklist_mobile_isexists($uid, $mobile) {
	$ret = FALSE;
	
	$condition = array(
		'uid' => $uid,
		'mobile' => $mobile 
	);
	$row = dba_search(_DB_PREF_ . '_featureStoplist', 'mobile', $condition);
	if (count($row) > 0) {
		$ret = TRUE;
	}
	
	return $ret;
}
