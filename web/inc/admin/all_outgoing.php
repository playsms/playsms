<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "all_outgoing":
		$search_category = array(_('User') => 'username', _('Gateway') => 'p_gateway', _('Time') => 'p_datetime', _('To') => 'p_dst', _('Message') => 'p_msg', _('Footer') => 'p_footer');
		$base_url = 'index.php?app=menu&inc=all_outgoing&op=all_outgoing';
		$search = themes_search($search_category, $base_url);
		$conditions = array('flag_deleted' => 0);
		$keywords = $search['dba_keywords'];
		$table = _DB_PREF_.'_tblSMSOutgoing';
		$join = 'INNER JOIN '._DB_PREF_.'_tblUser AS B ON A.uid=B.uid';
		$count = dba_count($table.' AS A', $conditions, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'smslog_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search($table.' AS A', '*', $conditions, $keywords, $extras, $join);

		$actions_box = "
			<div id=actions_box>
			<div id=actions_box_left><input type=submit name=go value=\""._('Export')."\" class=button /></div>
			<div id=actions_box_center>".$nav['form']."</div>
			<div id=actions_box_right><input type=submit name=go value=\""._('Delete')."\" class=button /></div>
			</div>";

		$content = "
			<h2>"._('All outgoing SMS')."</h2>
			<p>".$search['form']."</p>
			<form name=\"fm_outgoing\" action=\"index.php?app=menu&inc=all_outgoing&op=actions\" method=post onSubmit=\"return SureConfirm()\">
			".$actions_box."
			<table class=ps_table class=\"sortable\">
			<thead>
			<tr>
				<th width=20%>"._('User')."</th>
				<th width=15%>"._('Gateway')."</th>
				<th width=25%>"._('To')."</th>
				<th width=35%>"._('Message')."</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j=0;
		for ($j=0;$j<count($list);$j++) {
			$list[$j] = core_display_data($list[$j]);
			$p_username = $list[$j]['username'];
			$p_gateway = $list[$j]['p_gateway'];
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
			if ($p_gpid) {
				$p_gpcode = strtoupper(phonebook_groupid2code($p_gpid));
			} else {
				$p_gpcode = "&nbsp;";
			}
			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _a('index.php?app=menu&inc=send_sms&op=sendsmstopv&do=reply&message='.urlencode($msg).'&to='.urlencode($p_dst), $core_config['icon']['resend']);
				$forward = _a('index.php?app=menu&inc=send_sms&op=sendsmstopv&do=forward&message='.urlencode($msg), $core_config['icon']['forward']);
			}
			$c_message = "<div id=\"all_outgoing_msg\">".$p_msg."</div><div id=\"msg_label\">".$p_datetime."&nbsp;".$p_status."</div><div id=\"msg_option\">".$resend."&nbsp".$forward."</div>";
			$i--;
			$tr_class = ($i % 2) ? "row_odd" : "row_even";
			$content .= "
				<tr class=$tr_class>
					<td>$p_username</td>
					<td>$p_gateway</td>
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
			".$actions_box."
			</form>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case _('Export'):
				$conditions = array('flag_deleted' => 0);
				$table = _DB_PREF_.'_tblSMSOutgoing';
				$join = 'INNER JOIN '._DB_PREF_.'_tblUser AS B ON A.uid=B.uid';
				$list = dba_search($table.' AS A', '*', $conditions, $search['dba_keywords'], '', $join);
				$data[0] = array(_('User'), _('Gateway'),_('Time'), _('To'), _('Message'), _('Status'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['username'],
						$list[$i]['p_gateway'],
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'].$list[$i]['p_footer'],
						$list[$i]['p_status']);
				}
				$content = csv_format($data);
				$fn = 'all_outgoing-'.$core_config['datetime']['now_stamp'].'.csv';
				download($content, $fn, 'text/csv');
				break;
			case _('Delete'):
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
						dba_update(_DB_PREF_.'_tblSMSOutgoing', $up, array('smslog_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected outgoing SMS has been deleted');
				header("Location: ".$ref);
		}
		break;
}

?>