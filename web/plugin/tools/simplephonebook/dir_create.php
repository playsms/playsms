<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

switch ($op)
{
    case "create":
	if ($err)
	{
	    $content = "<div class=error_string>$err</div>";
	}
	$content .= "
	    <h2>"._('Create group')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create_yes method=POST>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=75>"._('Group name')."</td><td width=5>:</td><td><input type=text name=dir_name size=50></td>
	    </tr>
	    <tr>
		<td>"._('Group code')."</td><td>:</td><td><input type=text name=dir_code size=10> ("._('please use uppercase and make it short').")</td>
	    </tr>	    
	</table>
	    <p>"._('Note').": "._('Group code used by keyword')." BC ("._('broadcast SMS from single SMS').")
	    <p><input type=submit class=button value=\""._('Create')."\"> 
	    </form>
	";
	echo $content;
	break;
    case "create_yes":
	$dir_name = $_POST['dir_name'];
	$dir_code = strtoupper(trim($_POST['dir_code']));
	if ($dir_name && $dir_code)
	{
	    $db_query = "SELECT gp_code FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE uid='$uid' AND gp_code='$dir_code'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		header("Location: index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create&err=".urlencode(_('Group code is already exists')." ("._('code').": `$dir_code`)"));
		die();
	    }
	    else
	    {
		$db_query = "INSERT INTO "._DB_PREF_."_toolsSimplephonebook_group (uid,gp_name,gp_code) VALUES ('$uid','$dir_name','$dir_code')";
		$db_result = dba_query($db_query);
		header("Location:  index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create&err=".urlencode(_('Group code has been added')." ("._('group').": `$dir_name`, "._('code').": `$dir_code`)"));
		die();
	    }
	}
	header ("Location: index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create&err=".urlencode(_('You must fill all field')));
	break;
}

?>