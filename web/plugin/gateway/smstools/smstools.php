<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/smstools/config.php";

if ($smstools_param['ready'])
{
	$status_active = "(<b><font color=green>"._('Ready')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_smstools&op=manage_unready\">"._('click here to set gateway unready')."</a>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Unready')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_smstools&op=manage_ready\">"._('click here to set gateway ready')."</a>)";
}

switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage smstools')."</h2>
	    <p>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>smstools</b> $status_active</td>
	    </tr>
	</table>	    
	";
		echo $content;
		break;
	case "manage_ready":
		$db_query = "UPDATE "._DB_PREF_."_gatewaySMSTools_config SET ready=TRUE";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway is now ready to be used!');
		header ("Location: index.php?app=menu&inc=gateway_smstools&op=manage&err=".urlencode($error_string));
		break;
	case "manage_unready":
		$db_query = "UPDATE "._DB_PREF_."_gatewaySMSTools_config SET ready=FALSE";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway is not ready to be used!');
		header ("Location: index.php?app=menu&inc=gateway_smstools&op=manage&err=".urlencode($error_string));
		break;
}

?>
