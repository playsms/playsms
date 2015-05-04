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

@set_time_limit(0);

switch (_OP_) {
	case "user_outgoing":
		$search_category = array(
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer' 
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$queue_label = "<h4>" . sprintf(_('List of queue %s'), $queue_code) . "</h4>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
			$search = themes_search($search_category, $base_url);
			$conditions = array(
				'A.queue_code' => $queue_code,
				'A.uid' => $user_config['uid'],
				'A.flag_deleted' => 0 
			);
			$keywords = $search['dba_keywords'];
			$table = _DB_PREF_ . '_tblSMSOutgoing';
			$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON B.flag_deleted='0' AND A.uid=B.uid";
			$count = dba_count($table . ' AS A', $conditions, $keywords, '', $join);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
				'ORDER BY' => 'A.smslog_id DESC',
				'LIMIT' => $nav['limit'],
				'OFFSET' => $nav['offset'] 
			);
			$list = dba_search($table . ' AS A', 'A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, A.uid, A.queue_code', $conditions, $keywords, $extras, $join);
		} else {
			$search = themes_search($search_category, $base_url);
			$conditions = array(
				'A.uid' => $user_config['uid'],
				'A.flag_deleted' => 0 
			);
			$keywords = $search['dba_keywords'];
			$table = _DB_PREF_ . '_tblSMSOutgoing';
			$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON B.flag_deleted='0' AND A.uid=B.uid";
			$list = dba_search($table . ' AS A', 'A.id', $conditions, $keywords, array(
				'GROUP BY' => 'A.queue_code' 
			), $join);
			$count = count($list);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
				'GROUP BY' => 'A.queue_code',
				'ORDER BY' => 'A.smslog_id DESC',
				'LIMIT' => $nav['limit'],
				'OFFSET' => $nav['offset'] 
			);
			$list = dba_search($table . ' AS A', 'A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, A.uid, A.queue_code, COUNT(*) AS queue_count', $conditions, $keywords, $extras, $join);
		}
		
		$content = "
			<h2>" . _('My sent messages') . "</h2>
			" . $queue_label . "
			<p>" . $search['form'] . "</p>
			<form id=fm_user_outgoing name=fm_user_outgoing action=\"index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&queue_code=" . $queue_code . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=export&queue_code=' . $queue_code) . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_user_outgoing');\">" . $icon_config['delete'] . "</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=20%>" . _('To') . "</th>
				<th width=75%>" . _('Message') . "</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_user_outgoing)></th>
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$smslog_id = $list[$j]['smslog_id'];
			$p_dst = $list[$j]['p_dst'];
			$current_p_dst = report_resolve_sender($user_config['uid'], $p_dst);
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
			$p_rate = ($billing['rate'] ? $billing['rate'] : '0.0');
			$p_charge = ($billing['charge'] ? $billing['charge'] : '0.0');
			
			// if send SMS failed then display charge as 0
			if ($list[$j]['p_status'] == 2) {
				$p_charge = '0.0';
			}
			
			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _sendsms($p_dst, $msg, '', $icon_config['resend']);
				$forward = _sendsms('', $msg, '', $icon_config['forward']);
			}
			$c_message = "
				<div id=\"msg_label\">" . $p_datetime . "&nbsp;" . _('count') . ":" . $p_count . "&nbsp;" . _('rate') . ":" . $p_rate . "&nbsp;" . _('cost') . ":" . $p_charge . "&nbsp;" . $p_status . "</div>
				<div id=\"user_outgoing_msg\">" . $p_msg . "</div>
				<div id=\"msg_option\">" . $resend . "&nbsp" . $forward . "</div>";
			$i--;
			$content .= "
				<tr>
					<td><div>" . $current_p_dst . "</div><div>" . $queue_view_link . "</div></td>
					<td>$c_message</td>
					<td>
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
		
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array(
					'A.uid' => $user_config['uid'],
					'A.flag_deleted' => 0 
				);
				if ($queue_code = trim($_REQUEST['queue_code'])) {
					$conditions['A.queue_code'] = $queue_code;
				}
				$table = _DB_PREF_ . '_tblSMSOutgoing';
				$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON B.flag_deleted='0' AND A.uid=B.uid";
				$list = dba_search($table . ' AS A', 'A.p_datetime, A.p_dst, A.p_msg, A.p_footer, A.p_status', $conditions, $search['dba_keywords'], '', $join);
				$data[0] = array(
					_('Time'),
					_('To'),
					_('Message'),
					_('Status') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'] . $list[$i]['p_footer'],
						$list[$i]['p_status'] 
					);
				}
				$content = core_csv_format($data);
				if ($queue_code) {
					$fn = 'user_outgoing-' . $user_config['username'] . '-' . $core_config['datetime']['now_stamp'] . '-' . $queue_code . '.csv';
				} else {
					$fn = 'user_outgoing-' . $user_config['username'] . '-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => mktime(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							'uid' => $user_config['uid'],
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
