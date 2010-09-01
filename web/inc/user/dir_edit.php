<?php
if(!valid()){forcenoaccess();};

$gpid = $_REQUEST[gpid];

switch ($op)
{
    case "edit":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Edit group</h2>
	    <p>
	    <form action=menu.php?inc=dir_edit&op=edit_yes&gpid=$gpid method=POST>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=75>Group Name</td><td width=5>:</td><td><input type=text name=dir_name value=\"".gpid2gpname($gpid)."\" size=50></td>
	    </tr>
	    <tr>
		<td>Group Code</td><td>:</td><td><input type=text name=dir_code value=\"".gpid2gpcode($gpid)."\" size=10> (please use uppercase and make it short)</td>
	    </tr>	    
	</table>
	    <p>Note: Group Code used by keyword BC (broadcast SMS from single SMS)
	    <p><input type=submit class=button value=\"Save\"> 
	    </form>
	";
	echo $content;
	break;
    case "edit_yes":
	$dir_name = $_POST[dir_name];
	$dir_code = strtoupper(trim($_POST[dir_code]));
	if ($dir_name && $dir_code)
	{
	    $db_query = "SELECT gp_code FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid' AND gp_code='$dir_code' AND NOT gpid='$gpid'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		header("Location: menu.php?inc=phonebook_list&err=".urlencode("No changes has been made on group `$dir_name` code `$dir_code`"));
		die();
	    }
	    else
	    {
		$db_query = "UPDATE "._DB_PREF_."_tblUserGroupPhonebook SET c_timestamp='".mktime()."',gp_name='$dir_name',gp_code='$dir_code' WHERE uid='$uid' AND gpid='$gpid'";
		$db_result = dba_query($db_query);
		header("Location:  menu.php?inc=phonebook_list&err=".urlencode("Group `$dir_name` with code `$dir_code` has been edited"));
		die();
	    }
	}
	header ("Location: menu.php?inc=dir_edit&op=edit&gpid=$gpid&err=".urlencode("Group name and description must be filled"));
	break;
}

?>