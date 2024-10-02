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

// admin force to be user instead for my account page
$is_user_inbox = 0;
if ($is_admin && isset($_REQUEST['user_inbox']) && (int) $_REQUEST['user_inbox'] === 1) {
	$is_admin = false;
	$is_user_inbox = 1;
}

// base URL
$base_url = 'index.php?app=main&inc=feature_report&route=all_inbox';

// if user inbox
if ($is_user_inbox) {
	$base_url .= '&user_inbox=1';
}

// just in case it will take a very long time, we don't want to display blank or 500 error
@set_time_limit(0);

switch (_OP_) {
	case "all_inbox":
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
				$sql_messages .= "in_msg LIKE '%" . core_sanitize_string($keyword) . "%'";
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
				$sql_from .= "in_sender LIKE '%" . core_sanitize_string($from) . "%'";
				$exists = true;
			}
			$sql_from .= ")";
		}

		// search to
		$sql_to = '';
		if ($is_admin && isset($_REQUEST['search_to']) && $_REQUEST['search_to']) {
			$tos = explode(',', $_REQUEST['search_to']);
			$sql_to = "AND (";
			$exists = false;
			foreach ( $tos as $to ) {
				if ($exists) {
					$sql_to .= " OR ";
				}
				$sql_to .= "username LIKE '%" . core_sanitize_string($to) . "%'";
				$exists = true;
			}
			$sql_to .= ")";
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
			$sql_dt = "AND (in_datetime BETWEEN '" . $frdt . "' AND '" . $todt . "')";
			$_REQUEST['search_frdt'] = core_display_datetime($frdt);
			$_REQUEST['search_todt'] = core_display_datetime($todt);
		}

		$search_form = "
			<script type='text/javascript' src='" . _HTTP_PATH_THEMES_ . "/common/jscss/moment/moment-with-langs.min.js'></script>	
			<link rel='stylesheet' href='" . _HTTP_PATH_THEMES_ . "/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css' />
			<script type='text/javascript' src='" . _HTTP_PATH_THEMES_ . "/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js'></script>

			<form id=fm_search_all_inbox name=fm_search_all_inbox action='" . $base_url . "&op=all_inbox' method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=search>
			<div class=table-responsive>
			<table class=playsms-table-list>
				<tr>
					<td>" . _('Search message') . "</td>
					<td><input type='text' name='search_messages' value='" . _display($_REQUEST['search_messages']) . "'> " . _hint('Seperate by comma for multiple search') . "</td>
				</tr>
				<tr>
					<td>" . _('From') . "</td>
					<td><input type='text' name='search_from' value='" . _display($_REQUEST['search_from']) . "'> " . _hint('Seperate by comma for multiple sender') . "</td>
				</tr>";
		if ($is_admin) {
			$search_form .= "
				<tr>
					<td>" . _('To') . "</td>
					<td><input type='text' name='search_to' value='" . _display($_REQUEST['search_to']) . "'> " . _hint('Seperate by comma for multiple receiver') . "</td>
				</tr>";
		}
		$search_form .= "
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
				</tr>
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
		$sql_search = $sql_messages . " " . $sql_from . " " . $sql_to . " " . $sql_dt;

		// save search SQL in session for export
		$_SESSION['tmp']['report']['sql_search'] = $sql_search;

		// prepare search query
		$query_search = "&search_message=" . urlencode($_REQUEST['search_messages']);
		$query_search .= "&search_from=" . urlencode($_REQUEST['search_from']);
		$query_search .= "&search_frdt=" . urlencode($_REQUEST['search_frdt']);
		$query_search .= "&search_todt=" . urlencode($_REQUEST['search_todt']);
		if ($is_admin) {
			$query_search .= "&search_to=" . urlencode($_REQUEST['search_to']);
		}

		// save search query in session for nav
		$_SESSION['tmp']['report']['query_search'] = $query_search;

		// end of build search form

		// get row counts
		if ($is_admin) {
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSInbox A LEFT JOIN " . _DB_PREF_ . "_tblUser B ON A.in_uid=B.uid WHERE A.flag_deleted=0 AND B.flag_deleted=0 " . $sql_search;
		} else {
			$db_query = "SELECT COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSInbox A LEFT JOIN " . _DB_PREF_ . "_tblUser B ON A.in_uid=B.uid WHERE A.in_uid='" . $user_config['uid'] . "' AND A.flag_deleted=0 AND B.flag_deleted=0 " . $sql_search;
		}
		//echo "<p>Count: " . $db_query . "</p>";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);

		// build nav		
		$count = (int) $db_row['count'];
		$nav = themes_nav($count, $base_url . '&op=all_inbox' . $query_search);

		// header
		$content_title = $is_admin ? _('Inbox messages') : _('My inbox');
		$content = _dialog() . "
			<h2>" . $content_title . "</h2>
			<div class=search_form_box>
				" . $search_form . "
				<p>" . sprintf(_('Found %s records'), $count) . "</p>
			</div>			
			<form id=fm_all_inbox name=fm_all_inbox action=\"" . $base_url . "&op=actions\" method=POST>
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
			<thead>";
		if ($is_admin) {
			$content .= "
				<tr>
					<th width=20%>" . _('From') . "</th>
					<th width=20%>" . _('To') . "</th>
					<th width=55%>" . _('Message') . "</th>
					<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_all_inbox)>
						<div class=pull-right>
							<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_all_inbox');\">" . $icon_config['delete'] . "</a>
						</div>
					</th>
				</tr>";
		} else {
			$content .= "
				<tr>
					<th width=20%>" . _('From') . "</th>
					<th width=55%>" . _('Message') . "</th>
					<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_all_inbox)>
						<div class=pull-right>
							<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_all_inbox');\">" . $icon_config['delete'] . "</a>
						</div>
					</th>
				</tr>";
		}
		$content .= "
			</thead>
			<tbody>";

		// get content
		if ($is_admin) {
			$db_query = "
				SELECT in_id, in_uid, in_sender, in_msg, in_datetime, username
				FROM " . _DB_PREF_ . "_tblSMSInbox A 
				LEFT JOIN " . _DB_PREF_ . "_tblUser B 
				ON A.in_uid=B.uid 
				WHERE A.flag_deleted=0 AND B.flag_deleted=0 " . $sql_search . "
				ORDER BY A.in_id DESC
				LIMIT " . (int) $nav['limit'] . "
				OFFSET " . (int) $nav['offset'];
		} else {
			$db_query = "
				SELECT in_id, in_uid, in_sender, in_msg, in_datetime
				FROM " . _DB_PREF_ . "_tblSMSInbox
				WHERE in_uid='" . $user_config['uid'] . "' AND flag_deleted=0 " . $sql_search . "
				ORDER BY in_id DESC
				LIMIT " . (int) $nav['limit'] . "
				OFFSET " . (int) $nav['offset'];
		}
		//echo "<p>List: " . $db_query . "</p>";
		$db_result = dba_query($db_query);

		// iterate content
		$j = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$db_row = _display($db_row);
			$in_id = $db_row['in_id'];
			$in_uid = $db_row['in_uid'];
			$in_sender = $db_row['in_sender'];
			$current_in_sender = report_resolve_sender($in_uid, $in_sender);
			$in_datetime = core_display_datetime($db_row['in_datetime']);
			if ($is_admin) {
				$in_receiver = $db_row['username'];
			} else {
				$in_receiver = $user_config['username'];
			}

			$msg = $db_row['in_msg'];
			$in_msg = $msg;
			if ($msg && $in_sender) {
				$reply = _sendsms($in_sender, $msg, $base_url . "&op=all_inbox" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, $icon_config['reply']);
				$forward = _sendsms('', $msg, $base_url . "&op=all_inbox" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, $icon_config['forward']);
			}

			$pm = '';
			if ($is_admin && $in_username && $in_username != $user_config['username']) {
				$pm = _sendsms('@' . $in_username, '', $base_url . "&op=all_inbox" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search, '@' . $in_username);
			}

			$c_message = "
				<div id=\"msg_label\">" . $in_datetime . "&nbsp;" . $in_status . "</div>
				<div id=\"all_inbox_msg\">" . $in_msg . "</div>
				<div id=\"msg_option\">" . $reply . " " . $forward . "<strong>" . $pm . "</strong></div>";
			$j++;
			if ($is_admin) {
				$content .= "
					<tr>
						<td><div>" . $current_in_sender . "</div></td>
						<td><div>" . $in_receiver . "</div></td>
						<td>" . $c_message . "</td>
						<td>
							<input type=checkbox name=itemid[] value='" . $in_id . "'>
						</td>
					</tr>";
			} else {
				$content .= "
					<tr>
						<td><div>" . $current_in_sender . "</div></td>
						<td>" . $c_message . "</td>
						<td>
							<input type=checkbox name=itemid[] value='" . $in_id . "'>
						</td>
					</tr>";
			}
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
					<h2>" . _('Feature messages') . "</h2>
					<div class=search_form_box>
						<p>" . sprintf(_('Export %s records as CSV'), $count) . " " . _hint(sprintf(_('Maximum records for export is %s'), $count)) . "</p>
						<p>" . themes_button($base_url . "&op=actions&go=export_yes", _('Download')) . "</p>
						<p>" . themes_button_back($base_url . "&op=all_inbox" . '&page=' . $nav['page'] . '&nav=' . $nav['nav'] . $query_search) . "</p>
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
						_('Time'),
						_('From'),
						_('To'),
						_('Message')
					];

				} else {
					$data[0] = [
						_('Time'),
						_('From'),
						_('Message')
					];
				}

				// get content
				if ($is_admin) {
					$db_query = "
						SELECT A.in_id AS in_id, A.in_uid AS in_uid, A.in_sender AS in_sender, A.in_msg AS in_msg, A.in_datetime AS in_datetime, B.username AS username
						FROM " . _DB_PREF_ . "_tblSMSInbox A 
						LEFT JOIN " . _DB_PREF_ . "_tblUser B 
						ON A.in_uid=B.uid 
						WHERE A.flag_deleted=0 AND B.flag_deleted=0 " . $sql_search . "
						ORDER BY A.in_id DESC
						LIMIT " . (int) $nav['limit'] . "
						OFFSET " . (int) $nav['offset'];
				} else {
					$db_query = "
						SELECT in_id, in_uid, in_sender, in_msg, in_datetime
						FROM " . _DB_PREF_ . "_tblSMSInbox
						WHERE in_uid='" . $user_config['uid'] . "' AND flag_deleted=0 " . $sql_search . "
						ORDER BY in_id DESC
						LIMIT " . (int) $nav['limit'] . "
						OFFSET " . (int) $nav['offset'];
				}
				$db_result = dba_query($db_query);

				// iterate content
				$j = 0;
				while ($db_row = dba_fetch_array($db_result)) {
					$j++;
					if ($is_admin) {
						$data[$j] = [
							core_display_datetime($db_row['in_datetime']),
							$db_row['in_sender'],
							$db_row['username'],
							$db_row['in_msg']
						];
					} else {
						$data[$j] = [
							core_display_datetime($db_row['in_datetime']),
							$db_row['in_sender'],
							$db_row['in_msg']
						];
					}
				}

				// number of rows
				$count = count($data) - 1;

				// format csv
				$content = core_csv_format($data);

				// prepare file name
				$fn = 'inbox_' . $count . '_rec_' . $core_config['datetime']['now_stamp'] . '.csv';

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
							'in_id' => $itemid
						];
						if (!$is_admin) {
							$conditions['in_uid'] = $user_config['uid'];
						}
						dba_update(_DB_PREF_ . '_tblSMSInbox', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected inbox messages has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
