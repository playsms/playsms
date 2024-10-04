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
 * Connect to database
 * @param string $db_user Database user
 * @param string $db_pass Database password
 * @param string $db_name Database name
 * @param string $db_host Database host default to 127.0.0.1
 * @param string $db_port Database port default to 3306
 * @param bool $persistant TRUE for persistant database connection
 * @return PDO PHP Data Objects
 */
function dba_connect($db_user, $db_pass, $db_name, $db_host = '127.0.0.1', $db_port = 3306, $persistant = true)
{
	global $DBA_PDO;

	$db_user = trim($db_user) ? $db_user : _DB_USER_;
	$db_pass = trim($db_pass) ? $db_pass : _DB_PASS_;
	$db_name = trim($db_name) ? $db_name : _DB_NAME_;
	$db_host = trim($db_host) ? $db_host : _DB_HOST_;
	$db_port = (int) $db_port ? $db_port : _DB_PORT_;

	if (defined(_DB_TYPE_) && _DB_TYPE_) {
		$db_type = strtolower(_DB_TYPE_) == 'mysqli' ? 'mysql' : strtolower(_DB_TYPE_);
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
		_log(_('Exception') . ': ' . $e->getMessage() . ' ip:' . _REMOTE_ADDR_, 2, 'dba_connect');
		die(_('FATAL ERROR') . ' : ' . _('Fail to connect to database'));
	}

	return $DBA_PDO;
}

/**
 * Prepare statement
 * @param string $db_query Prepared SQL statement
 * @return PDOStatement|bool PDO statement or FALSE
 */
function _dba_prepare($db_query)
{
	global $DBA_PDO;

	try {
		if ($pdo_statement = $DBA_PDO->prepare($db_query)) {
			return $pdo_statement;
		}
	} catch (PDOException $e) {
		_log(_('Exception') . ': ' . $e->getMessage() . ' q:' . $db_query . ' ip:' . _REMOTE_ADDR_, 2, '_dba_prepare');
		die(_('FATAL ERROR') . ' : ' . _('Database prepare statement operation has failed'));
	}

	return false;
}

/**
 * Execute prepared statement
 * @param PDOStatement $pdo_statement PDO statement
 * @param array $db_argv SQL arguments
 * @return bool TRUE for sucessful execution and FALSE for failure
 */
function _dba_execute($pdo_statement, $db_argv = [])
{
	$ret = false;

	try {
		if (is_array($db_argv) && $db_argv) {
			$ret = $pdo_statement->execute(core_stripslashes($db_argv));
		} else {
			$ret = $pdo_statement->execute();
		}

	} catch (PDOException $e) {
		//_log(_('Exception') . ': ' . $e->getMessage() . ' q:' . $pdo_statement->queryString . ' argv:' . print_r($db_argv, true) . ' ip:' . _REMOTE_ADDR_, 2, '_dba_execute');
		_log(_('Exception') . ': ' . $e->getMessage() . ' ip:' . _REMOTE_ADDR_, 2, '_dba_execute');
		die(_('FATAL ERROR') . ' : ' . _('Database query execution has failed'));
	}

	return $ret;
}

/**
 * Query database
 * @param string $db_query Prepared SQL statement
 * @param array $db_argv SQL arguments
 * @return PDOStatement|bool PDO statement or FALSE
 */
function dba_query($db_query, $db_argv = [])
{
	if ($pdo_statement = _dba_prepare($db_query)) {
		if (_dba_execute($pdo_statement, $db_argv)) {
			return $pdo_statement;
		}
	}

	return false;
}

/**
 * Fetch results as array indexed by column name
 * @param PDOStatement $db_result PDO statement from dba_query()
 * @return array Array result
 */
function dba_fetch_array($db_result)
{
	if ($db_result) {
		return $db_result->fetch(PDO::FETCH_ASSOC);
	} else {
		return [];
	}
}

/**
 * Fetch results as array indexed by column number
 * @param PDOStatement $db_result PDO statement from dba_query()
 * @return array Array result
 */
function dba_fetch_row($db_result)
{
	if ($db_result) {
		return $db_result->fetch(PDO::FETCH_NUM);
	} else {
		return [];
	}
}

/**
 * Query database and count the number of rows
 * @param string $db_query Prepared SQL statement
 * @param array $db_argv SQL arguments
 * @return int Number of rows
 */
function dba_num_rows($db_query, $db_argv = [])
{
	if ($db_result = dba_query($db_query, $db_argv)) {
		if ($db_result = dba_query('SELECT FOUND_ROWS()')) {
			return (int) $db_result->fetchColumn();
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}

/**
 * Delete or update database and count the number of updated rows
 * @param string $db_query Prepared SQL statement
 * @param array $db_argv SQL arguments
 * @return int Number of updated rows
 */
function dba_affected_rows($db_query, $db_argv = [])
{
	if ($pdo_statement = _dba_prepare($db_query)) {
		if (_dba_execute($pdo_statement, $db_argv)) {
			return (int) $pdo_statement->rowCount();
		}
	}

	return 0;
}

/**
 * Insert into database and count the number of inserted rows
 * @param string $db_query Prepared SQL statement
 * @param array $db_argv SQL arguments
 * @return int Insert ID
 */
function dba_insert_id($db_query, $db_argv = [])
{
	global $DBA_PDO;

	if ($pdo_statement = _dba_prepare($db_query)) {
		if (_dba_execute($pdo_statement, $db_argv)) {
			return (int) $DBA_PDO->lastInsertId();
		}
	}

	return 0;
}

/**
 * Build SQL statement
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array|string $fields Array or string of fields
 * @param array $conditions List of WHERE conditions
 * @param array $keywords List of WHERE conditions for LIKE
 * @param array $extras List of extra SQL
 * @param array|string $joins Array or string of JOIN SQL
 * @return array List of a query and its argument array
 */
function dba_build($db_table, $fields = [], $conditions = [], $keywords = [], $extras = [], $joins = [])
{
	$ret = ['', []];
	$db_argv = [];

	// db table
	// $db_table can be prefixed with _DB_PREF_ or without
	// Example: _DB_PREF_ . '_tblUser' or just 'tblUser'
	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return $ret;
	}

	// fields
	$q_fields = '';
	if (is_array($fields)) {
		foreach ( $fields as $field ) {
			$q_fields .= $field . ',';
		}
		$q_fields = substr($q_fields, 0, -1);
	} else {
		$q_fields = trim($fields);
	}
	$q_fields = empty($q_fields) ? '*' : $q_fields;

	// WHERE conditions
	$q_conditions = '';
	if (is_array($conditions)) {
		foreach ( $conditions as $key => $val ) {
			$q_conditions .= "AND " . $key . "=? ";
			$db_argv[] = $val;
		}
	}

	// WHERE conditions wiht LIKE
	$q_keywords = '';
	if (is_array($keywords)) {
		foreach ( $keywords as $key => $val ) {
			$q_keywords .= "OR " . $key . " LIKE ? ";
			$db_argv[] = $val;
		}
		if ($q_keywords = trim(substr($q_keywords, 3))) {
			$q_keywords = "AND (" . $q_keywords . ")";
		}
	}

	// combine to get final WHERE conditions
	$q_sql_where = '';
	if ($q_conditions || $q_keywords) {
		$q_sql_where = trim($q_conditions . " " . $q_keywords);
		if ($q_sql_where = substr($q_sql_where, 3)) {
			$q_sql_where = "WHERE " . $q_sql_where;
		}
	}

	// extra SQL such as GROUP BY, ORDER BY, LIMIT and OFFSET
	$q_extras = '';
	if (is_array($extras)) {
		foreach ( $extras as $key => $val ) {
			$q_extras .= $key . " " . $val . " ";
		}
	}

	// JOIN statements
	$q_joins = '';
	if (is_array($joins)) {
		foreach ( $joins as $join ) {
			$q_joins .= $join . ' ';
		}
		$q_joins = trim($q_joins);
	} else {
		$q_joins = trim($joins);
	}

	$db_query = trim("SELECT " . $q_fields . " FROM " . $db_table . " " . $q_joins . " " . $q_sql_where . " " . $q_extras);
	//_log("DEBUG q: " . $db_query, 2, "dba_build");

	$ret = [$db_query, $db_argv];

	return $ret;
}


/**
 * Search database and returns array results
 * Not recommended for large result unless you know what you are doing
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array|string $fields Array or string of fields
 * @param array $conditions List of WHERE conditions
 * @param array $keywords List of WHERE conditions for LIKE
 * @param array $extras List of extra SQL
 * @param array|string $joins Array or string of JOIN SQL
 * @return array
 */
function dba_search($db_table, $fields = [], $conditions = [], $keywords = [], $extras = [], $joins = [])
{
	$ret = [];

	list($db_query, $db_argv) = dba_build($db_table, $fields, $conditions, $keywords, $extras, $joins);
	$db_result = dba_query($db_query, $db_argv);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}

	return $ret;
}

/**
 * Execute COUNT query and returns the number of rows
 * Not recommended for large result unless you know what you are doing
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $conditions List of conditions
 * @param array $keywords List of keywords
 * @param array $extras List of extra SQL
 * @param array|string $joins Array or string of JOIN SQL
 * @return int
 */
function dba_count($db_table, $conditions = [], $keywords = [], $extras = [], $joins = [])
{
	$ret = 0;

	list($db_query, $db_argv) = dba_build($db_table, 'COUNT(*) AS count', $conditions, $keywords, $extras, $joins);
	$db_result = dba_query($db_query, $db_argv);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = (int) $db_row['count'];
	}

	return $ret;
}

/**
 * Insert into database
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $items List of items to be inserted
 * @return int Insert ID
 */
function dba_add($db_table, $items)
{
	$db_argv = [];

	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return 0;
	}

	if (is_array($items)) {
		$sets = '';
		$vals = '';
		foreach ( $items as $key => $val ) {
			$sets .= $key . ",";
			$vals .= "?,";
			$db_argv[] = $val;
		}
		if ($sets && $vals) {
			$sets = substr($sets, 0, -1);
			$vals = substr($vals, 0, -1);
			$db_query = "INSERT INTO " . $db_table . " (" . $sets . ") VALUES (" . $vals . ")";
			return dba_insert_id($db_query, $db_argv);
		}
	}

	return 0;
}

/**
 * Update database
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $items List of items to be updated
 * @param array $conditions List of conditions
 * @param string $operand Operand AND/OR to be used for multiple conditions
 * @return int Number of updated rows
 */
function dba_update($db_table, $items, $conditions = [], $operand = 'AND')
{
	$db_argv = [];

	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return 0;
	}

	$operand = strtoupper(trim($operand));
	$operand = $operand === 'AND' || $operand === 'OR' ? $operand : 'AND';

	if (is_array($items)) {
		$sets = '';
		foreach ( $items as $key => $val ) {
			$sets .= $key . "=?,";
			$db_argv[] = $val;
		}
		if ($sets) {
			$sets = substr($sets, 0, -1);
			if (is_array($conditions)) {
				$q_conditions = '';
				foreach ( $conditions as $key => $val ) {
					$q_conditions .= $operand . " " . $key . "=? ";
					$db_argv[] = $val;
				}
				if ($q_conditions = substr(trim($q_conditions), 3)) {
					$q_conditions = "WHERE " . $q_conditions;
				}
			}
			$db_query = "UPDATE " . $db_table . " SET " . $sets . " " . $q_conditions;
			return dba_affected_rows($db_query, $db_argv);
		}
	}

	return 0;
}

/**
 * Remove from database
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $conditions List of conditions
 * @param string $operand Operand AND/OR to be used for multiple conditions
 * @return int Number of deleted rows
 */
function dba_remove($db_table, $conditions = [], $operand = 'AND')
{
	$db_argv = [];

	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return 0;
	}

	$operand = strtoupper(trim($operand));
	$operand = $operand === 'AND' || $operand === 'OR' ? $operand : 'AND';

	if (is_array($conditions)) {
		$q_conditions = '';
		foreach ( $conditions as $key => $val ) {
			$q_conditions .= $operand . " " . $key . "=? ";
			$db_argv[] = $val;
		}
		if ($q_conditions = substr(trim($q_conditions), 3)) {
			$q_conditions = "WHERE " . $q_conditions;
		}
		$db_query = "DELETE FROM " . $db_table . " " . $q_conditions;
		return dba_affected_rows($db_query, $db_argv);
	}

	return 0;
}

/**
 * Check if specific data value does not exists so its available to be added later when needed
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $conditions List of conditions
 * @param string $operand Operand AND/OR to be used for multiple conditions
 * @return bool TRUE if data value does not exists thus available to be added later
 */
function dba_isavail($db_table, $conditions = [], $operand = 'OR')
{
	$db_argv = [];

	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return 0;
	}

	$operand = strtoupper(trim($operand));
	$operand = $operand === 'AND' || $operand === 'OR' ? $operand : 'OR'; // default is OR

	if (is_array($conditions)) {
		$q_conditions = '';
		foreach ( $conditions as $key => $val ) {
			$q_conditions .= $operand . " " . $key . "=? ";
			$db_argv[] = $val;
		}
		$q_conditions = trim($q_conditions);
		if ($q_conditions = substr($q_conditions, 3)) {
			$q_conditions = "WHERE " . $q_conditions;
		}
		$db_query = "SELECT * FROM " . $db_table . " " . $q_conditions . " LIMIT 1";
		$db_result = dba_query($db_query, $db_argv);
		if (dba_fetch_array($db_result)) {
			return false; // data exists, so its not available
		} else {
			return true;
		}
	}

	return false;
}

/**
 * Check if specific data value is exists
 * This function is the opposite of dba_isavail()
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param array $conditions List of conditions
 * @param string $operand Operand AND/OR to be used for multiple conditions
 * @return bool TRUE if data value is exists
 */
function dba_isexists($db_table, $conditions = '', $operand = 'OR')
{
	return (dba_isavail($db_table, $conditions, $operand) ? false : true);
}

/**
 * Check if field value pair is valid by checking their existance in database
 * This function currently used by plugins in playSMS
 * @param string $db_table DB table name prefixed with _DB_PREF_ or without
 * @param string $field DB table's field name
 * @param string $value DB table's field value
 * @return bool TRUE if data is valid
 */
function dba_valid($db_table, $field, $value)
{
	global $user_config;

	$db_table = trim($db_table);
	if (!(_DB_PREF_ && (string) substr($db_table, 0, strlen(_DB_PREF_)) === _DB_PREF_)) {
		$db_table = _DB_PREF_ . '_' . $db_table;
	}
	if (!$db_table) {
		return false;
	}

	$field = trim($field);
	$value = trim($value);

	if ($db_table && $field && $value) {
		$conditions[$field] = $value;
		if (!auth_isadmin()) {
			$conditions['uid'] = $user_config['uid'];
		}
		if ($list = dba_search($db_table, $field, $conditions, [], ['LIMIT' => 1])) {
			if (isset($list[0][$field]) && $list[0][$field]) {
				return true;
			}
		}
	}

	return false;
}