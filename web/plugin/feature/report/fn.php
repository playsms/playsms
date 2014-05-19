<?php

/**
 * Count number of SMS for report
 * @param  integer $uid          User ID or 0 for all users
 * @param  integer $dlr_status   Delivery report status
 * @param  integer $flag_deleted Deleted SMS flagged with 1
 * @return integer                Number of SMS
 */
function report_count($uid=0, $dlr_status=0, $flag_deleted=0) {
	$sms_count = 0;

	$db_table = _DB_PREF_.'_tblSMSOutgoing';
	$conditions = array('p_status' => $dlr_status, 'flag_deleted' => $flag_deleted);
	if ($uid) {
		$conditions['uid'] = $uid;
	}
	$list = dba_search($db_table, 'queue_code', $conditions, '', array('GROUP BY' => 'queue_code'));
	foreach ($list as $row) {
		$db_table = _DB_PREF_.'_tblSMSOutgoing_queue';
		$data = dba_search($db_table, 'sms_count', array('queue_code' => $row['queue_code']));
		$sms_count += $data[0]['sms_count'];
	}

	return (int)$sms_count;
}

/**
 * Report count of pending SMS
 * @param integer $uid User ID
 * @return integer Count of pending SMS
 */
function report_count_pending($uid=0) {
	return report_count($uid, 0);
}

/**
 * Report count of sent SMS
 * @param integer $uid User ID
 * @return integer Count of sent SMS
 */
function report_count_sent($uid=0) {
	return report_count($uid, 1);
}

/**
 * Report count of failed SMS
 * @param integer $uid User ID
 * @return integer Count of failed SMS
 */
function report_count_failed($uid=0) {
	return report_count($uid, 2);
}

/**
 * Report count of delivered SMS
 * @param integer $uid User ID
 * @return integer Count of delivered SMS
 */
function report_count_delivered($uid=0) {
	return report_count($uid, 3);
}

/**
 * Report count of deleted SMS
 * @param integer $uid User ID
 * @return integer Count of deleted SMS
 */
function report_count_deleted($uid=0) {
	$deleted_pending = report_count($uid, 0, 1);
	$deleted_sent = report_count($uid, 1, 1);
	$deleted_failed = report_count($uid, 2, 1);
	$deleted_delivered = report_count($uid, 3, 1);
	$deleted = $deleted_pending + $deleted_sent + $deleted_failed + $deleted_delivered;

	return $deleted;
}

/**
 * Get whose online
 * @param integer $status User status
 * @param boolean $online_only Report whose online only
 * @param boolean $idle_only Report whose online with login status idle only
 * @return array Whose online data
 */
function report_whoseonline($status=0, $online_only=FALSE, $idle_only=FALSE) {
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

		$is_idle = FALSE;
		$is_online = FALSE;
		$c_idle = (int)(strtotime(core_get_datetime()) - strtotime($val['last_update']));

		// last update more than 15 minutes will be considered as idle
		if ($c_idle > 15*60) {
			$is_idle = TRUE;
			$c_login_status = $icon_config['idle'];
		} else {
			$is_online = TRUE;
			$c_login_status = $icon_config['online'];
		}

		if ($online_only && ! $is_online) {
			continue;
		}

		if ($idle_only && ! $is_idle) {
			continue;
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
			'action_link' => _a('index.php?app=main&inc=feature_report&route=online&op=kick&hash='.$key, $icon_config['delete']),
		);
	}

	ksort($ret);

	return $ret;
}

/**
 * Get admin whose online
 * @param boolean $online_only Report whose online only
 * @param boolean $idle_only Report whose online with login status idle only
 * @return array Whose online data
 */
function report_whoseonline_admin($online_only=FALSE, $idle_only=FALSE) {
	return report_whoseonline(2, $online_only, $idle_only);
}

/**
 * Get user whose online
 * @param boolean $online_only Report whose online only
 * @param boolean $idle_only Report whose online with login status idle only
 * @return array Whose online data
 */
function report_whoseonline_user($online_only=FALSE, $idle_only=FALSE) {
	return report_whoseonline(3, $online_only, $idle_only);
}

/**
 * Get subuser whose online
 * @param boolean $online_only Report whose online only
 * @param boolean $idle_only Report whose online with login status idle only
 * @return array Whose online data
 */
function report_whoseonline_subuser($online_only=FALSE, $idle_only=FALSE) {
	return report_whoseonline(4, $online_only, $idle_only);
}

/**
 * Get banned users list
 * @param  integer $status User status
 * @return array Banned users
 */
function report_banned_list($status=0) {
	global $icon_config;
	$ret = array();

	$users = user_banned_list();
	foreach($users as $user) {
		$c_user = user_getdatabyuid($user['uid']);
		$c_username = $c_user['username'];
		$c_email = $c_user['email'];
		$c_status = $c_user['status'];

		if ($status && $c_status <> $status) {
			continue;
		}
		
		$c_is_admin = '';
		if ($c_status == '2') {
			$c_is_admin = $icon_config['admin'];
		}

		$ret[] = array(
			'username' => $c_username,
			'icon_is_admin' => $c_is_admin,
			'email' => $c_email,
			'bantime' => core_display_datetime($user['bantime']),
			'action_link' => _a('index.php?app=main&inc=feature_report&route=banned&op=unban&uid='.$user['uid'], $icon_config['unban']),
		);
	}

	return $ret;
}

/**
 * Get banned admin users list
 * @return array Banned users
 */
function report_banned_admin() {
	return report_banned_list(2);
}

/**
 * Get banned normal users list
 * @return array Banned users
 */
function report_banned_user() {
	return report_banned_list(3);
}

/**
 * Get banned subusers list
 * @return array Banned subusers
 */
function report_banned_subuser() {
	return report_banned_list(4);
}

/**
 * Remove login sessions older than 1 hour idle
 */
function report_hook_playsmsd() {
	global $plugin_config;
	$plugin_config['report']['current_tick'] = (int)strtotime(core_get_datetime());
	$period = $plugin_config['report']['current_tick'] - $plugin_config['report']['last_tick'];

	// login session older than 1 hour will be removed
	if ($period >= 60*60) {
		$users = report_whoseonline(0, FALSE, TRUE);
		foreach ($users as $user) {
			foreach ($user as $hash) {
				user_session_remove('', '', $hash['hash']);
				_log('login session removed uid:'.$hash['uid'].' hash:'.$hash['hash'], 3, 'report_hook_playsmsd');
			}
		}
		$plugin_config['report']['last_tick'] = $plugin_config['report']['current_tick'];
	}

}
