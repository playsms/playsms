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
 * Update registry
 *
 * @param int $uid
 * @param string $registry_group
 * @param string $registry_family
 * @param array $items registry_key => value
 * @return array registry_key => true or false
 */
function registry_update($uid, $registry_group, $registry_family, $items)
{
	$ret = [];

	if (is_array($items)) {
		foreach ( $items as $key => $val ) {
			$conditions = [
				'uid' => $uid,
				'registry_group' => $registry_group,
				'registry_family' => $registry_family,
				'registry_key' => $key
			];
			$values = [
				'c_timestamp' => strtotime(core_get_datetime()),
				'registry_value' => $val
			];
			if (dba_count(_DB_PREF_ . '_tblRegistry', $conditions)) {
				$ret[$key] = dba_update(_DB_PREF_ . '_tblRegistry', $values, $conditions) ? true : false;
			} else {
				$ret[$key] = dba_add(_DB_PREF_ . '_tblRegistry', array_merge($conditions, $values)) ? true : false;
			}
		}
	}

	return $ret;
}

/**
 * Search in registry
 *
 * @param int $uid
 * @param string $registry_group
 * @param string $registry_family optional registry family
 * @param string $registry_key optional registry key
 * @return array search results
 */
function registry_search($uid, $registry_group, $registry_family = '', $registry_key = '')
{
	$ret = [];

	if ($registry_group && $registry_family && $registry_key) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group,
			'registry_family' => $registry_family,
			'registry_key' => $registry_key
		];
		$list = dba_search(_DB_PREF_ . '_tblRegistry', 'registry_value', $conditions);
		$ret[$registry_group][$registry_family][$registry_key] = $list[0]['registry_value'];
	} else if ($registry_group && $registry_family) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group,
			'registry_family' => $registry_family
		];
		$list = dba_search(_DB_PREF_ . '_tblRegistry', 'registry_key, registry_value', $conditions);
		foreach ( $list as $db_row ) {
			$ret[$registry_group][$registry_family][$db_row['registry_key']] = $db_row['registry_value'];
		}
	} else if ($registry_group) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group
		];
		$list = dba_search(_DB_PREF_ . '_tblRegistry', 'registry_family, registry_key, registry_value', $conditions);
		foreach ( $list as $db_row ) {
			$ret[$registry_group][$db_row['registry_family']][$db_row['registry_key']] = $db_row['registry_value'];
		}
	}

	return $ret;
}

/**
 * Remove from registry
 *
 * @param int $uid
 * @param string $registry_group
 * @param string $registry_family optional registry family
 * @param string $registry_key optional registry key
 * @return bool true if removed successfully
 */
function registry_remove($uid, $registry_group, $registry_family = '', $registry_key = '')
{
	$ret = false;

	if ($registry_group && $registry_family && $registry_key) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group,
			'registry_family' => $registry_family,
			'registry_key' => $registry_key
		];
		$ret = dba_remove(_DB_PREF_ . '_tblRegistry', $conditions) ? true : false;
	} else if ($registry_group && $registry_family) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group,
			'registry_family' => $registry_family
		];
		$ret = dba_remove(_DB_PREF_ . '_tblRegistry', $conditions) ? true : false;
	} else if ($registry_group) {
		$conditions = [
			'uid' => $uid,
			'registry_group' => $registry_group
		];
		$ret = dba_remove(_DB_PREF_ . '_tblRegistry', $conditions) ? true : false;
	}

	return $ret;
}

/**
 * Search directly in registry database
 *
 * @param array $search search specific pair of key value
 * @param array $keywords search key with patterned value
 * @param array $extras extra database SQL
 * @return array search results
 */
function registry_search_record($search, $keywords = '', $extras = '')
{
	$ret = [];

	if (is_array($search)) {
		foreach ( $search as $key => $val ) {
			if ($val) {
				$conditions[$key] = $val;
			}
		}
		$ret = dba_search(_DB_PREF_ . '_tblRegistry', '*', $conditions, $keywords, $extras);
	}

	return $ret;
}
