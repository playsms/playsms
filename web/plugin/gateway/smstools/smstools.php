<?php
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/smstools/config.php";

if ($gateway_module == $smstools_param['name'])
{
    $status_active = "(<font color=green><b>Active</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>Inactive</b></font>) (<a href=\"menu.php?inc=gateway_smstools&op=manage_activate\">click here to activate</a>)";
}

switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage ".$smstools_param['name']."</h2>
	    <p>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>Gateway Name</td><td width=5>:</td><td><b>".$smstools_param['name']."</b> $status_active</td>
	    </tr>
	</table>	    
	";
	echo $content;
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='smstools'";
	$db_result = dba_query($db_query);
	$error_string = "Gateway has been activated";
	header ("Location: menu.php?inc=gateway_smstools&op=manage&err=".urlencode($error_string));
	break;
}

?>