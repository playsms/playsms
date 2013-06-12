<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/msgtoolbox/config.php";

$gw = gateway_get();

if ($gw == $msgtoolbox_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_msgtoolbox&op=manage_activate\">"._('click here to activate')."</a>)";
}


switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage msgtoolbox')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_msgtoolbox&op=manage_save method=post>
		<table width=100% cellpadding=1 cellspacing=2 border=0>
		    <tr>
			<td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>msgtoolbox</b> $status_active</td>
		    </tr>
		    <tr>
			<td>"._('msgtoolbox URL')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_url value=\"".$msgtoolbox_param['url']."\"></td>
		    </tr>
		    <tr>
			<td>"._('Route')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_route value=\"".$msgtoolbox_param['route']."\"></td>
		    </tr>
		    <td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$msgtoolbox_param['username']."\"></td>
		    </tr>	    	    
		    <tr>
			<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
		    </tr>	    
		    <tr>
			<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$msgtoolbox_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
		    </tr>
		    <tr>
			<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$msgtoolbox_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
		    </tr>
		</table>
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>
	";
		echo $content;
		break;
	case "manage_save":
		$up_url = $_POST['up_url'];
		$up_route = $_POST['up_route'];
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_url && $up_username)
		{
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayMsgtoolbox_config 
				SET c_timestamp='".mktime()."',
				    cfg_url='$up_url',
				    cfg_route='$up_route',
				    cfg_username='$up_username',
				    ".$password_change."
				    cfg_global_sender='$up_global_sender',
				    cfg_datetime_timezone='$up_global_timezone'
			    ";
			if (@dba_affected_rows($db_query))
			{
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_msgtoolbox&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='msgtoolbox'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_msgtoolbox&op=manage");
		exit();
		break;
}

?>