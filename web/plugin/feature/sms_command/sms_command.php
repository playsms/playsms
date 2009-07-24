<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "sms_command_list":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage command</h2>
	    <p>
	    <input type=button value=\"Add SMS command\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	    <p>SMS command exec path : <b>".$plugin_config['feature']['sms_command']['bin']."/</b>
	";
	if (!isadmin())
	{
	    $query_user_only = "WHERE uid='$uid'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand $query_user_only ORDER BY command_keyword";
	$db_result = dba_query($db_query);
	$content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=100>Keyword</td>
        <td class=box_title>Exec</td>
        <td class=box_title width=100>User</td>	
        <td class=box_title width=75>Action</td>
    </tr>	
	";	
	$i=0;		
	$maxlen=50;
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $owner = uid2username($db_row[uid]);
	    $action = "<a href=menu.php?inc=feature_sms_command&op=sms_command_edit&command_id=$db_row[command_id]>$icon_edit</a>&nbsp;";
	    $action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete SMS command keyword `$db_row[command_keyword]` ?','menu.php?inc=feature_sms_command&op=sms_command_del&command_id=$db_row[command_id]')\">$icon_delete</a>";
	    $command_exec = ( (strlen($db_row[command_exec]) > $maxlen) ? substr($db_row[command_exec],0,$maxlen)."..." : $db_row[command_exec] );
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>$db_row[command_keyword]</td>
	<td class=$td_class>".stripslashes($command_exec)."</td>
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
	    <input type=button value=\"Add SMS command\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	";
	break;
    case "sms_command_edit":
	$command_id = $_GET[command_id];
	$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand WHERE command_id='$command_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$edit_command_keyword = $db_row[command_keyword];
	$edit_command_exec = stripslashes($db_row[command_exec]);
	$edit_command_exec = str_replace($plugin_config['feature']['sms_command']['bin']."/",'',$edit_command_exec);
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Edit SMS command</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_command&op=sms_command_edit_yes method=post>
	    <input type=hidden name=edit_command_id value=$command_id>
	    <input type=hidden name=edit_command_keyword value=$edit_command_keyword>
	    <p>SMS command keyword: <b>$edit_command_keyword</b>
	    <p>Pass these parameter to command exec field:
	    <p><b>{SMSDATETIME}</b> replaced by SMS incoming date/time
	    <p><b>{SMSSENDER}</b> replaced by sender number
	    <p><b>{COMMANDKEYWORD}</b> replaced by command keyword 
	    <p><b>{COMMANDPARAM}</b> replaced by command parameter passed to server from SMS
	    <p>SMS command exec path : <b>".$plugin_config['feature']['sms_command']['bin']."</b>
	    <p>SMS command exec: <input type=text size=60 name=edit_command_exec value=\"$edit_command_exec\">
	    <p><input type=submit class=button value=Save>
	    </form>
	";
	echo $content;
	break;
    case "sms_command_edit_yes":
	$edit_command_id = $_POST[edit_command_id];
	$edit_command_keyword = $_POST[edit_command_keyword];
	$edit_command_exec = $_POST[edit_command_exec];
	if ($edit_command_id && $edit_command_keyword && $edit_command_exec)
	{
	    $edit_command_exec = str_replace("/","",$edit_command_exec);
	    $edit_command_exec = str_replace("|","",$edit_command_exec);
	    $edit_command_exec = str_replace("\\","",$edit_command_exec);
	    $db_query = "UPDATE "._DB_PREF_."_featureCommand SET c_timestamp='".mktime()."',command_exec='$edit_command_exec' WHERE command_keyword='$edit_command_keyword' AND uid='$uid'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "SMS command keyword `$edit_command_keyword` has been saved";
	    }
	    else
	    {
	        $error_string = "Fail to save SMS command keyword `$edit_command_keyword`";
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";
	}
	header ("Location: menu.php?inc=feature_sms_command&op=sms_command_edit&command_id=$edit_command_id&err=".urlencode($error_string));
	break;
    case "sms_command_del":
	$command_id = $_GET[command_id];
	$db_query = "SELECT command_keyword FROM "._DB_PREF_."_featureCommand WHERE command_id='$command_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$keyword_name = $db_row[command_keyword];
	if ($keyword_name)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featureCommand WHERE command_keyword='$keyword_name'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "SMS command keyword `$keyword_name` has been deleted!";
	    }
	    else
	    {
		$error_string = "Fail to delete SMS command keyword `$keyword_name`";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_command&op=sms_command_list&err=".urlencode($error_string));
	break;
    case "sms_command_add":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Add SMS command</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_command&op=sms_command_add_yes method=post>
	    <p>SMS command keyword: <input type=text size=10 maxlength=10 name=add_command_keyword value=\"$add_command_keyword\">
	    <p>Pass these parameter to command exec field:
	    <p><b>{SMSDATETIME}</b> replaced by SMS incoming date/time
	    <p><b>{SMSSENDER}</b> replaced by sender number
	    <p><b>{COMMANDKEYWORD}</b> replaced by command keyword 
	    <p><b>{COMMANDPARAM}</b> replaced by command parameter passed to server from SMS
	    <p>SMS command binary path : <b>".$plugin_config['feature']['sms_command']['bin']."</b>
	    <p>SMS command exec: <input type=text size=60 maxlength=200 name=add_command_exec value=\"$add_command_exec\">
	    <p><input type=submit class=button value=Add>
	    </form>
	";
	echo $content;
	break;
    case "sms_command_add_yes":
	$add_command_keyword = strtoupper($_POST[add_command_keyword]);
	$add_command_exec = $_POST[add_command_exec];
	if ($add_command_keyword && $add_command_exec)
	{
	    $add_command_exec = $add_command_exec;
	    $add_command_exec = str_replace("/","",$add_command_exec);
	    $add_command_exec = str_replace("|","",$add_command_exec);
	    $add_command_exec = str_replace("\\","",$add_command_exec);
	    if (checkavailablekeyword($add_command_keyword))
	    {
		$db_query = "INSERT INTO "._DB_PREF_."_featureCommand (uid,command_keyword,command_exec) VALUES ('$uid','$add_command_keyword','$add_command_exec')";
		if ($new_uid = @dba_insert_id($db_query))
		{
	    	    $error_string = "SMS command keyword `$add_command_keyword` has been added";
		}
		else
		{
	    	    $error_string = "Fail to add SMS command keyword `$add_command_keyword`";
		}
	    }
	    else
	    {
		$error_string = "SMS keyword `$add_command_keyword` already exists, reserved or use by other feature!";
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";
	}
	header ("Location: menu.php?inc=feature_sms_command&op=sms_command_add&err=".urlencode($error_string));
	break;
}

?>