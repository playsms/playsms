<?
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "sms_poll_list":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage poll</h2>
	    <p>
	    <input type=button value=\"Add SMS poll\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_poll&op=sms_poll_add')\" class=\"button\" />
	    <p>
	";
	if (!isadmin())
	{
	    $query_user_only = "WHERE uid='$uid'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll $query_user_only ORDER BY poll_id";
	$db_result = dba_query($db_query);
	$content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=150>Keyword</td>
        <td class=box_title>Title</td>
        <td class=box_title width=150>User</td>	
        <td class=box_title width=75>Status</td>
        <td class=box_title width=75>Action</td>
    </tr>
	";	
	$i=0;		
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $owner = uid2username($db_row[uid]);
	    $poll_status = "<font color=red>Disable</font>";
	    if ($db_row[poll_enable])
	    {
		$poll_status = "<font color=green>Enable</font>";
	    }
	    $action = "<a href=menu.php?inc=feature_sms_poll&op=sms_poll_view&poll_id=$db_row[poll_id] target=_blank>$icon_view</a>&nbsp;";
	    $action .= "<a href=menu.php?inc=feature_sms_poll&op=sms_poll_edit&poll_id=$db_row[poll_id]>$icon_edit</a>&nbsp;";
	    $action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete SMS poll keyword `$db_row[poll_keyword]` with all its choices and votes ?','menu.php?inc=feature_sms_poll&op=sms_poll_del&poll_id=$db_row[poll_id]')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>$db_row[poll_keyword]</td>
	<td class=$td_class>$db_row[poll_title]</td>
	<td class=$td_class>$owner</td>	
	<td class=$td_class>$poll_status</td>		
	<td class=$td_class align=center>$action</td>
    </tr>";	    
	}
	$content .= "</table>";
	echo $content;
	echo "
	    <p>
	    <input type=button value=\"Add SMS poll\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_poll&op=sms_poll_add')\" class=\"button\" />
	";
	break;
    case "sms_poll_view":
	$poll_id = $_REQUEST[poll_id];
	$db_query = "SELECT poll_keyword FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_keyword = $db_row[poll_keyword];
	header ("Location: output.php?show=poll&keyword=$poll_keyword");
	break;
    case "sms_poll_edit":
	$poll_id = $_REQUEST[poll_id];
	$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$edit_poll_title = $db_row[poll_title];
	$edit_poll_keyword = $db_row[poll_keyword];
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Edit SMS poll</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_poll&op=sms_poll_edit_yes method=post>
	    <input type=hidden name=edit_poll_id value=\"$poll_id\">
	    <input type=hidden name=edit_poll_keyword value=\"$edit_poll_keyword\">
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>SMS poll keyword</td><td width=5>:</td><td><b>$edit_poll_keyword</b></td>
	    </tr>
	    <tr>
		<td>SMS poll title</td><td>:</td><td><input type=text size=60 maxlength=200 name=edit_poll_title value=\"$edit_poll_title\"></td>
	    </tr>	    
	</table>	    
	    <p><input type=submit class=button value=\"Save Poll\">
	    </form>
	    <br>
	";
	echo $content;
	$content = "
	    <h2>Edit SMS poll choices</h2>
	    <p>
	";
	$db_query = "SELECT choice_id,choice_title,choice_keyword FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
	$db_result = dba_query($db_query);
	$content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=150>Keyword</td>
        <td class=box_title>title</td>
        <td class=box_title width=75>Action</td>
    </tr>
	";
	$i=0;	
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $choice_id = $db_row[choice_id];
	    $choice_keyword = $db_row[choice_keyword];
	    $choice_title = $db_row[choice_title];
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>$choice_keyword</td>
	<td class=$td_class>$choice_title</td>
	<td class=$td_class align=center><a href=\"javascript:ConfirmURL('Are you sure you want to delete choice titled `".$choice_title."` with keyword `".$choice_keyword."` ?','menu.php?inc=feature_sms_poll&op=sms_poll_choice_del&poll_id=$poll_id&choice_id=$choice_id');\">$icon_delete</a></td>
    </tr>";	    
	}
	$content .= "
    </table>
	    <p><b>Add choice to this poll</b>
	    <form action=\"menu.php?inc=feature_sms_poll&op=sms_poll_choice_add\" method=post>
	    <input type=hidden name=poll_id value=\"$poll_id\">
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=80>Choice Keyword</td><td width=5>:</td><td><input type=text size=3 maxlength=10 name=add_choice_keyword></td>
	    </tr>
	    <tr>
		<td>Choice Title</td><td>:</td><td><input type=text size=60 maxlength=250 name=add_choice_title></td>
	    </tr>	    
	</table>	    
	    <p><input type=submit class=button value=\"Add Choice\">
	    </form>
	    <br>";
	echo $content;
	$db_query = "SELECT poll_enable FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_status = "<font color=red><b>Disable</b></font>";
	if ($db_row[poll_enable])
	{
	    $poll_status = "<font color=green><b>Enable</b></font>";
	}
	$content = "
	    <h2>Enable or disable this poll</h2>
	    <p>
	    <p>Current status: $poll_status
	    <p>What do you want to do ?
	    <p>- <a href=\"menu.php?inc=feature_sms_poll&op=sms_poll_status&poll_id=$poll_id&ps=1\">I want to <b>enable</b> this poll</a>
	    <p>- <a href=\"menu.php?inc=feature_sms_poll&op=sms_poll_status&poll_id=$poll_id&ps=0\">I want to <b>disable</b> this poll</a>
	    <br>
	";
	echo $content;
	break;
    case "sms_poll_edit_yes":
	$edit_poll_id = $_POST[edit_poll_id];
	$edit_poll_keyword = $_POST[edit_poll_keyword];
	$edit_poll_title = $_POST[edit_poll_title];
	if ($edit_poll_id && $edit_poll_title && $edit_poll_keyword)
	{
	    $db_query = "
	        UPDATE "._DB_PREF_."_featurePoll
	        SET c_timestamp='".mktime()."',poll_title='$edit_poll_title',poll_keyword='$edit_poll_keyword'
		WHERE poll_id='$edit_poll_id' AND uid='$uid'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
	        $error_string = "SMS poll with keyword `$edit_poll_keyword` has been saved";
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_edit&poll_id=$edit_poll_id&err=".urlencode($error_string));
	break;
    case "sms_poll_status":
	$poll_id = $_REQUEST[poll_id];
	$ps = $_REQUEST[ps];
	$db_query = "UPDATE "._DB_PREF_."_featurePoll SET c_timestamp='".mktime()."',poll_enable='$ps' WHERE poll_id='$poll_id'";
	$db_result = @dba_affected_rows($db_query);
	if ($db_result > 0)
	{
	    $error_string = "This poll status has been changed!";
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id&err=".urlencode($error_string));
	break;
    case "sms_poll_del":
	$poll_id = $_REQUEST[poll_id];
	$db_query = "SELECT poll_title FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_title = $db_row[poll_title];
	if ($poll_title)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featurePoll WHERE poll_title='$poll_title'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "SMS poll `$poll_title` with all its messages has been deleted!";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_list&err=".urlencode($error_string));
	break;
    case "sms_poll_choice_add":
	$poll_id = $_POST[poll_id];
	$add_choice_title = $_POST[add_choice_title];
	$add_choice_keyword = strtoupper($_POST[add_choice_keyword]);
	if ($poll_id && $add_choice_title && $add_choice_keyword)
	{
	    $db_query = "SELECT choice_id FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_keyword='$add_choice_keyword'";
	    $db_result = @dba_num_rows($db_query);
	    if (!$db_result)
	    {
		$db_query = "
		    INSERT INTO "._DB_PREF_."_featurePoll_choice 
		    (poll_id,choice_title,choice_keyword)
		    VALUES ('$poll_id','$add_choice_title','$add_choice_keyword')
		";
		if ($db_result = @dba_insert_id($db_query))
		{
		    $error_string = "Choice with keyword `$add_choice_keyword` has been added";
		}
	    }
	    else
	    {
		$error_string = "Choice with keyword `$add_choice_keyword` already exists";
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";	    
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id&err=".urlencode($error_string));
	break;
    case "sms_poll_choice_del":
	$poll_id = $_REQUEST[poll_id];
	$choice_id = $_REQUEST[choice_id];
	$db_query = "SELECT choice_keyword FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$choice_keyword = $db_row[choice_keyword];
	$error_string = "Fail to delete SMS poll choice with keyword `$choice_keyword`!";
	if ($poll_id && $choice_id && $choice_keyword)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
	    if (@dba_affected_rows($db_query))
	    {
		$db_query = "DELETE FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
		dba_query($db_query);
		$error_string = "SMS poll choice with keyword `$choice_keyword` and all its voters has been deleted!";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id&err=".urlencode($error_string));
	break;
    case "sms_poll_add":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Add SMS poll</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_poll&op=sms_poll_add_yes method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>SMS poll keyword</td><td width=5>:</td><td><input type=text size=3 maxlength=10 name=add_poll_keyword value=\"$add_poll_keyword\"></td>
	    </tr>
	    <tr>
		<td>SMS poll title</td><td>:</td><td><input type=text size=60 maxlength=200 name=add_poll_title value=\"$add_poll_title\"></td>
	    </tr>	    
	</table>
	    <p><input type=submit class=button value=Add>
	    </form>
	";
	echo $content;
	break;
    case "sms_poll_add_yes":
	$add_poll_keyword = strtoupper($_POST[add_poll_keyword]);
	$add_poll_title = $_POST[add_poll_title];
	if ($add_poll_title && $add_poll_keyword)
	{
	    if (checkavailablekeyword($add_poll_keyword))
	    {
		$db_query = "
		    INSERT INTO "._DB_PREF_."_featurePoll (uid,poll_keyword,poll_title)
		    VALUES ('$uid','$add_poll_keyword','$add_poll_title')
		";
		if ($new_uid = @dba_insert_id($db_query))
		{
		    $error_string = "SMS poll with keyword `$add_poll_keyword` has been added";
		}
	    }
	    else
	    {
		$error_string = "SMS keyword `$add_poll_keyword` already exists, reserved or use by other feature!";
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";
	}
	header ("Location: menu.php?inc=feature_sms_poll&op=sms_poll_add&err=".urlencode($error_string));
	break;
}

?>