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

function dba_connect($db_user, $db_pass, $db_name, $db_host = '127.0.0.1', $db_port = 3306, $persistant = true)
{
	global $DBA_PDO;

	$db_user = trim($db_user) ? $db_user : _DB_USER_;
	$db_pass = trim($db_pass) ? $db_pass : _DB_PASS_;
	$db_name = trim($db_name) ? $db_name : _DB_NAME_;
	$db_host = trim($db_host) ? $db_host : _DB_HOST_;
	$db_port = (int) $db_port ? $db_port : _DB_PORT_;

	if (defined(_DB_TYPE_) && _DB_TYPE_) {
		$db_type = _DB_TYPE_ == 'mysqli' ? 'mysql' : _DB_TYPE_;
	} else {
		$db_type = 'mysql';
	}

	if ((int) $db_port > 0) {
		$db_dsn = $db_type . ':dbname=' . (string) $db_name . ';host=' . (string) $db_host . ';port=' . (int) $db_port;
	} else {
		$db_dsn = $db_type . ':dbname=' . (string) $db_name . ';host=' . (string) $db_host;
	}
	if (defined(_DB_DSN_)) {
		$db_dsn = _DB_DSN_ ? _DB_DSN_ : $db_dsn;
	}

	$db_opt = [];
	if (defined(_DB_OPT_)) {
		$db_opt = is_array(_DB_OPT_) && _DB_OPT_ ? _DB_OPT_ : [];
	}

	$persistant = (bool) $persistant ? (bool) $persistant : false;

	try {
		$db_opt['PDO::ATTR_PERSISTENT'] = $persistant;
		$DBA_PDO = new PDO(
			(string) $db_dsn,
			(string) $db_user,
			(string) $db_pass,
			$db_opt
		);
	} catch (PDOException $e) {
		//ob_end_clean();
		//echo 'DB Error: ' . $e->getMessage() . PHP_EOL;
		//exit();
		_log(_('Exception') . ': ' . $e->getMessage() . ' ip:' . _REMOTE_ADDR_, 2, 'dba_connect');
		die(_('FATAL ERROR') . ' : ' . _('Fail to connect to database'));
	}

	return $DBA_PDO;
}

function dba_prepare($db_query)
{
	global $DBA_PDO;

	if ($q = $DBA_PDO->prepare($db_query)) {
		return $q;
	}

	return false;
}

function dba_execute($pdo_statement, $db_argv = [])
{
	if (is_array($db_argv) && $db_argv) {
		$db_result = $pdo_statement->execute($db_argv);
	} else {
		$db_result = $pdo_statement->execute();
	}

	return $db_result;
}

function dba_query($db_query, $db_argv = [])
{
	if ($q = dba_prepare($db_query)) {
		if (dba_execute($q, $db_argv)) {
			return $q;
		}
	}

	return false;
}

function dba_fetch_array($db_result)
{
	if ($db_result) {
		return $db_result->fetch(PDO::FETCH_ASSOC);
	} else {
		return false;
	}
}

function dba_fetch_row($db_result)
{
	if ($db_result) {
		return $db_result->fetch(PDO::FETCH_NUM);
	} else {
		return false;
	}
}

function dba_num_rows($db_query)
{
	if ($db_result = dba_query($db_query)) {
		if ($db_result = dba_query('SELECT FOUND_ROWS()')) {
			return $db_result->fetchColumn();
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}

function dba_affected_rows($db_query)
{
	global $DBA_PDO;

	$q = $DBA_PDO->prepare($db_query);
	if ($q->execute()) {
		return $q->rowCount();
	} else {
		return 0;
	}
}

function dba_insert_id($db_query)
{
	global $DBA_PDO;

	$q = $DBA_PDO->prepare($db_query);
	if ($q->execute()) {
		return $DBA_PDO->lastInsertId();
	} else {
		return 0;
	}
}

function dba_search($db_table, $fields = '*', $conditions = '', $keywords = '', $extras = '', $join = '')
{
	$ret = array();
	if ($fields) {
		$q_fields = trim($fields);
	}
	if (is_array($conditions)) {
		foreach ( $conditions as $key => $val ) {
			$q_conditions .= "AND " . $key . "='" . $val . "' ";
		}
	}
	if (is_array($keywords)) {
		$q_keywords = "AND (";
		foreach ( $keywords as $key => $val ) {
			$q_keywords .= "OR " . $key . " LIKE '" . $val . "' ";
		}
		$q_keywords .= ")";
		$q_keywords = str_replace("(OR", "(", $q_keywords);
	}
	$q_sql_where = trim($q_conditions . " " . $q_keywords);
	if ($q_conditions || $q_keywords) {
		$q_where = 'WHERE';
		$q_sql_where = substr($q_sql_where, 3);
	}
	if (is_array($extras)) {
		foreach ( $extras as $key => $val ) {
			$q_extras .= $key . " " . $val . " ";
		}
	}
	$db_query = "SELECT " . $q_fields . " FROM " . $db_table . " " . $join . " " . $q_where . " " . $q_sql_where . " " . $q_extras;
	// _log("q: ".$db_query, 3, "dba_search");
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function dba_count($db_table, $conditions = '', $keywords = '', $extras = '', $join = '')
{
	$ret = 0;
	if (is_array($conditions)) {
		foreach ( $conditions as $key => $val ) {
			$q_conditions .= "AND " . $key . "='" . $val . "' ";
		}
	}
	if (is_array($keywords)) {
		$q_keywords = "AND (";
		foreach ( $keywords as $key => $val ) {
			$q_keywords .= "OR " . $key . " LIKE '" . $val . "' ";
		}
		$q_keywords .= ")";
		$q_keywords = str_replace("(OR", "(", $q_keywords);
	}
	$q_sql_where = trim($q_conditions . " " . $q_keywords);
	if ($q_conditions || $q_keywords) {
		$q_where = 'WHERE';
		$q_sql_where = substr($q_sql_where, 3);
	}
	if (is_array($extras)) {
		foreach ( $extras as $key => $val ) {
			$q_extras .= $key . " " . $val . " ";
		}
	}
	$db_query = "SELECT COUNT(*) AS count FROM " . $db_table . " " . $join . " " . $q_where . " " . $q_sql_where . " " . $q_extras;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	// fixme anton - just to make sure, if its empty then should be 0
	$ret = (trim($ret) ? trim($ret) : 0);
	return $ret;
}

function dba_add($db_table, $items)
{
	$ret = false;
	if (is_array($items)) {
		foreach ( $items as $key => $val ) {
			$sets .= $key . ",";
			$vals .= "'" . $val . "',";
		}
		if ($sets && $vals) {
			$sets = substr($sets, 0, -1);
			$vals = substr($vals, 0, -1);
			$db_query = "INSERT INTO " . $db_table . " (" . $sets . ") VALUES (" . $vals . ")";
			if ($c_id = dba_insert_id($db_query)) {
				$ret = $c_id;
			}
		}
	}
	return $ret;
}

function dba_update($db_table, $items, $condition = '', $operand = 'AND')
{
	$ret = false;
	global $core_config;
	if (is_array($items)) {
		foreach ( $items as $key => $val ) {
			$sets .= $key . "='" . $val . "',";
		}
		if ($sets) {
			$sets = substr($sets, 0, -1);
			if (is_array($condition)) {
				foreach ( $condition as $key => $val ) {
					$q_condition .= " " . $operand . " " . $key . "='" . $val . "'";
				}
				if ($q_condition) {
					$q_condition = " WHERE 1=1 " . $q_condition;
				}
			}
			$db_query = "UPDATE " . $db_table . " SET " . $sets . " " . $q_condition;
			if ($c_rows = dba_affected_rows($db_query)) {
				$ret = $c_rows;
			}
		}
	}
	return $ret;
}

function dba_remove($db_table, $condition = '', $operand = 'AND')
{
	$ret = false;
	if (is_array($condition)) {
		foreach ( $condition as $key => $val ) {
			$q_condition .= $operand . " " . $key . "='" . $val . "' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE " . substr($q_condition, 3);
		}
		$db_query = "DELETE FROM " . $db_table . " " . $q_condition;
		if ($c_rows = dba_affected_rows($db_query)) {
			$ret = $c_rows;
		}
	}
	return $ret;
}

function dba_isavail($db_table, $conditions = '', $operand = 'OR')
{
	$ret = false;
	if (is_array($conditions)) {
		foreach ( $conditions as $key => $val ) {
			$q_condition .= $operand . " " . $key . "='" . $val . "' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE " . substr($q_condition, 3);
		}
	}
	$db_query = "SELECT * FROM " . $db_table . " " . $q_condition . " LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = false;
	} else {
		$ret = true;
	}
	return $ret;
}

function dba_isexists($db_table, $conditions = '', $operand = 'OR')
{
	$ret = (dba_isavail($db_table, $conditions, $operand) ? false : true);
	return $ret;
}

function dba_valid($db_table, $field, $value)
{
	global $user_config;
	$ret = false;
	if ($db_table && $field && $value) {
		$conditions[$field] = $value;
		if (!auth_isadmin()) {
			$conditions['uid'] = $user_config['uid'];
		}
		if ($list = dba_search($db_table, $field, $conditions)) {
			$ret = $list[0][$field];
		}
	}
	return $ret;
}