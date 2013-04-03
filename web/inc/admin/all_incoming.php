<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "all_incoming":
		$search_var = array(
			'name' => 'all_incoming',
			'url' => 'index.php?app=menu&inc=all_incoming&op=all_incoming',
		);
		$search = themes_search($search_var);
		$fields = array('flag_deleted' => 0);
		if ($kw = $search['keyword']) {
			$keywords = array(
			    'in_message' => '%'.$kw.'%',
			    'in_sender' => '%'.$kw.'%',
			    'in_datetime' => '%'.$kw.'%',
			    'in_feature' => '%'.$kw.'%',
			    'in_keyword' => '%'.$kw.'%'
			    );
		}
		$count = data_count(_DB_PREF_.'_tblSMSIncoming', $fields, $keywords);
		$nav = themes_nav($count, "index.php?app=menu&inc=all_incoming&op=all_incoming");
		$extras = array('ORDER BY' => 'in_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = data_search(_DB_PREF_.'_tblSMSIncoming', $fields, $keywords, $extras);
		
		$content = "
			<h2>"._('All incoming SMS')."</h2>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_incoming\" action=\"index.php?app=menu&inc=all_incoming&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=10%>"._('User')."</th>
				<th align=center width=20%>"._('Time')."</th>
				<th align=center width=10%>"._('From')."</th>
				<th align=center width=10%>"._('Keyword')."</th>
				<th align=center width=30%>"._('Content')."</th>
				<th align=center width=10%>"._('Feature')."</th>
				<th align=center width=10%>"._('Status')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$in_message = core_display_text($list[$j]['in_message'], 25);
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_username = uid2username($list[$j]['in_uid']);
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
					<td valign=top class=$td_class align=center>$in_username</td>
					<td valign=top class=$td_class align=center>$in_datetime</td>
					<td valign=top class=$td_class align=center>$current_sender</td>
					<td valign=top class=$td_class align=center>$in_keyword</td>
					<td valign=top class=$td_class align=left>$in_message</td>
					<td valign=top class=$td_class align=center>$in_feature</td>
					<td valign=top class=$td_class align=center>$in_status</td>
					<td class=$td_class width=4>
						<input type=hidden name=inid".$j." value=\"$in_id\">
						<input type=checkbox name=chkid".$j.">
					</td>
				</tr>";
		}
		$item_count = $j;

		$content .= "
			</tbody>
			</table>
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td width=100% colspan=2 align=right>
					<input type=hidden name=item_count value=\"$item_count\">
					<input type=hidden name=ref value=\"".$_SERVER['REQUEST_URI']."\">
					<input type=submit value=\""._('Delete selection')."\" class=button />
				</td>
			</tr></tbody>
			</table>
			</form>
			<p>".$nav['form']."</p>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "act_del":
		$item_count = $_POST['item_count'];
		$ref = $_POST['ref'];
		for ($i=0;$i<$item_count;$i++) {
			$chkid = $_POST['chkid'.$i];
			$inid = $_POST['inid'.$i];
			if(($chkid=="on") && $inid) {
				$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
				data_update(_DB_PREF_.'_tblSMSIncoming', $up, array('in_id' => $inid));
			}
		}
		$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
		header("Location: ".$ref);
		exit();
		break;
}

?>