<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

$slid = $_REQUEST['slid'];

switch ($op)
{
	case "all_outgoing":
		if(!$page){$page = 1;}
		if(!$nav){$nav = 1;}

		$line_per_page = 50;
		$max_nav = 15;

		$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSOutgoing WHERE flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows = $db_row['count'];

		$pages = ceil($num_rows/$line_per_page);
		$nav_pages = themes_navbar($pages, $nav, $max_nav, "index.php?app=menu&inc=all_outgoing&op=all_outgoing", $page);
		$limit = ($page-1)*$line_per_page;

		$content = "
	    <h2>"._('All outgoing SMS')."</h2>
	    <p>$nav_pages</p>
	    <form name=\"fm_outgoing\" action=\"index.php?app=menu&inc=all_outgoing&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
	    <table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
        <thead>
	    <tr>
	      <th align=center width=4>*</th>
	      <th align=center width=10%>"._('User')."</th>
	      <th align=center width=20%>"._('Time')."</th>
	      <th align=center width=10%>"._('Gateway')."</th>
	      <th align=center width=10%>"._('To')."</th>
	      <th align=center width=40%>"._('Message')."</th>
	      <th align=center width=10%>"._('Status')."</th>
	      <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td>
	    </tr>
        </thead>
        <tbody>
	";
		$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE flag_deleted='0' ORDER BY smslog_id DESC LIMIT $line_per_page OFFSET $limit";
		$db_result = dba_query($db_query);
		$i = ($num_rows-($line_per_page*($page-1)))+1;
		$j=0;
		while ($db_row = dba_fetch_array($db_result))
		{
			$p_msg = core_display_text($db_row['p_msg'], 25);
			$db_row = core_display_data($db_row);
			$j++;
			$current_slid = $db_row['smslog_id'];
			$p_username = uid2username($db_row['uid']);
			$p_dst = $db_row['p_dst'];
			$p_desc = phonebook_number2name($p_dst);
			$current_p_dst = $p_dst;
			if ($p_desc)
			{
				$current_p_dst = "$p_dst<br>($p_desc)";
			}
			$hide_p_dst = $p_dst;
			if ($p_desc)
			{
				$hide_p_dst = "$p_dst ($p_desc)";
			}
			$p_sms_type = $db_row['p_sms_type'];
			$hide_p_dst = str_replace("\'","",$hide_p_dst);
			$hide_p_dst = str_replace("\"","",$hide_p_dst);
			if (($p_footer = $db_row['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash")))
			{
				$p_msg = $p_msg.' '.$p_footer;
			}
			$p_datetime = core_display_datetime($db_row['p_datetime']);
			$p_gateway = $db_row['p_gateway'];
			$p_update = $db_row['p_update'];
			$p_status = $db_row['p_status'];
			$p_gpid = $db_row['p_gpid'];
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1")
			{
				$p_status = "<p><font color=green>"._('Sent')."</font></p>";
			}
			else if ($p_status == "2")
			{
				$p_status = "<p><font color=red>"._('Failed')."</font></p>";
			}
			else if ($p_status == "3")
			{
				$p_status = "<p><font color=green>"._('Delivered')."</font></p>";
			}
			else
			{
				$p_status = "<p><font color=orange>"._('Pending')."</font></p>";
			}
			if ($p_gpid)
			{
				$p_gpcode = strtoupper(phonebook_groupid2code($p_gpid));
			}
			else
			{
				$p_gpcode = "&nbsp;";
			}
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
		<tr>
	          <td valign=top class=$td_class align=left>$i.</td>
	          <td valign=top class=$td_class align=center>$p_username</td>
	          <td valign=top class=$td_class align=center>$p_datetime</td>
	          <td valign=top class=$td_class align=center>$p_gateway</td>
	          <td valign=top class=$td_class align=center>$current_p_dst</td>
	          <td valign=top class=$td_class align=left>$p_msg</td>
	          <td valign=top class=$td_class align=center>$p_status</td>
		<td class=$td_class width=4>
		    <input type=hidden name=slid".$j." value=\"$current_slid\">
		    <input type=checkbox name=chkid".$j.">
		</td>		  
		</tr>
	    ";
		}
		$item_count = $j;
		$content .= "
	</tbody></table>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr>
	    <td width=100% colspan=2 align=right>
		<input type=hidden name=item_count value=\"$item_count\">
		<input type=submit value=\""._('Delete selection')."\" class=button />
	    </td>
	</tr>
	</table>	
	</form>
	<p>$nav_pages</p>
	";
		if ($err = $_SESSION['error_string'])
		{
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "all_outgoing_del":
		if ($slid) {
			$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',flag_deleted='1' WHERE smslog_id='$slid'";
			$db_result = dba_affected_rows($db_query);
			if ($db_result > 0) {
				$_SESSION['error_string'] = _('Selected outgoing SMS has been deleted');
			} else {
				$_SESSION['error_string'] = _('Fail to delete SMS');
			}
		}
		header("Location: index.php?app=menu&inc=all_outgoing&op=all_outgoing");
		exit();
		break;
	case "act_del":
		$item_count = $_POST['item_count'];

		for ($i=1;$i<=$item_count;$i++) {
			$chkid = $_POST['chkid'.$i];
			$slid = $_POST['slid'.$i];

			if(($chkid=="on") && $slid) {
				$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',flag_deleted='1' WHERE smslog_id='$slid'";
				$db_result = dba_affected_rows($db_query);
			}
		}
		$_SESSION['error_string'] = _('Selected outgoing SMS has been deleted');
		header("Location: index.php?app=menu&inc=all_outgoing&op=all_outgoing");
		exit();
		break;
}

?>