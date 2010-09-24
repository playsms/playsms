<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

$gpid = $_REQUEST['gpid'];

switch ($op)
{
    case "edit":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Edit group')."</h2>
	    <p>
	    <form action=menu.php?inc=tools_simplephonebook&route=dir_edit&op=edit_yes&gpid=$gpid method=POST>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=75>"._('Group name')."</td><td width=5>:</td><td><input type=text name=dir_name value=\"".gpid2gpname($gpid)."\" size=50></td>
	    </tr>
	    <tr>
		<td>"._('Group code')."</td><td>:</td><td><input type=text name=dir_code value=\"".phonebook_groupid2code($gpid)."\" size=10> ("._('please use uppercase and make it short').")</td>
	    </tr>	    
	</table>
	    <p>"._('Note').": "._('Group code used by keyword')." BC ("._('broadcast SMS from single SMS').")
	    <p><input type=submit class=button value=\""._('Save')."\"> 
	    </form>
	";
	echo $content;
	break;
    case "edit_yes":
	$dir_name = $_POST['dir_name'];
	$dir_code = strtoupper(trim($_POST['dir_code']));
	if ($dir_name && $dir_code)
	{
	    $db_query = "SELECT gp_code FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE uid='$uid' AND gp_code='$dir_code' AND NOT gpid='$gpid'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		header("Location: menu.php?inc=phonebook_list&err=".urlencode(_('No changes has been made')));
		die();
	    }
	    else
	    {
		$db_query = "UPDATE "._DB_PREF_."_toolsSimplephonebook_group SET c_timestamp='".mktime()."',gp_name='$dir_name',gp_code='$dir_code' WHERE uid='$uid' AND gpid='$gpid'";
		$db_result = dba_query($db_query);
		header("Location:  menu.php?inc=phonebook_list&err=".urlencode(_('Group has been edited')." ("._('group').": `$dir_name`, "._('code')." `$dir_code`)"));
		die();
	    }
	}
	header ("Location: menu.php?inc=tools_simplephonebook&route=dir_edit&op=edit&gpid=$gpid&err=".urlencode(_('You must fill all field')));
	break;
}

?>