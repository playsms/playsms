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
