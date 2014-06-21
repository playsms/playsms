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
 * Add country
 * @param string $name Country name
 * @param string $code Country code
 * @param string $prefix Country prefix
 * @return boolean
 */
function country_add($name, $code, $prefix = '') {
	$ret = FALSE;
	
	if (!$name) {
		return FALSE;
	}
	
	$code = substr(0, 2, core_sanitize_alpha(strtolower(trim($code))));
	if (!$code) {
		return FALSE;
	}
	
	$prefix = (trim($prefix) ? core_sanitize_numeric($prefix) : '');
	
	$db_table = _DB_PREF_ . '_tblCountry';
	if (dba_isavail($db_table, array(
		'country_name' => $name,
		'country_code' => $code
	))) {
		$items = array(
			'name' => $name,
			'code' => $code,
			'prefix' => $prefix,
		);
		if ($result = dba_add($db_table, $items)) {
			logger_print('id:' . $result . ' name:' . $name . ' code:' . $code . ' prefix:' . $prefix, 3, 'country_add');
			$ret = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Remove country
 * @param string $id Country ID
 * @return boolean
 */
function country_remove($id) {
	$ret = FALSE;
	
	if (!$id) {
		return FALSE;
	}
	
	$db_table = _DB_PREF_ . '_tblCountry';
	if ($result = dba_remove($db_table, array(
		'id' => $id,
	))) {
		logger_print('id:' . $id, 3, 'country_remove');
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Update country
 * @param string $id Country ID
 * @param array $data Updated data
 * @return boolean
 */
function country_update($id, $data = array()) {
	$ret = FALSE;
	$replaced = '';
	
	if (!($id && is_array($data))) {
		return FALSE;
	}
	
	$db_table = _DB_PREF_ . '_tblCountry';
	$result = dba_search($db_table, '*', array(
		'id' => $id,
	));
	foreach ($result[0] as $key => $val) {
		$items[$key] = ($data[$key] ? $data[$key] : $val);
		if ($data[$key]) {
			$replaced = $key . ':' . $val . ' ';
		}
	}
	if ($items && trim($replaced)) {
		if (dba_update($db_table, $items, array(
			'id' => $id
		))) {
			logger_print('id:' . $id . ' ' . trim($replaced) , 3, 'country_update');
			$ret = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Search country
 * @param array $conditions Search criteria
 * @return array
 */
function country_search($conditions = '', $keywords = '') {
	$db_table = _DB_PREF_ . '_tblCountry';
	$results = dba_search($db_table, '*', $conditions, $keywords, array(
		'ORDER BY' => 'country_name'
	));
	
	return $results;
}
