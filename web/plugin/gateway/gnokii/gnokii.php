<?
if(!isadmin()){forcenoaccess();};

include "$apps_path[plug]/gateway/gnokii/config.php";

if ($gateway_module == $gnokii_param[name])
{
    $status_active = "(<font color=green><b>Active</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>Inactive</b></font>) (<a href=\"menu.php?inc=gateway_gnokii&op=manage_activate\">click here to activate</a>)";
}

switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage ".$gnokii_param[name]."</h2>
	    <p>
	    <form action=menu.php?inc=gateway_gnokii&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>Gateway Name</td><td width=5>:</td><td><b>".$gnokii_param[name]."</b> $status_active</td>
	    </tr>
	    <tr>
		<td>Gnokii Installation Path</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_path value=\"".$gnokii_param[path]."\"> (No trailing slash \"/\")</td>
	    </tr>	    
	</table>	    
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
		UPDATE "._DB_PREF_."_gatewayGnokii_config 
		SET c_timestamp='".mktime()."',cfg_path='$up_path'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "Gateway module configurations has been saved";
	    }
	}
	header ("Location: menu.php?inc=gateway_gnokii&op=manage&err=".urlencode($error_string));
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='gnokii'";
	$db_result = dba_query($db_query);
	$error_string = "Gateway has been activated";
	header ("Location: menu.php?inc=gateway_gnokii&op=manage&err=".urlencode($error_string));
	break;
}

?>