<?php
if(!isadmin()){forcenoaccess();};

switch ($op)
{
    case "all_incoming":
	if(!$page){$page = 1;}
	if(!$nav){$nav = 1;}
	
	$line_per_page = 50;
	$max_nav = 15;

	$db_query = "";

	$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSIncoming WHERE flag_deleted='0'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$num_rows = $db_row['count'];

	$pages = ceil($num_rows/$line_per_page);
	$nav_pages = themes_navbar($pages, $nav, $max_nav, "index.php?app=menu&inc=all_incoming&op=all_incoming", $page);
	$limit = ($page-1)*$line_per_page;    
	
	$content = "
	    <h2>"._('All incoming SMS')."</h2>
	    <p>$nav_pages</p>
	    <form name=\"fm_incoming\" action=\"index.php?app=menu&inc=all_incoming&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
	    <table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
        <thead>
	    <tr>
	      <th align=center width=4>*</th>
	      <th align=center width=10%>"._('User')."</th>
	      <th align=center width=20%>"._('Time')."</th>
	      <th align=center width=10%>"._('Sender')."</th>
	      <th align=center width=10%>"._('Keyword')."</th>
	      <th align=center width=30%>"._('Content')."</th>
	      <th align=center width=10%>"._('Feature')."</th>
	      <th align=center width=10%>"._('Status')."</th>
	      <th align=center class=\"sorttable_nosort\">"._('Action')."</td>
	      <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></td>
	    </tr>
        </thead>
        <tbody>
	";

	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSIncoming WHERE flag_deleted='0' ORDER BY in_id DESC LIMIT $limit,$line_per_page";
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
	    $in_keyword = $db_row['in_keyword'];
	    $in_message = $db_row['in_message'];
	    $in_datetime = $db_row['in_datetime'];
	    $in_feature = $db_row['in_feature'];
	    $in_status = ( $db_row['in_status'] == 1 ? '<p><font color=green>'._('handled').'</font></p>' : '<p><font color=red>'._('unhandled').'</font></p>' );
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
	          <td valign=top class=$td_class align=center nowrap>
		    <!-- <a href=\"javascript: PopupReplySms('$current_sender', '".urlencode($in_message)."')\">$icon_reply</a> -->
		    <!-- <a href=\"index.php?app=menu&inc=phone_add&op=add&phone=$current_sender\">$icon_phonebook</a> -->
		    <a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this SMS ?')."','index.php?app=menu&inc=all_incoming&op=all_incoming_del&inid=$in_id')\">$icon_delete</a>
		    
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
	    echo "<div class=error_string>$err</div><br><br>";
	}
	echo $content;
	break;
    case "all_incoming_del":
	$error_string = "Fail to delete incoming SMS";
	if ($in_id = $_REQUEST['inid'])
	{
	    $db_query = "UPDATE "._DB_PREF_."_tblSMSIncoming SET c_timestamp='".mktime()."',flag_deleted='1' WHERE in_id='$in_id'";
	    $db_result = dba_affected_rows($db_query);
	    if ($db_result > 0)
	    {
		$error_string = _('Selected incoming SMS has been deleted');
	    }
	}
	header("Location: index.php?app=menu&inc=all_incoming&op=all_incoming&err=".urlencode($error_string));
	break;
    case "act_del":
	$item_count = $_POST['item_count'];
	
	for ($i=1;$i<=$item_count;$i++)
	{
	    $chkid = $_POST['chkid'.$i];
	    $inid = $_POST['inid'.$i];
	    
	    if(($chkid=="on") && $inid)
	    {
		$db_query = "UPDATE "._DB_PREF_."_tblSMSIncoming SET c_timestamp='".mktime()."',flag_deleted='1' WHERE in_id='$inid'";
		$db_result = dba_affected_rows($db_query);
	    }
	}
	header ("Location: index.php?app=menu&inc=all_incoming&op=all_incoming&err=".urlencode(_('Selected incoming SMS has been deleted')));	
	break;
}

?>
