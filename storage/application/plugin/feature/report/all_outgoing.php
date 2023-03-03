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

if (!auth_isadmin()) {
	auth_block();
}

@set_time_limit(0);

switch (_OP_) {
	case "all_outgoing":
		$search_category = array(
			_('User') => 'username',
			_('Gateway') => 'p_gateway',
			_('SMSC') => 'p_smsc',
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer',
			_('Queue') => 'queue_code',
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=all_outgoing&op=all_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
		$fields = "B.username, A.p_gateway, A.p_smsc, A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, B.uid, A.queue_code";
		$conditions = [
			'A.flag_deleted' => 0,
		];
		$extras = [];
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$conditions['A.queue_code'] = $queue_code;
			$queue_label = "<p class=lead>" . sprintf(_('List of queue %s'), $queue_code) . "</p>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
		} else {
			$fields .= ", COUNT(A.queue_code) AS queue_count";
			$extras['GROUP BY'] = "A.queue_code";
		}
		
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$extras['ORDER BY'] = "A.smslog_id DESC";
		$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		$tmpCount = $list ? count($list) : 0;
		$nav = themes_nav($tmpCount, $search['url']);
		$extras['LIMIT'] = $nav['limit'];
		$extras['OFFSET'] = $nav['offset'];
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('All sent messages') . "</h2>
			" . $queue_label . "
			<p>" . $search['form'] . "</p>
			<form id=fm_all_outgoing name=fm_all_outgoing action=\"index.php?app=main&inc=feature_report&route=all_outgoing&op=actions&queue_code=" . $queue_code . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=all_outgoing&op=actions&go=export&queue_code=' . $queue_code) . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_all_outgoing', 'delete') . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=10%>" . _('User') . "</th>
				<th width=15%>" . _('Date/Time') . "</th>
				<th width=15%>" . _('To') . "</th>
				<th width=57%>" . _('Message') . "</th>
				<th width=3% class=\"sorttable_nosort\" nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_all_outgoing)></th>
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$p_username = $list[$j]['username'];
			$p_gateway = $list[$j]['p_gateway'];
			$p_smsc = $list[$j]['p_smsc'];
			$smslog_id = $list[$j]['smslog_id'];
			$p_uid = $list[$j]['uid'];
			$p_dst = $list[$j]['p_dst'];
			$current_p_dst = report_resolve_sender($p_uid, $p_dst);
			$p_sms_type = $list[$j]['p_sms_type'];
			if (($p_footer = $list[$j]['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg . ' ' . $p_footer;
			}
			$p_datetime = core_display_datetime($list[$j]['p_datetime']);
			$p_update = $list[$j]['p_update'];
			$p_status = $list[$j]['p_status'];
			$c_queue_code = $list[$j]['queue_code'];
			$c_queue_count = (int) $list[$j]['queue_count'];
			
			$queue_view_link = "";
			if ($c_queue_count > 1) {
				$queue_view_link = "<a href='" . $base_url . "&queue_code=" . $c_queue_code . "'>" . sprintf(_('view all %d'), $c_queue_count) . "</a>";
			}
			
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1") {
				$p_status = "<span class=status_sent title='" . _('Sent') . "'></span>";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed title='" . _('Failed') . "'></span>";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered title='" . _('Delivered') . "'></span>";
			} else {
				$p_status = "<span class=status_pending title='" . _('Pending') . "'></span>";
			}
			$p_status = "<span class='msg_status'>" . $p_status . "</span>";

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ($billing['count'] ? $billing['count'] : '0');
			$p_count = "<span class='msg_price'>" . $p_count . " sms</span>";

			$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
			$p_rate = "<span class='msg_rate'><span class='playsms-icon fas fa-table' title='" . _('Rate') . "'></span>" . $p_rate . "</span>";

			$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
			$p_charge = "<span class='msg_charge'><span class='playsms-icon fas fa-file-invoice-dollar' title='" . _('Charge') . "'></span>" . $p_charge . "</span>";

			// if send SMS failed then display charge as 0
			if ($list[$j]['p_status'] == 2) {
				$p_charge = '0.00';
			}

			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _sendsms($p_dst, $msg, $icon_config['resend']);
				$forward = _sendsms('', $msg, $icon_config['forward']);
			}
			$c_message = "
				<div class=\"row\">
					<div class=\"col-sm\">
						<div id=\"user_outgoing_msg\">
							<div class='msg_text'>" . $p_msg . "</div>
						</div>
					</div>
					<div class=\"col-sm\">
						<div class=\"row pull-right\">
							<div class=\"col d-none d-md-block\">
								<div class=\"msg_option\">" . $resend . " " . $forward . "</div>
								<div class=\"msg_info\">" . $p_status . " " . $p_count . " " . $p_rate . " " . $p_charge . "</div>
							</div>
						</div>
					</div>
				</div>
			";
			$content .= "
				<tr>
					<td>$p_username</td>
					<td>$p_datetime</td>
					<td><div>" . $current_p_dst . "</div><div>" . $queue_view_link . "</div></td>
					<td>$c_message</td>
					<td nowrap>
						<input type=hidden name=itemid" . $j . " value=\"$smslog_id\">
						<input type=checkbox name=checkid" . $j . ">
					</td>
				</tr>";
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>" . $queue_home_link;
		
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
				$fields = "B.username, A.p_gateway, A.p_smsc, A.p_datetime, A.p_dst, A.p_msg, A.p_footer, A.p_status, A.queue_code";
				$conditions = array(
					'A.flag_deleted' => 0,
				);
				if ($queue_code = trim($_REQUEST['queue_code'])) {
					$conditions['A.queue_code'] = $queue_code;
				}
				$keywords = $search['dba_keywords'];
				
				// fixme anton - will solve this later, for now maxed to 50k
				$extras = array(
					'ORDER BY' => "A.smslog_id DESC",
					'LIMIT' => 50000,
				);

				$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
				$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);

				if (!(count($list) > 0)) {
					$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
					$_SESSION['dialog']['info'][] = _('Nothing to export');
					header("Location: " . _u($ref));
					exit();
				}

				$data[0] = array(
					_('User'),
					_('Gateway'),
					_('SMSC'),
					_('Time'),
					_('To'),
					_('Message'),
					_('Status'),
					_('Queue'),
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['username'],
						$list[$i]['p_gateway'],
						$list[$i]['p_smsc'],
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'] . $list[$i]['p_footer'],
						$list[$i]['p_status'],
						$list[$i]['queue_code'],
					);
				}
				$content = core_csv_format($data);
				if ($queue_code) {
					$fn = 'all_outgoing-' . $core_config['datetime']['now_stamp'] . '-' . $queue_code . '.csv';
				} else {
					$fn = 'all_outgoing-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => time(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							'smslog_id' => $itemid 
						);
						if ($queue_code = trim($_REQUEST['queue_code'])) {
							$conditions['queue_code'] = $queue_code;
						}
						dba_update(_DB_PREF_ . '_tblSMSOutgoing', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected outgoing message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
