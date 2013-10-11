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
		$db_query = "SELECT SUM(p_credit) AS billing FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$billing = $db_row ['billing'];
		
		// BALANCE
		$balance = $core_config['user']['credit'];

		$content = "
			<h2>" . _('Report') . "</h2>
			<h3>" . _('My report') . "</h3>
			<table width=100%>
				<thead><tr>
					<th align='center' width='10%'>" . _('Pending') . "</th>
					<th align='center' width='10%'>" . _('Sent') . "</th>
					<th align='center' width='10%'>" . _('Delivered') . "</th>
					<th align='center' width='10%'>" . _('Failed') . "</th>
					<th align='center' width='10%'>" . _('Deleted') . "</th>
					<th align='center' width='15%'>" . _('Billing') . "</th>
					<th align='center' width='15%'>" . _('Balance') . "</th>
				</tr></thead>
				<tbody><tr class=row_odd>
					<td align=center>$num_rows_pending</td>
					<td align=center>$num_rows_sent</td>
					<td align=center>$num_rows_delivered</td>
					<td align=center>$num_rows_failed</td>
					<td align=center>$num_rows_deleted</td>
					<td align=center>$billing</td>
					<td align=center>$balance</td>
				</tr></tbody>
			</table>";
		echo $content;
		break;

	case "report_admin" :
		if (!isadmin()) {
			forcenoaccess();
		};

		$content = "
			<h2>" . _('Report') . "</h2>
			<h2>" . _('All reports') . "</h2>
			<table width=100% class=sortable>
			<thead><tr>
				<th align='center' width='20%'>" . _('User') . "</th>
				<th align='center' width='10%'>" . _('Pending') . "</th>
				<th align='center' width='10%'>" . _('Sent') . "</th>
				<th align='center' width='10%'>" . _('Delivered') . "</th>
				<th align='center' width='10%'>" . _('Failed') . "</th>
				<th align='center' width='20%'>" . _('Billing') . "</th>
				<th align='center' width='20%'>" . _('Balance') . "</th>
			</tr></thead>
			<tbody>";
		
		$l = 0;
		
		// USER LIST RESTRIVAL
		$db_queryU = "SELECT * FROM " . _DB_PREF_ . "_tblUser ORDER BY status, username";
		$db_resultU = dba_query($db_queryU);
		while ($db_rowU = dba_fetch_array($db_resultU)) {
			$l++;
			$c_username = $db_rowU['username'];
			$c_uid = $db_rowU['uid'];
			$c_balance = $db_rowU['credit'];
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

			$c_billing = '0.0';
			
			$sum_billing += $c_billing;
			$sum_balance += $c_balance;
			
			$c_is_admin = '';
			if ($c_status=='2') {
				$c_is_admin = $nd;
			}
			
			// $td_class = "box_text_odd";
			$tr_class = ($l & 1) ? 'row_odd' : 'row_even';
			$content .= "
				<tr class=$tr_class>
					<td align=center>$c_username $c_is_admin</td>
					<td align=center>$num_rows_pending</td>
					<td align=center>$num_rows_sent</td>
					<td align=center>$num_rows_delivered</td>
					<td align=center>$num_rows_failed</td>
					<td align=center>$c_billing</td>
					<td align=center>$c_balance</td>
				</tr>";
		}

		$sum_total = ($sum_num_rows_pending + $sum_num_rows_delivered + $sum_num_rows_sent + $sum_num_rows_failed);
		$content .= "
			<thead><tr>
				<th align=center>" . _('Total') . ": ".$sum_total."</td>
				<th align=center>$sum_num_rows_pending</th>
				<th align=center>$sum_num_rows_sent</th>
				<th align=center>$sum_num_rows_delivered</th>
				<th align=center>$sum_num_rows_failed</th>
				<th align=center>$sum_billing</th>
				<th align=center>$sum_balance</th>
			</tr></thead>
			</tbody></table>";
		echo $content;
		break;
}
?>