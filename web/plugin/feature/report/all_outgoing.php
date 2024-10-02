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

if (!auth_isvalid()) {
	auth_block();
}

// check if we are admin
$is_admin = auth_isadmin() ? true : false;

// base URL
$base_url = 'index.php?app=main&inc=feature_report&route=all_outgoing';

// just in case it will take a very long time, we don't want to display blank or 500 error
@set_time_limit(0);

switch (_OP_) {
	case "all_outgoing":
		// build search form

		// search messages
		$sql_messages = '';
		if (isset($_REQUEST['search_messages']) && $_REQUEST['search_messages']) {
			$messages = explode(',', $_REQUEST['search_messages']);
			$sql_messages = "AND (";
			$exists = false;
			foreach ( $messages as $keyword ) {
				if ($exists) {
					$sql_messages .= " OR ";
				}
				$sql_messages .= "p_msg LIKE '%" . core_sanitize_string($keyword) . "%'";
				$exists = true;
			}
			$sql_messages .= ")";
		}

		// search from
		$sql_from = '';
		if (isset($_REQUEST['search_from']) && $_REQUEST['search_from']) {
			$froms = explode(',', $_REQUEST['search_from']);
			$sql_from = "AND (";
			$exists = false;
			foreach ( $froms as $from ) {
				if ($exists) {
					$sql_from .= " OR ";
				}
				$sql_from .= "p_src LIKE '%" . core_sanitize_string($from) . "%'";
				$exists = true;
			}
			$sql_from .= ")";
		}

		// search to
		$sql_to = '';
		if (isset($_REQUEST['search_to']) && $_REQUEST['search_to']) {
			$tos = explode(',', $_REQUEST['search_to']);
			$sql_to = "AND (";
			$exists = false;
			foreach ( $tos as $to ) {
				if ($exists) {
					$sql_to .= " OR ";
				}
				$sql_to .= "p_dst LIKE '%" . core_sanitize_string($to) . "%'";
				$exists = true;
			}
			$sql_to .= ")";
		}

		// search SMSC or Gateway
		$sql_smsc_gw = '';
		if ($is_admin && isset($_REQUEST['search_smsc_gw']) && $_REQUEST['search_smsc_gw']) {
			$smsc_gws = explode(',', $_REQUEST['search_smsc_gw']);
			$sql_smsc_gw = "AND (";
			$exists = false;
			foreach ( $smsc_gws as $smsc_gw ) {
				if ($exists) {
					$sql_smsc_gw .= " OR ";
				}
				$sql_smsc_gw .= "p_smsc LIKE '%" . core_sanitize_string($smsc_gw) . "%' OR p_gateway='%" . core_sanitize_string($smsc_gw) . "%'";
				$exists = true;
			}
			$sql_smsc_gw .= ")";
		}

		// search username
		$sql_username = '';
		if ($is_admin && isset($_REQUEST['search_username']) && $_REQUEST['search_username']) {
			$usernames = explode(',', $_REQUEST['search_username']);
			$sql_username = "AND (";
			$exists = false;
			foreach ( $usernames as $username ) {
				if ($username && $uid = user_username2uid(core_sanitize_username($username))) {
					if ($exists) {
						$sql_to .= " OR ";
					}
					$sql_username .= "uid='" . $uid . "'";
					$exists = true;
				}
			}
			$sql_username .= ")";
			if (!$exists) {
				$sql_username = '';
				$_REQUEST['search_username'] = '';
			}
		}

		// search date/time
		// $frdt_ts older smaller timestamp
		// $todt_ts younger bigger timestamp
		$frdt_ts = 0;
		if (isset($_REQUEST['search_frdt']) && $_REQUEST['search_frdt']) {
			$search_frdt = core_adjust_datetime($_REQUEST['search_frdt']);
			$frdt_ts = strtotime($search_frdt);
		}
		$todt_ts = 0;
		if (isset($_REQUEST['search_todt']) && $_REQUEST['search_todt']) {
			$search_todt = core_adjust_datetime($_REQUEST['search_todt']);
			$todt_ts = strtotime($search_todt);
		}
		// to dt is empty
		if ($frdt_ts > 0 && $todt_ts === 0) {
			// fill to dt with time now		
			$todt_ts = strtotime(core_get_datetime());
		}
		// from dt is empty
		if ($todt_ts > 0 && $frdt_ts === 0) {
			// switch them and make to dt time now
			$frdt_ts = $todt_ts;
			$todt_ts = strtotime(core_get_datetime());
		}
		// if somehow from time is younger than to time
		if ($frdt_ts > $todt_ts) {
			// switch them
			list($todt_ts, $frdt_ts) = [$frdt_ts, $todt_ts];
		}
		$sql_dt = '';
		if ($frdt_ts > 0 && $todt_ts > 0) {
			$frdt = core_convert_datetime($frdt_ts);
			$todt = core_convert_datetime($todt_ts);
			$sql_dt = "AND (p_datetime BETWEEN '" . $frdt . "' AND '" . $todt . "')";
			$_REQUEST['search_frdt'] = core_display_datetime($frdt);
			$_REQUEST['search_todt'] = core_display_datetime($todt);
		}

		$search_form = "
			<script type='text/javascript' src='" . _HTTP_PATH_THEMES_ . "/common/jscss/moment/moment-with-langs.min.js'></script>	
			<link rel='stylesheet' href='" . _HTTP_PATH_THEMES_ . "/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css' />
			<script type='text/javascript' src='" . _HTTP_PATH_THEMES_ . "/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js'></script>

			<form id=fm_search_all_outgoing name=fm_search_all_outgoing action='" . $base_url . "&op=all_outgoing' method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=search>
			<div class=table-responsive>
			<table class=playsms-table-list>
				<tr>
					<td>" . _('Search message') . "</td>
					<td><input type='text' name='search_messages' value='" . _display($_REQUEST['search_messages']) . "'> " . _hint('Seperate by comma for multiple search') . "</td>
				</tr>
				<tr>
					<td>" . _('Sender') . "</td>
					<td><input type='text' name='search_from' value='" . _display($_REQUEST['search_from']) . "'> " . _hint('Seperate by comma for multiple sender') . "</td>
				</tr>
				<tr>
					<td>" . _('Receiver') . "</td>
					<td><input type='text' name='search_to' value='" . _display($_REQUEST['search_to']) . "'> " . _hint('Seperate by comma for multiple receiver') . "</td>
				</tr>
				<tr>
					<td>" . _('From date/time') . "</td>
					<td>
						<input type='text' id='search_frdt' name='search_frdt' value='" . _display($_REQUEST['search_frdt']) . "'> " . _hint('Format: YYYY-MM-DD hh:mm:ss') . "
					</td>
				</tr>
				<tr>
					<td>" . _('To date/time') . "</td>
					<td>
						<input type='text' id='search_todt' name='search_todt' value='" . _display($_REQUEST['search_todt']) . "'> " . _hint('Format: YYYY-MM-DD hh:mm:ss') . "
					</td>
				</tr>";
		if ($is_admin) {
			$search_form .= "
				<tr>
					<td>" . _('SMSC or gateway') . "</td>
					<td><input type='text' name='search_smsc_gw' value='" . _display($_REQUEST['search_smsc_gw']) . "'> " . _hint('Seperate by comma for multiple SMSC or gateway') . "</td>
				</tr>
				<tr>
					<td>" . _('Username') . "</td>
					<td><input type='text' name='search_username' value='" . _display($_REQUEST['search_username']) . "'> " . _hint('Seperate by comma for multiple username') . "</td>
				</tr>";
		}
		$search_form .= "
				<tr>
					<td colspan=2>
						<input type=submit class=button style='display:none;' name=submit_search value='" . _('Search') . "'>
					</td>
				</tr>
			</table>		
			</div>
			</form>
			<script type='text/javascript'>
				$(function () {
					$('#search_frdt').datetimepicker({
						format: 'YYYY-MM-DD HH:mm:ss'
					});
					$('#search_todt').datetimepicker({
						format: 'YYYY-MM-DD HH:mm:ss'
					});
				});
			</script>";

		// prepare search SQL
		$sql_search = $sql_messages . " " . $sql_from . " " . $sql_to . " " . $sql_smsc_gw . " " . $sql_username . " " . $sql_dt;

		// save search SQL in session for export
		$_SESSION['tmp']['report']['sql_search'] = $sql_search;

		// prepare search query
		$query_search = "&search_messages=" . urlencode($_REQUEST['search_messages']);
		$query_search .= "&search_from=" . urlencode($_REQUEST['search_from']);
		$query_search .= "&search_to=" . urlencode($_REQUEST['search_to']);
		$query_search .= "&search_frdt=" . urlencode($_REQUEST['search_frdt']);
		$query_search .= "&search_todt=" . urlencode($_REQUEST['search_todt']);
		if ($is_admin) {
			$query_search .= "&search_smsc_gw=" . urlencode($_REQUEST['search_smsc_gw']);
			$query_search .= "&search_username=" . urlencode($_REQUEST['search_username']);
		}

		// save search query in session for nav
		$_SESSION['tmp']['report']['query_search'] = $query_search;

		// end of build search form

		// get row counts
		if ($is_admin) {
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE flag_deleted=0 " . $sql_search;
		} else {
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE uid='" . $user_config['uid'] . "' AND flag_deleted=0 " . $sql_search;
		}
		//echo "<p>Fixme: " . $db_query . "</p>";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);

		// build nav		
		$count = (int) $db_row['count'];
		$nav = themes_nav($count, $base_url . '&op=all_outgoing' . $query_search);

		// header
		$content = _dialog() . "
			<h2>" . _('Sent messages') . "</h2>
			<div class=search_form_box>
				" . $search_form . "
				<p>" . sprintf(_('Found %s records'), $count) . "</p>
			</div>			
			<form id=fm_all_outgoing name=fm_all_outgoing action=\"" . $base_url . "&op=actions\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u($base_url . '&op=actions&go=export&search_count=' . $count) . $query_search . "\">" . $icon_config['export'] . "</a>
				</div>
				<div align=center>" . $nav['form'] . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=20%>" . _('To') . "</th>
				<th width=75%>" . _('Message') . "</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_all_outgoing)>
					<div class=pull-right>
						<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_all_outgoing');\">" . $icon_config['delete'] . "</a>
					</div>
				</th>
			</tr>
			</thead>
			<tbody>";

		// get content
		if ($is_admin) {
			$db_query = "
				SELECT uid, p_gateway, p_smsc, smslog_id, p_dst, p_sms_type, p_msg, p_footer, p_datetime, p_update, p_status, queue_code
				FROM " . _DB_PREF_ . "_tblSMSOutgoing
				WHERE flag_deleted=0 " . $sql_search . "
				ORDER BY id DESC
				LIMIT " . (int) $nav['limit'] . "
				OFFSET " . (int) $nav['offset'];
		} else {
			$db_query = "
				SELECT uid, p_gateway, p_smsc, smslog_id, p_dst, p_sms_type, p_msg, p_footer, p_datetime, p_update, p_status, queue_code
				FROM " . _DB_PREF_ . "_tblSMSOutgoing
				WHERE uid='" . $user_config['uid'] . "' AND flag_deleted=0 " . $sql_search . "
				ORDER BY id DESC
				LIMIT " . (int) $nav['limit'] . "
				OFFSET " . (int) $nav['offset'];
		}
		//echo $db_query;
		$db_result = dba_query($db_query);

		// iterate content
		$j = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$db_row = _display($db_row);
			$smslog_id = $db_row['smslog_id'];
			$p_uid = $db_row['uid'];
			$p_dst = $db_row['p_dst'];
			$current_p_dst = report_resolve_sender($p_uid, $p_dst);
			$p_sms_type = $db_row['p_sms_type'];
			if (($p_footer = $db_row['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg . ' ' . $p_footer;
			}
			$p_datetime = core_display_datetime($db_row['p_datetime']);
			$p_update = $db_row['p_update'];
			$p_status = $db_row['p_status'];
			$p_username = '';
			$p_gateway = '';
			$p_smsc = '';
			if ($is_admin) {
				$p_username = user_uid2username($db_row['uid']);
				$p_gateway = $db_row['p_gateway'];
				$p_smsc = $db_row['p_smsc'];
			}

			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1") {
				$p_status = "<span class=status_sent title='" . _('Sent') . "'/>";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed title='" . _('Failed') . "'/>";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered title='" . _('Delivered') . "'/>";
			} else {
				$p_status = "<span class=status_pending title='" . _('Pending') . "'/>";
			}

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ($billing['count'] ? $billing['count'] : '0');
			$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
			$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
			$p_charge = $db_row['p_status'] == 2 ? '0.0' : $p_charge;

			$msg = $db_row['p_msg'];
			$p_msg = $msg;
			if ($msg && $p_dst) {
				$resend = _sendsms($p_dst, $msg, $base_url . "&op=all_outgoing" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, $icon_config['resend']);
				$forward = _sendsms('', $msg, $base_url . "&op=all_outgoing" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, $icon_config['forward']);
			}

			$pm = '';
			if ($is_admin && $p_username && $p_username != $user_config['username']) {
				$pm = _sendsms('@' . $p_username, '', $base_url . "&op=all_outgoing" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, '@' . $p_username);
			}

			$c_message = "
				<div id=\"msg_label\">" . $p_datetime . "&nbsp;" . _('count') . ":" . $p_count . "&nbsp;" . _('rate') . ":" . $p_rate . "&nbsp;" . _('cost') . ":" . $p_charge . "&nbsp;" . $p_status . "</div>
				<div id=\"all_outgoing_msg\">" . $p_msg . "</div>
				<div id=\"msg_option\">" . $resend . " " . $forward . "<strong>" . $pm . "</strong> " . $p_smsc . " " . $p_gateway . "</div>";

			$j++;
			$content .= "
				<tr>
					<td><div>" . $current_p_dst . "</div></td>
					<td>" . $c_message . "</td>
					<td>
						<input type=checkbox name=itemid[] value='" . $smslog_id . "'>
					</td>
				</tr>";
		}

		// footer
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u($base_url . '&op=actions&go=export&search_count=' . $count) . $query_search . "\">" . $icon_config['export'] . "</a>
				</div>
				<div align=center>" . $nav['form'] . "</div>
			</div>
			</form>";

		// display content
		_p($content);
		break;

	case "actions":
		// get last nav from session
		$nav = themes_nav_session();

		$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : '';
		switch ($go) {
			case 'export':
				// get last query search from session
				if (isset($_SESSION['tmp']['report']['query_search']) && $_SESSION['tmp']['report']['query_search']) {
					$query_search = $_SESSION['tmp']['report']['query_search'];
				} else {
					$query_search = '';
				}

				// get row counts
				$count = isset($_REQUEST['search_count']) && (int) $_REQUEST['search_count'] ? (int) $_REQUEST['search_count'] : 0;
				$count = $count > (int) $report_export_limit ? (int) $report_export_limit : $count;

				$content = _dialog() . "
					<h2>" . _('Sent messages') . "</h2>
					<div class=search_form_box>
						<p>" . sprintf(_('Export %s records as CSV'), $count) . " " . _hint(sprintf(_('Maximum records for export is %s'), $count)) . "</p>
						<p>" . themes_button($base_url . "&op=actions&go=export_yes", _('Download')) . "</p>
						<p>" . themes_button_back($base_url . "&op=all_outgoing" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search) . "</p>
					</div>";
				_p($content);
				break;

			case 'export_yes':
				// get last search SQL from session
				if (isset($_SESSION['tmp']['report']['sql_search']) && $_SESSION['tmp']['report']['sql_search']) {
					$sql_search = $_SESSION['tmp']['report']['sql_search'];
				} else {
					$sql_search = '';
				}

				// header
				if ($is_admin) {
					$data[0] = [
						_('User'),
						_('Gateway'),
						_('SMSC'),
						_('Time'),
						_('To'),
						_('Message'),
						_('Status'),
						_('Count'),
						_('Rate'),
						_('Charge')
					];

				} else {
					$data[0] = [
						_('Time'),
						_('To'),
						_('Message'),
						_('Status'),
						_('Count'),
						_('Rate'),
						_('Charge')
					];
				}

				// get content
				if ($is_admin) {
					$db_query = "
						SELECT uid, p_gateway, p_smsc, smslog_id, p_dst, p_sms_type, p_msg, p_footer, p_datetime, p_update, p_status, queue_code
						FROM " . _DB_PREF_ . "_tblSMSOutgoing
						WHERE flag_deleted=0 " . $sql_search . "
						ORDER BY id DESC
						LIMIT " . (int) $report_export_limit . "
						OFFSET 0";
				} else {
					$db_query = "
						SELECT uid, p_gateway, p_smsc, smslog_id, p_dst, p_sms_type, p_msg, p_footer, p_datetime, p_update, p_status, queue_code
						FROM " . _DB_PREF_ . "_tblSMSOutgoing
						WHERE uid='" . $user_config['uid'] . "' AND flag_deleted=0 " . $sql_search . "
						ORDER BY id DESC
						LIMIT " . (int) $report_export_limit . "
						OFFSET 0";
				}
				$db_result = dba_query($db_query);

				// iterate content
				$j = 0;
				while ($db_row = dba_fetch_array($db_result)) {
					// get billing info
					$billing = billing_getdata($db_row['smslog_id']);
					$p_count = $billing['count'] ? $billing['count'] : '0';
					$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
					$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
					$p_charge = $db_row['p_status'] == 2 ? '0.0' : $p_charge;

					$j++;
					if ($is_admin) {
						$data[$j] = [
							user_uid2username($db_row['uid']),
							$db_row['p_gateway'],
							$db_row['p_smsc'],
							core_display_datetime($db_row['p_datetime']),
							$db_row['p_dst'],
							$db_row['p_msg'] . $db_row['p_footer'],
							$db_row['p_status'],
							$p_count,
							$p_rate,
							$p_charge
						];
					} else {
						$data[$j] = [
							core_display_datetime($db_row['p_datetime']),
							$db_row['p_dst'],
							$db_row['p_msg'] . $db_row['p_footer'],
							$db_row['p_status'],
							$p_count,
							$p_rate,
							$p_charge
						];
					}
				}

				// number of rows
				$count = count($data) - 1;

				// format csv
				$content = core_csv_format($data);

				// prepare file name
				$fn = 'sent_' . $count . '_rec_' . $core_config['datetime']['now_stamp'] . '.csv';

				// download
				core_download($content, $fn, 'text/csv');
				exit();

			case 'delete':
				if (isset($_POST['itemid'])) {
					foreach ($_POST['itemid'] as $itemid) {
						$up = [
							'c_timestamp' => time(),
							'flag_deleted' => '1'
						];
						$conditions = [
							'smslog_id' => $itemid
						];
						if (!$is_admin) {
							$conditions['uid'] = $user_config['uid'];
						}
						dba_update(_DB_PREF_ . '_tblSMSOutgoing', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected sent messages has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
