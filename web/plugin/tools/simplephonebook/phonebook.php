<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

$item_count = $_POST['item_count'];
for ($i=1;$i<=$item_count;$i++)
{
	${"chkid".$i} = $_POST['chkid'.$i];
	${"pid".$i} = $_POST['pid'.$i];
	${"pdesc".$i} = $_POST['pdesc'.$i];
	${"pnum".$i} = $_POST['pnum'.$i];
	${"pemail".$i} = $_POST['pemail'.$i];
}

switch ($op)
{
	case "edit":
		$op_content = "
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
	    <form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook\" method=post>
	    <input type=hidden name=op value=edit_yes>
	    <tr>
		<td class=box_title width=4>&nbsp;*&nbsp;</td>
		<td class=box_title width='33%'>"._('Edited name')."</td>
		<td class=box_title width='33%'>"._('Edited number')."</td>
		<td class=box_title width='33%'>"._('Edited email')."</td>
	    </tr>
	";
		$j=0;
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_chkbox = ${"chkid".$i};
			if (($c_pid = ${"pid".$i}) && ($c_chkbox == "on"))
			{
				$j++;
				$c_pnum = pid2pnum($c_pid);
				$c_pdesc = phonebook_number2name($c_pnum);
				$c_pemail = pnum2pemail($c_pnum);
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$op_content .= "
		    <input type=hidden name=pid".$j." value=\"$c_pid\">
		    <tr>
			<td class=$td_class width=4>&nbsp;$j.&nbsp;</td>
			<td class=$td_class><input type=text size=30 maxlength=100 name=\"pdesc".$j."\" value=\"$c_pdesc\"></td>
			<td class=$td_class><input type=text size=30 maxlength=100 name=\"pnum".$j."\" value=\"$c_pnum\"></td>
			<td class=$td_class><input type=text size=30 maxlength=100 name=\"pemail".$j."\" value=\"$c_pemail\"></td>
		    </tr>
		";
			}
		}
		$item_count = $j;
		$op_content .= "
	    </table>
	    <p><input type=submit class=button value=\""._('Save')."\">
	    <input type=hidden name=item_count value=\"$item_count\">
	    </form>
	";
		break;
	case "edit_yes":
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_pid = ${"pid".$i};
			$c_pdesc = ${"pdesc".$i};
			$c_pnum = ${"pnum".$i};
			$c_pemail = ${"pemail".$i};
			if ($c_pid && $c_pdesc && $c_pnum)
			{
				$db_query = "UPDATE "._DB_PREF_."_toolsSimplephonebook SET c_timestamp='".mktime()."',p_desc='$c_pdesc',p_num='$c_pnum',p_email='$c_pemail' WHERE pid='$c_pid'";
				$db_result = @dba_affected_rows($db_query);
			}
		}
		header("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list");
		die();
		break;
	case "copy":
		$op_content = "
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
	    <form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook\" method=post>
	    <input type=hidden name=op value=copy_yes>
	    <tr>
		<td class=box_title width=4>&nbsp;*&nbsp;</td>
		<td class=box_title>"._('Copied name')."</td>
		<td class=box_title>"._('Copied number')."</td>
		<td class=box_title>"._('Copied email')."</td>
		
	    </tr>
	";
		$j=0;
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_chkbox = ${"chkid".$i};
			if (($c_pid = ${"pid".$i}) && ($c_chkbox == "on"))
			{
				$j++;
				$c_pnum = pid2pnum($c_pid);
				$c_pdesc = phonebook_number2name($c_pnum);
				$c_pemail = pnum2pemail($c_pnum);
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$op_content .= "
		    <input type=hidden name=pid".$j." value=\"$c_pid\">
		    <tr>
			<td class=$td_class width=4>&nbsp;$j.&nbsp;</td>
			<td class=$td_class>&nbsp;$c_pdesc</td>
			<td class=$td_class>&nbsp;$c_pnum</td>
			<td class=$td_class>&nbsp;$c_pemail</td>
		    </tr>
		";
			}
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE uid='$uid' ORDER BY gp_name";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result))
		{
			$option_group .= "<option value=\"".$db_row['gpid']."\">".$db_row['gp_name']." - "._('Code').": ".$db_row['gp_code']."</option>";
		}
		$item_count = $j;
		$op_content .= "
	    </table>
	    <p>"._('Select destination group').": <select name=gpid>$option_group</select> <input type=submit class=button value=\""._('Go')."\">
	    <input type=hidden name=item_count value=\"$item_count\">
	    </form>
	";
		break;
	case "copy_yes":
		$gpid = $_POST['gpid'];
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_pid = ${"pid".$i};
			if ($c_pid)
			{
				$c_pnum = pid2pnum($c_pid);
				$c_pdesc = phonebook_number2name($c_pnum);
				$c_pemail = pnum2pemail($c_pnum);
				$db_query = "INSERT INTO "._DB_PREF_."_toolsSimplephonebook (gpid,uid,p_num,p_desc,p_email) VALUES ('$gpid','$uid','$c_pnum','$c_pdesc','$c_pemail')";
				$db_result = @dba_insert_id($db_query);
			}
		}
		header("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list");
		die();
		break;
	case "move":
		$op_content = "
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
	    <form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook\" method=post>
	    <input type=hidden name=op value=move_yes>
	    <tr>
		<td class=box_title width=4>&nbsp;*&nbsp;</td>
		<td class=box_title>"._('Moved name')."</td>
		<td class=box_title>"._('Moved number')."</td>
		<td class=box_title>"._('Moved email')."</td>
		
	    </tr>
	";
		$j=0;
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_chkbox = ${"chkid".$i};
			if (($c_pid = ${"pid".$i}) && ($c_chkbox == "on"))
			{
				$j++;
				$c_pnum = pid2pnum($c_pid);
				$c_pdesc = phonebook_number2name($c_pnum);
				$c_pemail = pnum2pemail($c_pnum);
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$op_content .= "
		    <input type=hidden name=pid".$j." value=\"$c_pid\">
		    <tr>
			<td class=$td_class width=4>&nbsp;$j.&nbsp;</td>
			<td class=$td_class>&nbsp;$c_pdesc</td>
			<td class=$td_class>&nbsp;$c_pnum</td>
			<td class=$td_class>&nbsp;$c_pemail</td>
		    </tr>
		";
			}
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE uid='$uid' ORDER BY gp_name";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result))
		{
			$option_group .= "<option value=\"".$db_row['gpid']."\">".$db_row['gp_name']." - Code: ".$db_row['gp_code']."</option>";
		}
		$item_count = $j;
		$op_content .= "
	    </table>
	    <p>"._('Select destination group').": <select name=gpid>$option_group</select> <input type=submit class=button value=\""._('Go')."\">
	    <input type=hidden name=item_count value=\"$item_count\">
	    </form>
	";
		break;
	case "move_yes":
		$gpid = $_POST['gpid'];
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_pid = ${"pid".$i};
			if ($c_pid)
			{
				$db_query = "UPDATE "._DB_PREF_."_toolsSimplephonebook SET c_timestamp='".mktime()."',gpid='$gpid' WHERE pid='$c_pid'";
				$db_result = @dba_affected_rows($db_query);
			}
		}
		header("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list");
		die();
		break;
	case "delete":
		$op_content = "
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
	    <form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook\" method=post>
	    <input type=hidden name=op value=delete_yes>
	    <tr>
		<td class=box_title width=4>&nbsp;*&nbsp;</td>
		<td class=box_title>"._('Deleted name')."</td>
		<td class=box_title>"._('Deleted number')."</td>
		<td class=box_title>"._('Deleted email')."</td>
		
	    </tr>
	";
		$j=0;
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_chkbox = ${"chkid".$i};
			if (($c_pid = ${"pid".$i}) && ($c_chkbox == "on"))
			{
				$j++;
				$c_pnum = pid2pnum($c_pid);
				$c_pdesc = phonebook_number2name($c_pnum);
				$c_pemail = pnum2pemail($c_pnum);
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$op_content .= "
		    <input type=hidden name=pid".$j." value=\"$c_pid\">
		    <tr>
			<td class=$td_class width=4>&nbsp;$j.&nbsp;</td>
			<td class=$td_class>&nbsp;$c_pdesc</td>
			<td class=$td_class>&nbsp;$c_pnum</td>
			<td class=$td_class>&nbsp;$c_pemail</td>
		    </tr>
		";
			}
		}
		$item_count = $j;
		$op_content .= "
	    </table>
	    <p><input type=submit class=button value=\""._('Delete')."\">
	    <input type=hidden name=item_count value=\"$item_count\">
	    </form>
	";
		break;
	case "delete_yes":
		$gpid = $_POST['gpid'];
		for ($i=1;$i<=$item_count;$i++)
		{
			$c_pid = ${"pid".$i};
			if ($c_pid)
			{
				$db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook WHERE pid='$c_pid'";
				$db_result = @dba_affected_rows($db_query);
			}
		}
		header("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list");
		die();
		break;
	case "share_this_group":
		$gpid = $_REQUEST['gpid'];
		if ($gpid)
		{
			$db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook_group_public WHERE gpid='$gpid' AND uid='$uid'";
			$db_result = @dba_query($db_query);
			$gpname = gpid2gpname($gpid);
			$error_string = _('Fail to publish')." ("._('group').": $gpname)";
			$db_query = "INSERT INTO "._DB_PREF_."_toolsSimplephonebook_group_public (gpid,uid) VALUES ('$gpid','$uid')";
			$db_result = @dba_insert_id($db_query);
			if ($db_result > 0)
			{
				$error_string = _('Group has been published')." ("._('group').": $gpname)";
			}
		}
		header ("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list&err=".urlencode($error_string));
		die();
		break;
	case "hide_from_public":
		$pp = $_REQUEST['pp'];
		$gpid = $_REQUEST['gpid'];
		if ($gpid)
		{
			$gpname = gpid2gpname($gpid);
			$error_string = _('Fail to unpublish')." ("._('group').": $gpname)";
			$db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook_group_public WHERE gpid='$gpid' AND uid='$uid'";
			$db_result = @dba_affected_rows($db_query);
			if ($db_result > 0)
			{
				$error_string = _('Group has been unpublished')." ("._('group').": $gpname)";
			}
		}
		header ("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list&err=".urlencode($error_string));
		die();
		break;
}

$content = "
    <h2>"._('Phonebook')." ".ucfirst($op)."</h2>
    <p>
";
if ($err)
{
	$content .= "<div class=error_string>$err</div>";
}
$content .= "
$op_content
";

echo $content;

?>