<?php
defined('_SECURE_') or die('Forbidden');

function country_getall($fields='', $extras='') {
	$ret = array();
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extra .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser_country ".$q_condition." ".$q_extra;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function country_count($fields='', $extras='') {
	$ret = 0;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extra .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT count(*) AS count FROM "._DB_PREF_."_tblUser_country ".$q_condition." ".$q_extra;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	return $ret;
}

function country_add($item) {
	$ret = false;
	if (is_array($item)) {
		foreach ($item as $key => $val) {
			$sets .= $key.",";
			$vals .= "'".$val."',";
		}
		if ($sets && $vals) {
			$sets = substr($sets, 0, -1);
			$vals = substr($vals, 0, -1);
			$db_query = "INSERT INTO "._DB_PREF_."_tblUser_country (".$sets.") VALUES (".$vals.")";
			if ($c_id = dba_insert_id($db_query)) {
				$ret = $c_id;
			}
		}
	}
	return $ret;
}

function country_update($item, $condition='') {
	$ret = false;
	if (is_array($item)) {
		foreach ($item as $key => $val) {
			$sets .= $key."='".$val."',";
		}
		if ($sets) {
			$sets = substr($sets, 0, -1);
			if (is_array($condition)) {
				foreach ($condition as $key => $val){ 
					$q_condition .= " AND ".$key."='".$val."'";
				}
				if ($q_condition) {
					$q_condition = " WHERE 1=1 ".$q_condition;
				}
			}
			$db_query = "UPDATE "._DB_PREF_."_tblUser_country SET ".$sets." ".$q_condition;
			if ($c_rows = dba_affected_rows($db_query)) {
				$ret = $c_rows;
			}
		}
	}
	return $ret;
}

function country_remove($condition='') {
	$ret = false;
	if (is_array($condition)) {
		foreach ($condition as $key => $val){ 
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
		$db_query = "DELETE FROM "._DB_PREF_."_tblUser_country ".$q_condition;
		if ($c_rows = dba_affected_rows($db_query)) {
			$ret = $c_rows;
		}
	}
	return $ret;
}

function country_isavail($fields='') {
	$ret = false;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "OR ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 2);
		}
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser_country ".$q_condition." LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = 0;
	} else {
		$ret = 1;
	}
	return $ret;
}

?>