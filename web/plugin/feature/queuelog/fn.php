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

function queuelog_get($line_per_page = 0, $limit = 0)
{
	global $user_config;

	$ret = [];

	$user_query = $user_config['status'] != 2 ? "AND uid='" . $user_config['uid'] . "'" : "";
	$line_per_page_query = (int) $line_per_page > 0 ? "LIMIT " . (int) $line_per_page : "";
	$limit_query = (int) $limit ? "OFFSET " . (int) $limit : "";
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE (flag='0' OR flag='3') " . $user_query . " ORDER BY id " . $line_per_page_query . " " . $limit_query;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($c_id = $db_row['id']) {
			$db_row['count'] = dba_count(_DB_PREF_ . '_tblSMSOutgoing_queue_dst', [
				'flag' => 0,
				'queue_id' => $c_id
			]);
			$ret[] = $db_row;
		}
	}

	return $ret;
}

function queuelog_countall()
{
	global $user_config;

	$ret = 0;

	$user_query = $user_config['status'] != 2 ? "AND uid='" . $user_config['uid'] . "'" : "";
	$db_query = "SELECT count(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE (flag='0' OR flag='3') " . $user_query;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}

	return $ret;
}

function queuelog_delete($queue)
{
	global $user_config;

	$ret = false;

	$user_query = $user_config['status'] != 2 ? "AND uid='" . $user_config['uid'] . "'" : "";
	$db_query = "DELETE FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE (flag='0' OR flag='3') AND queue_code=? " . $user_query;
	if (dba_affected_rows($db_query, [$queue])) {
		$ret = true;
	}

	return $ret;
}

function queuelog_delete_all($queue)
{
	global $user_config;

	$ret = false;

	$user_query = $user_config['status'] != 2 ? "AND uid='" . $user_config['uid'] . "'" : "";
	$db_query = "DELETE FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE (flag='0' OR flag='3') " . $user_query;
	if (dba_affected_rows($db_query)) {
		$ret = true;
	}

	return $ret;
}
