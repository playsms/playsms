<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "create":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Create group</h2>
	    <p>
	    <form action=menu.php?inc=dir_create&op=create_yes method=POST>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=75>Group Name</td><td width=5>:</td><td><input type=text name=dir_name size=50></td>
	    </tr>
	    <tr>
		<td>Group Code</td><td>:</td><td><input type=text name=dir_code size=10> (please use uppercase and make it short)</td>
	    </tr>	    
	</table>
	    <p>Note: Group Code used by keyword BC (broadcast SMS from single SMS)
	    <p><input type=submit class=button value=Create> 
	    </form>
	";
	echo $content;
	break;
    case "create_yes":
	$dir_name = $_POST['dir_name'];
	$dir_code = strtoupper(trim($_POST['dir_code']));
	if ($dir_name && $dir_code)
	{
	    $db_query = "SELECT gp_code FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid' AND gp_code='$dir_code'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		header("Location: menu.php?inc=dir_create&op=create&err=".urlencode("Group code `$dir_code` already in use"));
		die();
	    }
	    else
	    {
		$db_query = "INSERT INTO "._DB_PREF_."_tblUserGroupPhonebook (uid,gp_name,gp_code) VALUES ('$uid','$dir_name','$dir_code')";
		$db_result = dba_query($db_query);
		header("Location:  menu.php?inc=dir_create&op=create&err=".urlencode("Group `$dir_name` with code `$dir_code` has been added"));
		die();
	    }
	}
	header ("Location: menu.php?inc=dir_create&op=create&err=".urlencode("Group name and description must be filled"));
	break;
}

?>