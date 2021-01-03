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

function registry_update($uid, $registry_group, $registry_family, $items) {
	$ret = false;
	$db_table = _DB_PREF_.'_tblRegistry';
	if (is_array($items)) {
		foreach ($items as $key => $val) {
			$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family, 'registry_key' => $key);
			$values = array('c_timestamp' => strtotime(core_get_datetime()), 'registry_value' => $val);
			if (dba_count($db_table, $conditions)) {
				$ret[$key] = dba_update($db_table, $values, $conditions);
			} else {
				$ret[$key] = dba_add($db_table, array_merge($conditions, $values));
			}
			unset($conditions);
			unset($values);
		}
	}
	return $ret;
}

function registry_search($uid, $registry_group, $registry_family = '', $registry_key = '') {
	$ret = array();
	$db_table = _DB_PREF_.'_tblRegistry';
	if ($registry_group && $registry_family && $registry_key) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family, 'registry_key' => $registry_key);
		$list = dba_search($db_table, 'registry_value', $conditions);
		$ret[$registry_group][$registry_family][$registry_key] = $list[0]['registry_value'];
	} else if ($registry_group && $registry_family) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family);
		$list = dba_search($db_table, 'registry_key, registry_value', $conditions);
		foreach ($list as $db_row) {
			$ret[$registry_group][$registry_family][$db_row['registry_key']] = $db_row['registry_value'];
		}
	} else if ($registry_group) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group);
		$list = dba_search($db_table, 'registry_family, registry_key, registry_value', $conditions);
		foreach ($list as $db_row) {
			$ret[$registry_group][$db_row['registry_family']][$db_row['registry_key']] = $db_row['registry_value'];
		}
	}
	return $ret;
}

function registry_remove($uid, $registry_group, $registry_family = '', $registry_key = '') {
	$ret = FALSE;
	$db_table = _DB_PREF_.'_tblRegistry';
	if ($registry_group && $registry_family && $registry_key) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family, 'registry_key' => $registry_key);
		$ret = dba_remove($db_table, $conditions);
	} else if ($registry_group && $registry_family) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family);
		$ret = dba_remove($db_table, $conditions);
	} else if ($registry_group) {
		$conditions = array('uid' => $uid, 'registry_group' => $registry_group);
		$ret = dba_remove($db_table, $conditions);
	}
	return $ret;
}

function registry_search_record($search, $keywords='', $extras='') {
	$db_table = _DB_PREF_.'_tblRegistry';

	foreach ($search as $key => $val) {
		if ($val) {
			$conditions[$key] = $val;
		}
	}

	return dba_search($db_table, '*', $conditions, $keywords, $extras);
}
