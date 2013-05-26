<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "user_incoming":
		$search_category = array(_('Time') => 'in_datetime', _('From') => 'in_sender', _('Keyword') => 'in_keyword', _('Content') => 'in_message', _('Feature') => 'in_feature');
		$base_url = 'index.php?app=menu&inc=user_incoming&op=user_incoming';
		$search = themes_search($search_category, $base_url);
		$conditions = array('in_uid' => $uid, 'flag_deleted' => 0);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_tblSMSIncoming', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'in_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblSMSIncoming', '*', $conditions, $keywords, $extras);
		
		$actions_box = "
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td><input type=submit name=go value=\""._('Export')."\" class=button /></td>
				<td width=100%>&nbsp;</td>
				<td><input type=submit name=go value=\""._('Delete selection')."\" class=button /></td>
			</tr></tbody>
			</table>";

		$content = "
			<h2>"._('Incoming SMS')."</h2>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_incoming\" action=\"index.php?app=menu&inc=user_incoming&op=actions\" method=post onSubmit=\"return SureConfirm()\">
			".$actions_box."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=20%>"._('Time')."</th>
				<th align=center width=10%>"._('From')."</th>
				<th align=center width=10%>"._('Keyword')."</th>
				<th align=center width=40%>"._('Content')."</th>
				<th align=center width=10%>"._('Feature')."</th>
				<th align=center width=10%>"._('Status')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$in_message = core_display_text($list[$j]['in_message']);
			$reply = '';
			$forward = '';
			if (($msg=$list[$j]['in_message']) && $in_sender) {
				$reply = '<br />'._a('index.php?app=menu&inc=send_sms&op=sendsmstopv&do=reply&message='.urlencode($msg).'&to='.urlencode($in_sender), _('reply'));
				$forward = _a('index.php?app=menu&inc=send_sms&op=sendsmstopv&do=forward&message='.urlencode($msg), _('forward'));
			}
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$p_desc = phonebook_number2name($in_sender);
			$current_sender = $in_sender;
			if ($p_desc) {
				$current_sender = "$in_sender<br>($p_desc)";
			}
			$in_keyword = $list[$j]['in_keyword'];
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			$in_feature = $list[$j]['in_feature'];
			$in_status = ( $list[$j]['in_status'] == 1 ? '<p><font color=green>'._('handled').'</font></p>' : '<p><font color=red>'._('unhandled').'</font></p>' );
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$in_datetime</td>
					<td valign=top class=$td_class align=center>$current_sender</td>
					<td valign=top class=$td_class align=center>$in_keyword</td>
					<td valign=top class=$td_class align=left><p id=\"user_incoming_msg\">$in_message $reply $forward</p></td>
					<td valign=top class=$td_class align=center>$in_feature</td>
					<td valign=top class=$td_class align=center>$in_status</td>
					<td class=$td_class width=4>
						<input type=hidden name=itemid".$j." value=\"$in_id\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			".$actions_box."
			<p>".$nav['form']."</p>
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
				$conditions = array('in_uid' => $uid, 'flag_deleted' => 0);
				$list = dba_search(_DB_PREF_.'_tblSMSIncoming', '*', $conditions, $search['dba_keywords']);
				$data[0] = array(_('User'), _('Time'), _('From'), _('Keyword'), _('Content'), _('Feature'), _('Status'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						uid2username($list[$i]['in_uid']),
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_keyword'],
						$list[$i]['in_message'],
						$list[$i]['in_feature'],
						( $list[$i]['in_status'] == 1 ? _('handled') : _('unhandled')));
				}
				$content = csv_format($data);
				$fn = 'user_incoming-'.$core_config['datetime']['now_stamp'].'.csv';
				download($content, $fn, 'text/csv');
				break;
			case _('Delete selection'):
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
						dba_update(_DB_PREF_.'_tblSMSIncoming', $up, array('in_uid' => $uid, 'in_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
				header("Location: ".$ref);
		}
		break;
}

?>