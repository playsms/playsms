<?php

function registry_update($group, $family, $items) {
	$ret = false;
	$db_table = _DB_PREF_.'_tblRegistry';
	if (is_array($items)) {
		foreach ($items as $key => $val) {
			$conditions = array('group' => $group, 'family' => $family, 'key' => $key);
			$values = array('c_timestamp' => mktime(), 'value' => $val);
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

function registry_search($group, $family='', $key='') {
	$ret = array();
	$db_table = _DB_PREF_.'_tblRegistry';
	if ($group && $family && $key) {
		$conditions = array('group' => $group, 'family' => $family, 'key' => $key);
		$list = dba_search($db_table, '`value`', $conditions);
		$ret[$group][$family][$key] = $list[0]['value'];
	} else if ($group && $family) {
		$conditions = array('group' => $group, 'family' => $family);
		$list = dba_search($db_table, '`key`, `value`', $conditions);
		foreach ($list as $db_row) {
			$ret[$group][$family][$db_row['key']] = $db_row['value'];
		}
	} else if ($group) {
		$conditions = array('group' => $group);
		$list = dba_search($db_table, '`family`, `key`, `value`', $conditions);
		foreach ($list as $db_row) {
			$ret[$group][$db_row['family']][$db_row['key']] = $db_row['value'];
		}
	}
	return $ret;
}

?>