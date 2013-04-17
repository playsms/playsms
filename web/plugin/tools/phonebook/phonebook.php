<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "phonebook_list":
		$search_category = array(_('Name') => 'A.name', _('Mobile') => 'mobile', _('Email') => 'email', _('Group name') => 'B.name', _('Group code') => 'code');
		$base_url = 'index.php?app=menu&inc=tools_phonebook&op=phonebook_list';
		$search = themes_search($search_category, $base_url);
		$fields = array('B.uid' => $core_config['user']['uid']);
		$keywords = $search['dba_keywords'];
		$join = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group AS B ON A.gpid=B.id';
		$count = dba_count(_DB_PREF_.'_toolsPhonebook AS A', $fields, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'A.name DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $keywords, $extras, $join);

		$actions_box = "
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td width=100% align=left>".$nav['form']."</td>
				<td>&nbsp;</td>
				<td><input type=submit name=go value=\""._('Export as CSV')."\" class=button /></td>
				<td><input type=submit name=go value=\""._('Delete selection')."\" class=button /></td>
			</tr></tbody>
			</table>";

		$content = "
			<h2>"._('Phonebook')."</h2>
			<p>".$search['form']."</p>
			<form name=\"fm_inbox\" action=\"index.php?app=menu&inc=phonebook_list&op=actions\" method=post onSubmit=\"return SureConfirm()\">
			".$actions_box."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=30%>"._('Name')."</th>
				<th align=center width=20%>"._('Mobile')."</th>
				<th align=center width=20%>"._('Email')."</th>
				<th align=center width=20%>"._('Group name')."</th>
				<th align=center width=10%>"._('Group code')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_inbox)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$name = $list[$j]['name'];
			$mobile = $list[$j]['mobile'];
			$email = $list[$j]['email'];
			$group_name = $list[$j]['group_name'];
			$group_code = strtoupper($list[$j]['code']);
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$name</td>
					<td valign=top class=$td_class align=center>$mobile</td>
					<td valign=top class=$td_class align=center>$email</td>
					<td valign=top class=$td_class align=left>$group_name</td>
					<td valign=top class=$td_class align=left>$group_code</td>
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
			</form>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case _('Export as CSV'):
				$fields = array('in_hidden' => 0);
				$join = 'INNER JOIN '._DB_PREF_.'_tblUser AS B ON in_uid=B.uid';
				$list = dba_search(_DB_PREF_.'_tblUserInbox', $fields, $search['dba_keywords'], '', $join);
				$data[0] = array(_('User'), _('Time'), _('From'), _('Message'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['username'],
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_msg']);
				}
				$content = csv_format($data);
				$fn = 'phonebook-'.$core_config['datetime']['now_stamp'].'.csv';
				download($content, $fn, 'text/csv');
				break;
			case _('Delete selection'):
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'in_hidden' => '1');
						dba_update(_DB_PREF_.'_tblUserInbox', $up, array('in_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
				header("Location: ".$ref);
		}
		break;
}

?>