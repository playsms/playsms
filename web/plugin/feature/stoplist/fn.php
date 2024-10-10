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
 * Validate and sanitize mobile phone number
 * 
 * @param string $mobile Pointer to mobile phone number
 * @return bool true if valid
 */
function _stoplist_validate(&$mobile)
{
	$mobile = trim($mobile) ? core_sanitize_mobile($mobile) : '';

	return $mobile ? true : false;
}

/**
 * Add a mobile number to stoplist
 *
 * @param string $mobile Mobile phone number
 * @return bool true when added
 */
function stoplist_hook_blacklist_mobile_add($mobile)
{
	if (!_stoplist_validate($mobile)) {

		return false;
	}

	if (!blacklist_mobile_isexists($mobile)) {
		if ($new_id = dba_add(_DB_PREF_ . '_featureStoplist', ['mobile' => $mobile])) {
			_log('added mobile number to stoplist id:' . $new_id . ' mobile:' . $mobile, 2, 'stoplist_hook_blacklist_mobile_add');
		} else {

			return false;
		}
	} else {
		_log('mobile number is already in stoplist mobile:' . $mobile, 2, 'stoplist_hook_blacklist_mobile_remove');
	}

	return true;
}

/**
 * Remove a mobile number from stoplist
 *
 * @param string $mobile Mobile phone number
 * @return bool true when added
 */
function stoplist_hook_blacklist_mobile_remove($mobile)
{
	if (!_stoplist_validate($mobile)) {

		return false;
	}

	if (blacklist_mobile_isexists($mobile)) {
		if (dba_remove(_DB_PREF_ . '_featureStoplist', ['mobile' => $mobile])) {
			_log('removed mobile from stoplist mobile:' . $mobile, 2, 'stoplist_hook_blacklist_mobile_remove');
		} else {

			return false;
		}
	} else {
		_log('mobile number is not in stoplist mobile:' . $mobile, 2, 'stoplist_hook_blacklist_mobile_remove');
	}

	return true;
}

/**
 * Check if mobile number is exists in stoplist
 *
 * @param string $mobile Mobile phone number
 * @return bool true if exists
 */
function stoplist_hook_blacklist_mobile_isexists($mobile)
{
	if (!_stoplist_validate($mobile)) {

		return false;
	}

	$db_query = "SELECT mobile FROM " . _DB_PREF_ . "_featureStoplist WHERE mobile=?";
	if (dba_num_rows($db_query, [$mobile])) {

		return true;
	} else {

		return false;
	}
}

/**
 * Get all mobile numbers from stoplist
 *
 * @return array mobile phone numbers
 */
function stoplist_hook_blacklist_mobile_getall()
{
	return dba_search(_DB_PREF_ . '_featureStoplist', '*');
}
