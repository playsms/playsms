<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/gnokii/config.php";

if ($gnokii_param['ready'])
{
	$status_active = "(<b><font color=green>"._('Ready')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_gnokii&op=manage_unready\">"._('click here to set gateway unready')."</a>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Unready')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_gnokii&op=manage_ready\">"._('click here to set gateway ready')."</a>)";
}

switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage gnokii')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_gnokii&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>gnokii</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('Gnokii installation path')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_path value=\"".$gnokii_param['path']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>	    
	</table>	    
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>
	";
		echo $content;
		break;
	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_path)
		{
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayGnokii_config 
		SET c_timestamp='".mktime()."',cfg_path='$up_path'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_gnokii&op=manage");
		exit();
		break;
	case "manage_ready":
		$db_query = "UPDATE "._DB_PREF_."_gatewayGnokii_config SET ready=TRUE";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway is now ready to be used!');
		header ("Location: index.php?app=menu&inc=gateway_gnokii&op=manage&err=".urlencode($error_string));
		break;
	case "manage_unready":
		$db_query = "UPDATE "._DB_PREF_."_gatewayGnokii_config SET ready=FALSE";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway is not ready to be used!');
		header ("Location: index.php?app=menu&inc=gateway_gnokii&op=manage&err=".urlencode($error_string));
		break;
}

?>
