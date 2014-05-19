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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

switch (_OP_) {
	case "user_outgoing":
		$search_category = array(_('Time') => 'p_datetime', _('To') => 'p_dst', _('Message') => 'p_msg', _('Footer') => 'p_footer');
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$search = themes_search($search_category, $base_url);
		$conditions = array('uid' => $user_config['uid'], 'flag_deleted' => 0);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_tblSMSOutgoing', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'smslog_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblSMSOutgoing', '*', $conditions, $keywords, $extras);

		$content = "
			<h2>"._('Outgoing messages')."</h2>
			<p>".$search['form']."</p>
			<form id=fm_outgoing name=fm_outgoing action=\"index.php?app=main&inc=feature_report&route=user_outgoing&op=actions\" method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\""._u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=export')."\">".$icon_config['export']."</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('"._('Are you sure you want to delete these items ?')."', 'fm_outgoing');\">".$icon_config['delete']."</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=20%>"._('To')."</th>
				<th width=75%>"._('Message')."</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></th>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j=0;
		for ($j=0;$j<count($list);$j++) {
			$list[$j] = core_display_data($list[$j]);
			$smslog_id = $list[$j]['smslog_id'];
			$p_dst = $list[$j]['p_dst'];
			$p_desc = phonebook_number2name($p_dst);
			$current_p_dst = $p_dst;
			if ($p_desc) {
				$current_p_dst = "$p_dst<br />$p_desc";
			}
			$p_sms_type = $list[$j]['p_sms_type'];
			if (($p_footer = $list[$j]['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg.' '.$p_footer;
			}
			$p_datetime = core_display_datetime($list[$j]['p_datetime']);
			$p_update = $list[$j]['p_update'];
			$p_status = $list[$j]['p_status'];
			$p_gpid = $list[$j]['p_gpid'];
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1") {
				$p_status = "<span class=status_sent />";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed />";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered />";
			} else {
				$p_status = "<span class=status_pending />";
			}
			$p_status = strtolower($p_status);

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ( $billing['count'] ? $billing['count'] : '0' );
			$p_rate = ( $billing['rate'] ? $billing['rate'] : '0.0' );
			$p_charge = ( $billing['charge'] ? $billing['charge'] : '0.0' );

			// if send SMS failed then display charge as 0
			if ($list[$j]['p_status'] == 2) {
				$p_charge = '0.0';
			}

			if ($p_gpid) {
				$p_gpcode = strtoupper(phonebook_groupid2code($p_gpid));
			} else {
				$p_gpcode = "&nbsp;";
			}
			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _a('index.php?app=main&inc=core_sendsms&op=sendsms&do=reply&message='.urlencode($msg).'&to='.urlencode($p_dst), $icon_config['resend']);
				$forward = _a('index.php?app=main&inc=core_sendsms&op=sendsms&do=forward&message='.urlencode($msg), $icon_config['forward']);
			}
			$c_message = "
				<div id=\"user_outgoing_msg\">".$p_msg."</div>
				<div id=\"msg_price\">"._('count').":".$p_count."&nbsp;"._('rate').":".$p_rate."&nbsp;"._('cost').":".$p_charge."</div>
				<div id=\"msg_label\">".$p_datetime."&nbsp;".$p_status."</div>
				<div id=\"msg_option\">".$resend."&nbsp".$forward."</div>";
			$i--;
			$content .= "
				<tr>
					<td>$current_p_dst</td>
					<td>$c_message</td>
					<td>
						<input type=hidden name=itemid".$j." value=\"$smslog_id\">
						<input type=checkbox name=checkid".$j.">
					</td>		  
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>".$nav['form']."</div>
			</form>";

		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array('uid' => $user_config['uid'], 'flag_deleted' => 0);
				$list = dba_search(_DB_PREF_.'_tblSMSOutgoing', '*', $conditions, $search['dba_keywords']);
				$data[0] = array(_('User'), _('Time'), _('To'), _('Message'), _('Status'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						user_uid2username($list[$i]['uid']),
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'],
						$list[$i]['p_status']);
				}
				$content = core_csv_format($data);
				$fn = 'user_outgoing-'.$core_config['datetime']['now_stamp'].'.csv';
				core_download($content, $fn, 'text/csv');
				break;
			case 'delete':
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
						dba_update(_DB_PREF_.'_tblSMSOutgoing', $up, array('uid' => $user_config['uid'], 'smslog_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected outgoing message has been deleted');
				header("Location: "._u($ref));
		}
		break;
}
