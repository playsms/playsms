<?php

/**
 * Report count of pending SMS
 * @param integer $uid User ID
 * @return integer Count of pending SMS
 */
function report_count_pending($uid=0) {
	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('p_status' => 0, 'flag_deleted' => 0);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	return dba_count($db_table, $conditions);
}

/**
 * Report count of sent SMS
 * @param integer $uid User ID
 * @return integer Count of sent SMS
 */
function report_count_sent($uid=0) {
	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('p_status' => 1, 'flag_deleted' => 0);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	return dba_count($db_table, $conditions);
}

/**
 * Report count of delivered SMS
 * @param integer $uid User ID
 * @return integer Count of delivered SMS
 */
function report_count_delivered($uid=0) {
	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('p_status' => 3, 'flag_deleted' => 0);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	return dba_count($db_table, $conditions);
}

/**
 * Report count of failed SMS
 * @param integer $uid User ID
 * @return integer Count of failed SMS
 */
function report_count_failed($uid=0) {
	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('p_status' => 2, 'flag_deleted' => 0);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	return dba_count($db_table, $conditions);
}

/**
 * Report count of deleted SMS
 * @param integer $uid User ID
 * @return integer Count of deleted SMS
 */
function report_count_deleted($uid=0) {
	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('flag_deleted' => 1);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	return dba_count($db_table, $conditions);
}

/**
 * Get whose online
 * @param integer $status User status
 * @return array Whose online data
 */
function report_whoseonline($status=0) {
	global $icon_config;

	$ret = array();

	$hashes = user_session_get();
	foreach ($hashes as $key => $val) {
		$c_user = user_getdatabyuid($val['uid']);
		$c_username = $c_user['username'];
		$c_status = $c_user['status'];

		if ($status && $c_status <> $status) {
			continue;
		}
		
		$c_is_admin = '';
		if ($c_status == '2') {
			$c_is_admin = $icon_config['admin'];
		}

		$c_idle = (int)(strtotime(core_get_datetime()) - strtotime($val['last_update']));
		if ($c_idle > 15*60) {
			$c_login_status = $icon_config['idle'];
		} else {
			$c_login_status = $icon_config['online'];
		}
	
		$ret[$c_username][] = array(
			'uid' => $c_user['uid'],
			'username' => $c_username,
			'status' => $c_status,
			'icon_is_admin' => $c_is_admin,
			'ip' => $val['ip'],
			'http_user_agent' => $val['http_user_agent'],
			'sid' => $val['sid'],
			'hash' => $key,
			'login_status' => $c_login_status,
			'last_update' => core_display_datetime($val['last_update']),
			'action_link' => _a('index.php?app=main&inc=tools_report&route=online&op=kick&hash='.$key, $icon_config['delete']),
		);
	}

	ksort($ret);

	return $ret;
}

/**
 * Get admin whose online
 * @return array Whose online data
 */
function report_whoseonline_admin() {
	return report_whoseonline(2);
}

/**
 * Get user whose online
 * @return array Whose online data
 */
function report_whoseonline_user() {
	return report_whoseonline(3);
}
