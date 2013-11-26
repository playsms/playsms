<?php

function registry_update($uid, $registry_group, $registry_family, $items) {
	$ret = false;
	$db_table = _DB_PREF_.'_tblRegistry';
	if (is_array($items)) {
		foreach ($items as $key => $val) {
			$conditions = array('uid' => $uid, 'registry_group' => $registry_group, 'registry_family' => $registry_family, 'registry_key' => $key);
			$values = array('c_timestamp' => mktime(), 'registry_value' => $val);
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

?>