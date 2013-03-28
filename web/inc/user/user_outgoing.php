<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

$slid = $_REQUEST['slid'];

switch ($op) {
	case "user_outgoing":
		$search_var = array(
			'name' => 'user_outgoing',
			'url' => 'index.php?app=menu&inc=user_outgoing&op=user_outgoing',
		);
		$search = themes_search($search_var);
		$fields = array('uid' => $uid, 'flag_deleted' => 0);
		if ($kw = $search['keyword']) {
			$keywords = array(
			    'p_msg' => '%'.$kw.'%',
			    'p_dst' => '%'.$kw.'%',
			    'p_datetime' => '%'.$kw.'%',
			    'p_gateway' => '%'.$kw.'%',
			    'p_footer' => '%'.$kw.'%'
			    );
		}
		$count = data_count(_DB_PREF_.'_tblSMSOutgoing', $fields, $keywords);
		$nav = themes_nav($count, "index.php?app=menu&inc=user_outgoing&op=user_outgoing");
		$extras = array('ORDER BY' => 'smslog_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = data_search(_DB_PREF_.'_tblSMSOutgoing', $fields, $keywords, $extras);

		$content = "
			<h2>"._('Outgoing SMS')."</h2>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_outgoing\" action=\"index.php?app=menu&inc=user_outgoing&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
			<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=20%>"._('Time')."</th>
				<th align=center width=10%>"._('To')."</th>
				<th align=center width=40%>"._('Message')."</th>
				<th align=center width=10%>"._('Status')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j=0;
		for ($j=0;$j<count($list);$j++) {
			$p_msg = core_display_text($list[$j]['p_msg'], 25);
			$list[$j] = core_display_data($list[$j]);
			$current_slid = $list[$j]['smslog_id'];
			$p_dst = $list[$j]['p_dst'];
			$p_desc = phonebook_number2name($p_dst);
			$current_p_dst = $p_dst;
			if ($p_desc) {
				$current_p_dst = "$p_dst<br>($p_desc)";
			}
			$hide_p_dst = $p_dst;
			if ($p_desc) {
				$hide_p_dst = "$p_dst ($p_desc)";
			}
			$p_sms_type = $list[$j]['p_sms_type'];
			$hide_p_dst = str_replace("\'","",$hide_p_dst);
			$hide_p_dst = str_replace("\"","",$hide_p_dst);
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
				$p_status = "<p><font color=green>"._('Sent')."</font></p>";
			} else if ($p_status == "2") {
				$p_status = "<p><font color=red>"._('Failed')."</font></p>";
			} else if ($p_status == "3") {
				$p_status = "<p><font color=green>"._('Delivered')."</font></p>";
			} else {
				$p_status = "<p><font color=orange>"._('Pending')."</font></p>";
			}
			if ($p_gpid) {
				$p_gpcode = strtoupper(phonebook_groupid2code($p_gpid));
			} else {
				$p_gpcode = "&nbsp;";
			}
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$p_datetime</td>
					<td valign=top class=$td_class align=center>$current_p_dst</td>
					<td valign=top class=$td_class align=left>$p_msg</td>
					<td valign=top class=$td_class align=center>$p_status</td>
					<td class=$td_class width=4>
						<input type=hidden name=slid".$j." value=\"$current_slid\">
						<input type=checkbox name=chkid".$j.">
					</td>		  
				</tr>";
		}
		$item_count = $j;

		$content .= "
			</tbody>
			</table>
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr>
				<td width=100% colspan=2 align=right>
					<input type=hidden name=item_count value=\"$item_count\">
					<input type=hidden name=ref value=\"".$_SERVER['REQUEST_URI']."\">
					<input type=submit value=\""._('Delete selection')."\" class=button />
				</td>
			</tr>
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
			$slid = $_POST['slid'.$i];
			if(($chkid=="on") && $slid) {
				$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
				data_update(_DB_PREF_.'_tblSMSOutgoing', $up, array('uid' => $uid, 'smslog_id' => $slid));
			}
		}
		$_SESSION['error_string'] = _('Selected outgoing SMS has been deleted');
		header("Location: ".$ref);
		exit();
		break;
}

?>