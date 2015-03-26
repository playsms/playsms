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

function queuelog_get($line_per_page = '', $limit = '') {
	global $user_config;
	$ret = array();
	if ($user_config['status'] != 2) {
		$user_query = "AND uid='" . $user_config['uid'] . "'";
	}
	if ($line_per_page) {
		$line_per_page_query = "LIMIT $line_per_page";
	}
	if ($limit) {
		$limit_query = "OFFSET $limit";
	}
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag='0' " . $user_query . " ORDER BY id " . $line_per_page_query . " " . $limit_query;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_id = $db_row['id'];
		$db_row['count'] = dba_count(_DB_PREF_ . '_tblSMSOutgoing_queue_dst', array(
			'flag' => 0,
			'queue_id' => $c_id 
		));
		$ret[] = $db_row;
	}
	return $ret;
}

function queuelog_countall() {
	global $user_config;
	$ret = 0;
	if ($user_config['status'] != 2) {
		$user_query = "AND uid='" . $user_config['uid'] . "'";
	}
	$db_query = "SELECT count(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag='0' " . $user_query;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	return $ret;
}

function queuelog_delete($queue) {
	global $user_config;
	$ret = FALSE;
	if ($user_config['status'] != 2) {
		$user_query = "AND uid='" . $user_config['uid'] . "'";
	}
	$db_query = "DELETE FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag='0' AND queue_code='" . $queue . "' " . $user_query;
	if ($db_result = dba_affected_rows($db_query)) {
		$ret = TRUE;
	}
	return $ret;
}

function queuelog_delete_all($queue) {
	global $user_config;
	$ret = FALSE;
	if ($user_config['status'] != 2) {
		$user_query = "AND uid='" . $user_config['uid'] . "'";
	}
	$db_query = "DELETE FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag='0' " . $user_query;
	if ($db_result = dba_affected_rows($db_query)) {
		$ret = TRUE;
	}
	return $ret;
}
