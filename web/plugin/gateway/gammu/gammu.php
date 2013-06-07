<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/gammu/config.php";

$gw = gateway_get();

if ($gw == $gammu_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_gammu&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage gammu')."</h2>
	    <p>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>gammu</b> $status_active</td>
	    </tr>
	</table>	    
	";
		echo $content;
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='gammu'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_gammu&op=manage");
		exit();
		break;
}

?>