<?php
if(!isadmin()){forcenoaccess();};

switch ($op)
{
    case "all_inbox":
	if(!$page){$page = 1;}
	if(!$nav){$nav = 1;}
	
	$line_per_page = 50;
	$max_nav = 15;

	$db_query = "";

	$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblUserInbox WHERE in_hidden='0'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$num_rows = $db_row['count'];

	$pages = ceil($num_rows/$line_per_page);
	$nav_pages = themes_navbar($pages, $nav, $max_nav, "menu.php?inc=all_inbox&op=all_inbox", $page);
	$limit = ($page-1)*$line_per_page;    
	
	$content = "
	    <h2>"._('All Inbox')."</h2>
	    <p>$nav_pages</p>
	    <form name=\"fm_inbox\" action=\"menu.php?inc=all_inbox&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
        <thead>
	    <tr>
	      <th align=center width=4>*</th>
	      <th align=center width=20%>"._('User')."</th>
	      <th align=center width=20%>"._('Time')."</th>
	      <th align=center width=20%>"._('Sender')."</th>
	      <th align=center width=40%>"._('Message')."</th>
	      <th align=center class=\"sorttable_nosort\">"._('Action')."</td>
	      <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_inbox)></td>
	    </tr>
        </thead>
        <tbody>
	";

	$db_query = "SELECT * FROM "._DB_PREF_."_tblUserInbox WHERE in_hidden='0' ORDER BY in_id DESC LIMIT $limit,$line_per_page";
	$db_result = dba_query($db_query);
	$i = ($num_rows-($line_per_page*($page-1)))+1;
	$j = 0;
	while ($db_row = dba_fetch_array($db_result))
	{
	    $j++;
	    $in_id = $db_row['in_id'];
	    $in_username = uid2username($db_row['in_uid']);
	    $in_sender = $db_row['in_sender'];
	    $p_desc = phonebook_number2name($in_sender);
	    $current_sender = $in_sender;
	    if ($p_desc) 
	    {
		$current_sender = "$in_sender<br>($p_desc)";
	    }
	    $in_msg = $db_row['in_msg'];
	    $in_datetime = $db_row['in_datetime'];
	    $i--;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";	    
	    $content .= "
		<tr>
	          <td valign=top class=$td_class align=left>$i.</td>
	          <td valign=top class=$td_class align=center>$in_username</td>
	          <td valign=top class=$td_class align=center>$in_datetime</td>
	          <td valign=top class=$td_class align=center>$current_sender</td>
	          <td valign=top class=$td_class align=left>$in_msg</td>
	          <td valign=top class=$td_class align=center nowrap>
		    <!-- <a href=\"javascript: PopupReplySms('$current_sender', '".urlencode($in_msg)."')\">$icon_reply</a> -->
		    <!-- <a href=\"menu.php?inc=phone_add&op=add&phone=$current_sender\">$icon_phonebook</a> -->
		    <a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this SMS ?')."','menu.php?inc=all_inbox&op=all_inbox_del&inid=$in_id')\">$icon_delete</a>
		    
		  </td>
		<td class=$td_class width=4>
		    <input type=hidden name=inid".$j." value=\"$in_id\">
		    <input type=checkbox name=chkid".$j.">
		</td>		  
		</tr>
	    ";
	}
	$item_count = $j;
	$content .= "
    </tbody>
    </table>
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
	if ($err)
	{
	    echo "<font color=red>$err</font><br><br>";
	}
	echo $content;
	break;
    case "all_inbox_del":
	$error_string = _('Fail to delete incoming SMS');
	if ($in_id = $_REQUEST['inid'])
	{
	    $db_query = "UPDATE "._DB_PREF_."_tblUserInbox SET c_timestamp='".mktime()."',in_hidden='1' WHERE in_id='$in_id'";
	    $db_result = dba_affected_rows($db_query);
	    if ($db_result > 0)
	    {
		$error_string = _('Selected incoming SMS has been deleted');
	    }
	}
	header("Location: menu.php?inc=all_inbox&op=all_inbox&err=".urlencode($error_string));
	break;
    case "act_del":
	$item_count = $_POST['item_count'];
	
	for ($i=1;$i<=$item_count;$i++)
	{
	    $chkid = $_POST['chkid'.$i];
	    $inid = $_POST['inid'.$i];
	    
	    if(($chkid=="on") && $inid)
	    {
		$db_query = "UPDATE "._DB_PREF_."_tblUserInbox SET c_timestamp='".mktime()."',in_hidden='1' WHERE in_id='$inid'";
		$db_result = dba_affected_rows($db_query);
	    }
	}
	header ("Location: menu.php?inc=all_inbox&op=all_inbox&err=".urlencode(_('Selected incoming SMS has been deleted')));	
	break;
}

?>
