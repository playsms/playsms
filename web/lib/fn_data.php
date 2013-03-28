<?php
defined('_SECURE_') or die('Forbidden');

function data_search($db_table, $fields='', $keywords='', $extras='') {
	$ret = array();
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_fields .= "AND ".$key."=".$val." ";
		}
	}
	if (is_array($keywords)) {
		foreach ($keywords as $key => $val) {
			$q_keywords .= "OR ".$key." LIKE '".$val."' ";
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extras .= $key." ".$val." ";
		}
	}
	if ($q_fields || $q_keywords || $q_extras) {
		$q_where = 'WHERE';
	}
	
	// keywords first, and then fields
	$q_conditions = substr(trim($q_keywords." ".$q_fields." ".$q_extras), 3);
	
	$db_query = "SELECT * FROM ".$db_table." ".$q_where." ".$q_conditions;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function data_count($db_table, $fields='', $keywords='', $extras='') {
	$ret = 0;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_fields .= "AND ".$key."=".$val." ";
		}
	}
	if (is_array($keywords)) {
		foreach ($keywords as $key => $val) {
			$q_keywords .= "OR ".$key." LIKE '".$val."' ";
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extras .= $key." ".$val." ";
		}
	}
	if ($q_fields || $q_keywords || $q_extras) {
		$q_where = 'WHERE';
	}
	
	// keywords first, and then fields
	$q_conditions = substr(trim($q_keywords." ".$q_fields." ".$q_extras), 3);
	
	$db_query = "SELECT COUNT(*) AS count FROM ".$db_table." ".$q_where." ".$q_conditions;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	return $ret;
}

function data_add($db_table, $item) {
	$ret = false;
	if (is_array($item)) {
		foreach ($item as $key => $val) {
			$sets .= $key.",";
			$vals .= "'".$val."',";
		}
		if ($sets && $vals) {
			$sets = substr($sets, 0, -1);
			$vals = substr($vals, 0, -1);
			$db_query = "INSERT INTO ".$db_table." (".$sets.") VALUES (".$vals.")";
			if ($c_id = dba_insert_id($db_query)) {
				$ret = $c_id;
			}
		}
	}
	return $ret;
}

function data_update($db_table, $item, $condition='') {
	$ret = false;
	global $core_config;
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
			$db_query = "UPDATE ".$db_table." SET ".$sets." ".$q_condition;
			if ($c_rows = dba_affected_rows($db_query)) {
				$ret = $c_rows;
			}
		}
	}
	return $ret;
}

function data_remove($db_table, $condition='') {
	$ret = false;
	if (is_array($condition)) {
		foreach ($condition as $key => $val){ 
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
		$db_query = "DELETE FROM ".$db_table." ".$q_condition;
		if ($c_rows = dba_affected_rows($db_query)) {
			$ret = $c_rows;
		}
	}
	return $ret;
}

function data_isavail($db_table, $fields='') {
	$ret = false;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "OR ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 2);
		}
	}
	$db_query = "SELECT * FROM ".$db_table." ".$q_condition." LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = 0;
	} else {
		$ret = 1;
	}
	return $ret;
}

?>