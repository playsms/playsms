<?php

if (!valid()) {
	forcenoaccess();
};

$smslog_id = $_GET ['smslog_id'];

switch ($op) {
	case "report_user" :
		// SMS PENDING
		$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND p_status='0' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows_pending = $db_row ['count'];

		// SMS SENT
		$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND p_status='1' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows_sent = $db_row ['count'];

		// SMS DELIVERED
		$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND p_status='3' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows_delivered = $db_row ['count'];

		// SMS FAILED
		$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND p_status='2' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows_failed = $db_row ['count'];

		// SMS DELETED
		$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='1'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows_deleted = $db_row ['count'];

		// BILLING
		$billing = 0;
		$data = billing_getdata_by_uid($uid);
		foreach ($data AS $a) {
			$billing += $a['count'] * $a['rate'];
		}
		
		// CREDIT
		$credit = $core_config['user']['credit'];

		unset($tpl);
		$tpl = array(
		    'name' => 'report_user',
		    'var' => array(
			'Report' => _('Report'),
			'My report' => _('My report'),
			'Pending' => _('Pending'),
			'Sent' => _('Sent'),
			'Delivered' => _('Delivered'),
			'Failed' => _('Failed'),
			'Deleted' => _('Deleted'),
			'Billing' => _('Billing'),
			'Credit' => _('Credit'),
			'num_rows_pending' => $num_rows_pending,
			'num_rows_sent' => $num_rows_sent,
			'num_rows_delivered' => $num_rows_delivered,
			'num_rows_failed' => $num_rows_failed,
			'num_rows_deleted' => $num_rows_deleted,
			'billing' => $billing,
			'credit' => $credit
		    )
		);
		echo tpl_apply($tpl);
		break;

	case "report_admin" :
		if (!isadmin()) {
			forcenoaccess();
		};

		unset($tpl);
		$tpl = array(
		    'name' => 'report_admin',
		    'var' => array(
			'Report' => _('Report'),
			'All reports' => _('All reports'),
			'User' => _('User'),
			'Pending' => _('Pending'),
			'Sent' => _('Sent'),
			'Delivered' => _('Delivered'),
			'Failed' => _('Failed'),
			'Billing' => _('Billing'),
			'Credit' => _('Credit'),
		    )
		);
		
		$l = 0;
		
		// USER LIST RESTRIVAL
		$db_queryU = "SELECT * FROM " . _DB_PREF_ . "_tblUser ORDER BY status, username";
		$db_resultU = dba_query($db_queryU);
		while ($db_rowU = dba_fetch_array($db_resultU)) {
			$l++;
			$c_username = $db_rowU['username'];
			$c_uid = $db_rowU['uid'];
			$c_credit = $db_rowU['credit'];
			$c_status = $db_rowU['status'];

			// SMS SENT
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$c_uid' AND p_status='1' AND flag_deleted='0'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$num_rows_sent = $db_row ['count'];
			$sum_num_rows_sent = ($sum_num_rows_sent + $num_rows_sent);

			// SMS PENDING
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$c_uid' AND p_status='0' AND flag_deleted='0'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$num_rows_pending = $db_row ['count'];
			$sum_num_rows_pending = ($sum_num_rows_pending + $num_rows_pending);

			// SMS DELIVERED
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$c_uid' AND p_status='3' AND flag_deleted='0'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$num_rows_delivered = $db_row ['count'];
			$sum_num_rows_delivered = ($sum_num_rows_delivered + $num_rows_delivered);

			// SMS FAILED
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$c_uid' AND p_status='2' AND flag_deleted='0'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$num_rows_failed = $db_row ['count'];
			$sum_num_rows_failed = ($sum_num_rows_failed + $num_rows_failed);

			// BILLING
			$c_billing = 0;
			$c_data = billing_getdata_by_uid($c_uid);
			foreach ($c_data AS $a) {
				$c_billing += $a['count'] * $a['rate'];
			}
			
			$sum_billing += $c_billing;
			$sum_credit += $c_credit;
			
			$c_is_admin = '';
			if ($c_status=='2') {
				$c_is_admin = "<i class='glyphicon glyphicon-asterisk playsms-mandatory' data-toggle=tooltip title='"._('This user is administrator')."' rel=tooltip></i>";
			}
			

			$tpl['loop']['data'][] = array(
				'tr_class' => $tr_class,
				'c_username' => $c_username,
				'c_is_admin' => $c_is_admin,
				'num_rows_pending' => $num_rows_pending,
				'num_rows_sent' => $num_rows_sent,
				'num_rows_delivered' => $num_rows_delivered,
				'num_rows_failed' => $num_rows_failed,
				'c_billing' => $c_billing,
				'c_credit' => $c_credit
			);
		}

		$sum_total = ($sum_num_rows_pending + $sum_num_rows_delivered + $sum_num_rows_sent + $sum_num_rows_failed);

		$tpl['var']['Total'] = _('Total');
		$tpl['var']['sum_total'] = $sum_total;
		$tpl['var']['sum_num_rows_pending'] = $sum_num_rows_pending;
		$tpl['var']['sum_num_rows_sent'] = $sum_num_rows_sent;
		$tpl['var']['sum_num_rows_delivered'] = $sum_num_rows_delivered;
		$tpl['var']['sum_num_rows_failed'] = $sum_num_rows_failed;
		$tpl['var']['sum_billing'] = $sum_billing;
		$tpl['var']['sum_credit'] = $sum_credit;

		echo tpl_apply($tpl);
		break;
}
?>