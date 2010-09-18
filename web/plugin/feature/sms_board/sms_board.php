<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "sms_board_list":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Manage board')."</h2>
	    <p>
	    <input type=button value=\""._('Add SMS board')."\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_board&op=sms_board_add')\" class=\"button\" />
	    <p>
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=100>"._('Keyword')."</td>
        <td class=box_title>"._('Forward')."</td>
        <td class=box_title width=100>"._('User')."</td>
        <td class=box_title width=75>"._('Action')."</td>
    </tr>		    
	";
	if (!isadmin())
	{
	    $query_user_only = "WHERE uid='$uid'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureBoard $query_user_only ORDER BY board_keyword";
	$db_result = dba_query($db_query);
	$i=0;	
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $owner = uid2username($db_row['uid']);
	    $action = "<a href=menu.php?inc=feature_sms_board&op=sms_board_view&board_id=".$db_row['board_id']." target=_blank>$icon_view</a>&nbsp;";
	    $action .= "<a href=menu.php?inc=feature_sms_board&op=sms_board_edit&board_id=".$db_row['board_id'].">$icon_edit</a>&nbsp;";
	    $action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS board with all its messages ?')." ("._('keyword').": `".$db_row['board_keyword']."`)','menu.php?inc=feature_sms_board&op=sms_board_del&board_id=".$db_row['board_id']."')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>".$db_row['board_keyword']."</td>
	<td class=$td_class>".$db_row['board_forward_email']."</td>
	<td class=$td_class>$owner</td>	
	<td class=$td_class align=center>$action</td>
    </tr>";
	}
	
	$content .= "
    </table>
	";
	echo $content;
	echo "
	    <p>
	    <input type=button value=\"Add SMS board\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_board&op=sms_board_add')\" class=\"button\" />
	";
	break;
    case "sms_board_view":
	$board_id = $_REQUEST['board_id'];
	$db_query = "SELECT board_keyword FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$board_keyword = $db_row['board_keyword'];
	header ("Location: output.php?keyword=$board_keyword");
	break;
    case "sms_board_edit":
	$board_id = $_REQUEST['board_id'];
	$db_query = "SELECT * FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$edit_board_keyword = $db_row['board_keyword'];
	$edit_email = $db_row['board_forward_email'];
	$edit_template = $db_row['board_pref_template'];
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Edit SMS board')."</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_board&op=sms_board_edit_yes method=post>
	    <input type=hidden name=edit_board_id value=$board_id>
	    <input type=hidden name=edit_board_keyword value=$edit_board_keyword>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('SMS board keyword')."</td><td width=5>:</td><td><b>$edit_board_keyword</b></td>
	    </tr>
	    <tr>
		<td>"._('Forward to email')."</td><td>:</td><td><input type=text size=30 name=edit_email value=\"$edit_email\"></td>
	    </tr>	    
	    <tr>
		<td colspan=3>
		"._('Template').":
		<br><textarea name=edit_template rows=5 cols=60>$edit_template</textarea>		
		</td>
	    </tr>	    	    
	</table>	    
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>
	";
	echo $content;
	break;
    case "sms_board_edit_yes":
	$edit_board_id = $_POST['edit_board_id'];
	$edit_board_keyword = $_POST['edit_board_keyword'];
	$edit_email = $_POST['edit_email'];
	$edit_template = $_POST['edit_template'];
	if ($edit_board_id)
	{
	    if (!$edit_template)
	    {
		$edit_template = "<font color=black size=-1><b>{SENDER}</b></font><br>";
		$edit_template .= "<font color=black size=-2><i>{DATETIME}</i></font><br>";
		$edit_template .= "<font color=black size=-1>{MESSAGE}</font>";
	    }
	    $db_query = "
	        UPDATE "._DB_PREF_."_featureBoard
	        SET c_timestamp='".mktime()."',board_forward_email='$edit_email',board_pref_template='$edit_template'
		WHERE board_id='$edit_board_id' AND uid='$uid'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
	        $error_string = _('SMS board has been saved')." ("._('keyword').": `$edit_board_keyword`)";
	    }
	}
	else
	{
	    $error_string = _('You must fill all fields');
	}
	header ("Location: menu.php?inc=feature_sms_board&op=sms_board_edit&board_id=$edit_board_id&err=".urlencode($error_string));
	break;
    case "sms_board_del":
	$board_id = $_REQUEST['board_id'];
	$db_query = "SELECT board_keyword FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$board_keyword = $db_row['board_keyword'];
	if ($board_keyword)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$board_keyword'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = _('SMS board with all its messages has been deleted')." ("._('keyword').": `$board_keyword`)";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_board&op=sms_board_list&err=".urlencode($error_string));
	break;
    case "sms_board_add":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Add SMS board')."</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_board&op=sms_board_add_yes method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('SMS board keyword')."</td><td width=5>:</td><td><input type=text size=30 maxlength=30 name=add_board_keyword value=\"$add_board_keyword\"></td>
	    </tr>
	    <tr>
		<td colspan=3><p><b>"._('Leave them empty if you dont know what to fill in these boxes below')."</b></td>
	    </tr>	    
	    <tr>
		<td>"._('Forward to email')."</td><td>:</td><td><input type=text size=30 name=add_email value=\"$add_email\"></td>
	    </tr>	    	    
	    <tr>
		<td colspan=3>
		"._('Template').":
	       <br><textarea name=add_template rows=5 cols=60>$add_template</textarea>
		</td>
	    </tr>	    	    
	</table>
	    <p><input type=submit class=button value=\""._('Add')."\">
	    </form>
	";
	echo $content;
	break;
    case "sms_board_add_yes":
	$add_board_keyword = strtoupper($_POST['add_board_keyword']);
	$add_email = $_POST['add_email'];
	$add_template = $_POST['add_template'];
	if ($add_board_keyword)
	{
	    if (checkavailablekeyword($add_board_keyword))
	    {
		if (!$add_template)
		{
		    $add_template = "<font color=black size=-1><b>{SENDER}</b></font><br>";
		    $add_template .= "<font color=black size=-2><i>{DATETIME}</i></font><br>";
		    $add_template .= "<font color=black size=-1>{MESSAGE}</font>";
		}
		$db_query = "
		    INSERT INTO "._DB_PREF_."_featureBoard (uid,board_keyword,board_forward_email,board_pref_template)
		    VALUES ('$uid','$add_board_keyword','$add_email','$add_template')
		";
		if ($new_uid = @dba_insert_id($db_query))
		{
		    $error_string = _('SMS board has been added')." ("._('keyword').": `$add_board_keyword`)";
		}
		else
		{
		    $error_string = _('Fail to add SMS board')." ("._('keyword').": `$add_board_keyword`)";
		}
	    }
	    else
	    {
		$error_string = _('SMS keyword already exists, reserved or use by other feature')." ("._('keyword').": `$add_board_keyword`)";
	    }
	}
	else
	{
	    $error_string = _('You must fill all fields');
	}
	header ("Location: menu.php?inc=feature_sms_board&op=sms_board_add&err=".urlencode($error_string));
	break;
}

?>