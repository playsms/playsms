<?php
defined('_SECURE_') or die('Forbidden');

// DB.php is part of PHP PEAR-DB package
// previously removed in 0.9.2 but re-added in this release due to its complicated installation
define('LIBS', $apps_path['libs'].'/external/pear-db/');
include_once LIBS.'DB.php';

// --------------------------------------------------------------------------//

function dba_connect($username,$password,$dbname,$hostname,$port="",$persistant="true") {
	global $apps_path;
	$access = $username;
	if ($password) {
		$access = "$username:$password";
	}
	$host = $hostname;
	if ($port) {
		$host = "$hostname:$port";
	}
	$dsn = _DB_TYPE_."://$access@$host/$dbname";
	$dba_object = DB::connect("$dsn","$persistant");

	if (DB::isError($dba_object)) {
		// $error_msg = "DB Name: $dbname<br>DB Host: $host";
		ob_end_clean();
		die ("<p align=left>".$dba_object->getMessage()."<br>".$error_msg."<br>");
	}
	return $dba_object;
}

function dba_query_simple($mystring) {
	global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
	$result = $dba_object->query($mystring);
	if (DB::isError($dba_object)) {
		// ob_end_clean();
		// die ("<p align=left>".$dba_object->getMessage()."<br>".$dba_object->userinfo."<br>");
		return "";
	}
	return $result;
}

function dba_query($mystring, $from="0", $count="0") {
	global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;

	// log all db query
	if (function_exists('logger_print')) {
		logger_print("q:".$mystring, 4, "dba query");
	}

	$DBA_ROW_COUNTER = 0;

	if ($DBA_LIMIT_COUNT > 0) {
		$from = $DBA_LIMIT_FROM;
		$count = $DBA_LIMIT_COUNT;
	}
	$DBA_LIMIT_FROM = 0;
	$DBA_LIMIT_COUNT = 0;

	if (($from == 0) && ($count == 0)) {
		$result = dba_query_simple($mystring);
		return $result;
	}

	$is_special = false;
	switch ($dba_DB) {
		case "mssql":
			$limit = $from + $count;
			if ($limit == $count) {
				$str_limit = "SELECT TOP $limit";
				$mystring = str_replace ("SELECT",$str_limit,$mystring);
				$is_special = true;
			}
			break;
		case "mysql":
		case "mysqli":
			$str_limit = " LIMIT $from, $count";
			$mystring .= $str_limit;
			$is_special = true;
			break;
		default:
			break;
	}

	if ($is_special) {
		$result = $dba_object->query($mystring);
	} else {
		$result = $dba_object->limitQuery($mystring, $from, $count);
	}

	if (DB::isError($dba_object)) {
		// ob_end_clean();
		// die ("<p align=left>".$dba_object->getMessage()."<br>".$dba_object->userinfo."<br>");
		return "";
	}

	if (!$is_special) {
		$result->limit_from = $from;
		$result->limit_count = $count;
	}

	return $result;
}

function dba_fetch_array($myresult, $rownum=null) {
	global $DBA_ROW_COUNTER;
	if (!$myresult) {
		return "";
	}

	if (!$DBA_ROW_COUNTER) {
		$DBA_ROW_COUNTER = $myresult->limit_from;
	}
	if (DB::isError($myresult)) {
		// ob_end_clean();
		// die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
		return "";
	}
	$myresult->row_counter = $DBA_ROW_COUNTER++;
	$result = $myresult->fetchRow(DB_FETCHMODE_ASSOC, $rownum);
	return $result;
}

function dba_fetch_row($myresult, $rownum=null) {
	global $DBA_ROW_COUNTER;
	if (!$myresult) {
		return "";
	}

	if (!$DBA_ROW_COUNTER) {
		$DBA_ROW_COUNTER = $myresult->limit_from;
	}
	if (DB::isError($myresult)) {
		// ob_end_clean();
		// die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
		return "";
	}
	$myresult->row_counter = $DBA_ROW_COUNTER++;
	$result = $myresult->fetchRow(DB_FETCHMODE_ORDERED, $rownum);
	return $result;
}

function dba_num_rows($mystring) {
	global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
	$myresult = dba_query ($mystring);
	if (DB::isError($myresult)) {
		// ob_end_clean();
		// die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
		return 0;
	}
	if ($result = $myresult->numRows()) return $result;
	return 0;
}

function dba_affected_rows($mystring) {
	global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
	$myresult = dba_query ($mystring);
	if (DB::isError($myresult)) {
		// ob_end_clean();
		// die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
		return 0;
	}
	if ($result = $dba_object->affectedRows()) return $result;
	return 0;
}

function dba_insert_id($mystring) {
	global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
	if (dba_query ($mystring)) {
		switch (_DB_TYPE_) {
			case "mysql":
			case "mysqli":
				$myquery = "SELECT @@IDENTITY";
				$result_tmp = dba_query($myquery);
				list($result) = dba_fetch_row($result_tmp);
				break;
			case "sqlite3":
				$myquery = "SELECT last_insert_rowid()";
				$result_tmp = dba_query($myquery);
				$result = dba_fetch_row($result_tmp);
				$result = $result[0];
				break;
			case "pgsql":
				$myquery = "SELECT lastval()";
				$result_tmp = dba_query($myquery);
				list($result) = dba_fetch_row($result_tmp);
				break;
		}
	}
	return $result;
}

function dba_disconnect() {
	global $dba_object;
	if ($dba_object->disconnect()) {
		return 1;
	} else {
		return 0;
	}
}

function dba_search($db_table, $fields='*', $conditions='', $keywords='', $extras='', $join='') {
	$ret = array();
	if ($fields) {
		$q_fields = trim($fields);
	}
	if (is_array($conditions)) {
		foreach ($conditions as $key => $val) {
			$q_conditions .= "AND ".$key."='".$val."' ";
		}
	}
	if (is_array($keywords)) {
		$q_keywords = "AND (";
		foreach ($keywords as $key => $val) {
			$q_keywords .= "OR ".$key." LIKE '".$val."' ";
		}
		$q_keywords .= ")";
		$q_keywords = str_replace("(OR","(",$q_keywords);
	}
	$q_sql_where = trim($q_conditions." ".$q_keywords);
	if ($q_conditions || $q_keywords) {
		$q_where = 'WHERE';
		$q_sql_where = substr($q_sql_where, 3);
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extras .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT ".$q_fields." FROM ".$db_table." ".$join." ".$q_where." ".$q_sql_where." ".$q_extras;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function dba_count($db_table, $conditions='', $keywords='', $extras='', $join='') {
	$ret = 0;
	if (is_array($conditions)) {
		foreach ($conditions as $key => $val) {
			$q_conditions .= "AND ".$key."='".$val."' ";
		}
	}
	if (is_array($keywords)) {
		$q_keywords = "AND (";
		foreach ($keywords as $key => $val) {
			$q_keywords .= "OR ".$key." LIKE '".$val."' ";
		}
		$q_keywords .= ")";
		$q_keywords = str_replace("(OR","(",$q_keywords);
	}
	$q_sql_where = trim($q_conditions." ".$q_keywords);
	if ($q_conditions || $q_keywords) {
		$q_where = 'WHERE';
		$q_sql_where = substr($q_sql_where, 3);
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extras .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT COUNT(*) AS count FROM ".$db_table." ".$join." ".$q_where." ".$q_sql_where." ".$q_extras;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	return $ret;
}

function dba_add($db_table, $items) {
	$ret = false;
	if (is_array($items)) {
		foreach ($items as $key => $val) {
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

function dba_update($db_table, $items, $condition='', $operand='AND') {
	$ret = false;
	global $core_config;
	if (is_array($items)) {
		foreach ($items as $key => $val) {
			$sets .= $key."='".$val."',";
		}
		if ($sets) {
			$sets = substr($sets, 0, -1);
			if (is_array($condition)) {
				foreach ($condition as $key => $val){ 
					$q_condition .= " ".$operand." ".$key."='".$val."'";
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

function dba_remove($db_table, $condition='', $operand='AND') {
	$ret = false;
	if (is_array($condition)) {
		foreach ($condition as $key => $val){ 
			$q_condition .= $operand." ".$key."='".$val."' ";
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

function dba_isavail($db_table, $conditions='', $operand='OR') {
	$ret = false;
	if (is_array($conditions)) {
		foreach ($conditions as $key => $val) {
			$q_condition .= $operand." ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
	}
	$db_query = "SELECT * FROM ".$db_table." ".$q_condition." LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = false;
	} else {
		$ret = true;
	}
	return $ret;
}

function dba_isexists($db_table, $conditions='', $operand='OR') {
	$ret = ( dba_isavail($db_table, $conditions, $operand) ? false : true );
	return $ret;
}

function dba_valid($db_table, $field, $value) {
	global $core_config;
	$ret = false;
	if ($db_table && $field && $value) {
		$conditions[$field] = $value;
		if (! isadmin()) {
			$conditions['uid'] = $core_config['user']['uid'];
		}
		if ($list = dba_search($db_table, $field, $conditions)) {
			$ret = $list[0][$field];
		}
	}
	return $ret;
}

?>