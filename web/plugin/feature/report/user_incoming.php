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
;

switch (_OP_) {
	case "user_incoming":
		$search_category = array(
			_('Time') => 'in_datetime',
			_('From') => 'in_sender',
			_('Keyword') => 'in_keyword',
			_('Content') => 'in_message',
			_('Feature') => 'in_feature' 
		);
		$base_url = 'index.php?app=main&inc=feature_report&route=user_incoming&op=user_incoming';
		$search = themes_search($search_category, $base_url);
		$conditions = array(
			'in_uid' => $user_config['uid'],
			'flag_deleted' => 0,
			'in_status' => 1 
		);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblSMSIncoming', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'AND in_feature' => '!= ""',
			'ORDER BY' => 'in_id DESC',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_tblSMSIncoming', 'in_id, in_sender, in_keyword, in_datetime, in_feature, in_message', $conditions, $keywords, $extras);
		
		$content = "
			<h2>" . _('My feature messages') . "</h2>
			<p>" . $search['form'] . "</p>
			<form id=fm_incoming name=fm_incoming action=\"index.php?app=main&inc=feature_report&route=user_incoming&op=actions\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_incoming&op=actions&go=export') . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_incoming');\">" . $icon_config['delete'] . "</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=20%>" . _('From') . "</th>
				<th width=20%>" . _('Keyword') . "</th>
				<th width=55%>" . _('Content') . "</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></th>
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$current_sender = report_resolve_sender($user_config['uid'], $in_sender);
			$in_keyword = $list[$j]['in_keyword'];
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			$in_feature = $list[$j]['in_feature'];
			// $in_status = ($list[$j]['in_status'] == 1 ? '<span class=status_handled />' : '<span class=status_unhandled />');
			// $in_status = strtolower($in_status);
			$c_feature = '';
			if ($in_feature) {
				$c_feature = "<br />" . $in_feature;
			}
			$msg = trim($list[$j]['in_message']);
			$in_message = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _sendsms($in_sender, $msg);
				$forward = _sendsms('', $msg, '', $icon_config['forward']);
			}
			$c_message = "<div id=\"user_incoming_msg\">" . $in_message . "</div><div id=\"msg_label\">" . $in_datetime . "&nbsp;" . $in_status . "</div><div id=\"msg_option\">" . $reply . "&nbsp" . $forward . "</div>";
			$i--;
			$content .= "
				<tr>
					<td>$current_sender</td>
					<td>" . $in_keyword . $c_feature . "</td>
					<td>$c_message</td>
					<td>
						<input type=hidden name=itemid" . $j . " value=\"$in_id\">
						<input type=checkbox name=checkid" . $j . ">
					</td>
				</tr>";
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>";
		
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
					'in_uid' => $user_config['uid'],
					'flag_deleted' => 0,
					'in_status' => 1 
				);
				$extras = array(
					'AND in_keyword' => '!= ""' 
				);
				$list = dba_search(_DB_PREF_ . '_tblSMSIncoming', 'in_sender, in_keyword, in_datetime, in_feature, in_message', $conditions, $search['dba_keywords'], $extras);
				$data[0] = array(
					_('Time'),
					_('From'),
					_('Keyword'),
					_('Content'),
					_('Feature') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_keyword'],
						$list[$i]['in_message'],
						$list[$i]['in_feature'] 
					);
				}
				$content = core_csv_format($data);
				$fn = 'user_incoming-' . $user_config['username'] . '-' . $core_config['datetime']['now_stamp'] . '.csv';
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
						dba_update(_DB_PREF_ . '_tblSMSIncoming', $up, array(
							'in_uid' => $user_config['uid'],
							'in_id' => $itemid 
						));
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected incoming message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
