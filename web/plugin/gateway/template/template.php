<?php
if(!isadmin()){forcenoaccess();};

include "$apps_path[plug]/gateway/template/config.php";

if ($gateway_module == $template_param[name])
{
    $status_active = "(<font color=green><b>Active</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>Inactive</b></font>) (<a href=\"menu.php?inc=gateway_template&op=manage_activate\">click here to activate</a>)";
}


switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage ".$template_param[name]."</h2>
	    <p>
	    <form action=menu.php?inc=gateway_template&op=manage_save method=post>
	    <p>Gateway Name: <b>".$template_param[name]."</b> $status_active
	    <p>Template Path: <input type=text size=40 maxlength=250 name=up_path value=\"".$template_param[path]."\"> (No trailing slash \"/\")
	    <!--
	    <p>Note:</br>
	    <p><input type=checkbox name=up_trn $checked> Send SMS message without footer banner ($username) 
	    -->
	    <p><input type=submit class=button value=Save>
	    </form>
	";
	echo $content;
	break;
    case "manage_save":
	$up_path = $_POST[up_path];
	$error_string = "No changes made!";
	if ($up_path)
	{
	    $db_query = "
		UPDATE "._DB_PREF_."_gatewayTemplate_config 
		SET c_timestamp='".mktime()."',cfg_path='$up_path'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "Gateway module configurations has been saved";
	    }
	}
	header ("Location: menu.php?inc=gateway_template&op=manage&err=".urlencode($error_string));
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='template'";
	$db_result = dba_query($db_query);
	$error_string = "Gateway has been activated";
	header ("Location: menu.php?inc=gateway_template&op=manage&err=".urlencode($error_string));
	break;
}

?>