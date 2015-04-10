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
 * Add a mobile number to stoplist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile number
 * @return boolean TRUE on added
 */
function stoplist_hook_blacklist_mobile_add($uid, $mobile) {
	$ret = FALSE;
	
	// if account exists
	$uid = (user_uid2username((int) $uid) ? (int) $uid : 1);
	
	$items = array(
		'uid' => $uid,
		'mobile' => $mobile 
	);
	
	if (!blacklist_mobile_isexists(0, $mobile)) {
		if ($new_id = dba_add(_DB_PREF_ . '_featureStoplist', $items)) {
			_log('added mobile number to stoplist id:' . $new_id . ' mobile:' . $mobile . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_add');
			
			$ret = TRUE;
		}
	} else {
		_log('mobile number is already in stoplist mobile:' . $mobile . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_remove');
		
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Remove a mobile number from stoplist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile number
 * @return boolean TRUE on added
 */
function stoplist_hook_blacklist_mobile_remove($uid, $mobile) {
	$ret = FALSE;
	
	$conditions = array(
		'mobile' => $mobile 
	);
	
	if ($uid = (int) $uid) {
		$conditions['uid'] = $uid;
	}
	
	if (blacklist_mobile_isexists(0, $mobile)) {
		$removed = dba_remove(_DB_PREF_ . '_featureStoplist', $conditions);
		if ($removed) {
			_log('removed mobile from stoplist mobile:' . $mobile . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_remove');
			
			$ret = TRUE;
		}
	} else {
		_log('mobile number is not in stoplist mobile:' . $mobile . ' uid:' . $uid, 2, 'stoplist_hook_blacklist_mobile_remove');
		
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Get all mobile numbers from stoplist
 *
 * @return array mobile numbers
 */
function stoplist_hook_blacklist_mobile_getall() {
	$ret = dba_search(_DB_PREF_ . '_featureStoplist', '*');
	
	return $ret;
}

/**
 * Get mobile numbers from stoplist belongs to certain user
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile numbers
 * @return boolean TRUE on removed
 */
function stoplist_hook_blacklist_mobile_get($uid) {
	$ret = array();
	
	if ($uid = (int) $uid) {
		$conditions = array(
			'uid' => $uid 
		);
		
		$ret = dba_search(_DB_PREF_ . '_featureStoplist', '*', $conditions);
	}
	
	return $ret;
}

/**
 * Check if mobile number is exists in stoplist
 *
 * @param integer $uid
 *        User ID
 * @param string $mobile
 *        single mobile number
 * @return boolean TRUE when found and FALSE if not found
 */
function stoplist_hook_blacklist_mobile_isexists($uid = 0, $mobile) {
	$ret = FALSE;
	
	$conditions = array(
		'mobile' => $mobile 
	);
	
	if ($uid = (int) $uid) {
		$conditions['uid'] = $uid;
	}
	
	$row = dba_search(_DB_PREF_ . '_featureStoplist', 'mobile', $conditions);
	if (count($row) > 0) {
		$ret = TRUE;
	}
	
	return $ret;
}
