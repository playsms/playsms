<?php
if(!isadmin()){forcenoaccess();};

include "$apps_path[plug]/gateway/uplink/config.php";

if ($gateway_module == $uplink_param[name])
{
    $status_active = "(<font color=green><b>Active</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>Inactive</b></font>) (<a href=\"menu.php?inc=gateway_uplink&op=manage_activate\">click here to activate</a>)";
}


switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage ".$uplink_param[name]."</h2>
	    <p>
	    <form action=menu.php?inc=gateway_uplink&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>Gateway Name:</td><td width=5>:</td><td><b>".$uplink_param[name]."</b> $status_active</td>
	    </tr>
	    <tr>
		<td>Master URL</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_master value=\"".$uplink_param[master]."\"></td>
	    </tr>
	    <tr>
		<td>Username</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$uplink_param[username]."\"></td>
	    </tr>	    	    
	    <tr>
		<td>Password</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_password value=\"".$uplink_param[password]."\"></td>
	    </tr>	    
	    <tr>
		<td>Global Sender</td><td>:</td><td><input type=text size=11 maxlength=11 name=up_global_sender value=\"".$uplink_param[global_sender]."\"> (Max. 16 numeric or 11 alphanumeric char. empty to disable)</td>
	    </tr>
	    <tr>
		<td>Uplink Incoming Path</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_incoming_path value=\"".$uplink_param[path]."\"> (No trailing slash \"/\")</td>
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
	$up_master = $_POST['up_master'];
	$up_username = $_POST['up_username'];
	$up_password = $_POST['up_password'];
	$up_global_sender = $_POST['up_global_sender'];
	$up_incoming_path = $_POST['up_incoming_path'];
	$error_string = "No changes made!";
	if ($up_master && $up_username && $up_password && $up_incoming_path)
	{
	    $db_query = "
		UPDATE "._DB_PREF_."_gatewayUplink_config 
		SET c_timestamp='".mktime()."',
		    cfg_master='$up_master',
		    cfg_username='$up_username',
		    cfg_password='$up_password',
		    cfg_global_sender='$up_global_sender',
		    cfg_incoming_path='$up_incoming_path'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "Gateway module configurations has been saved";
	    }
	}
	header ("Location: menu.php?inc=gateway_uplink&op=manage&err=".urlencode($error_string));
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='uplink'";
	$db_result = dba_query($db_query);
	$error_string = "Gateway has been activated";
	header ("Location: menu.php?inc=gateway_uplink&op=manage&err=".urlencode($error_string));
	break;
}

?>