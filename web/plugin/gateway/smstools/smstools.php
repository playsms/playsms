<?php
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/smstools/config.php";

if ($gateway_module == $smstools_param['name'])
{
    $status_active = "(<font color=green><b>"._('Active')."</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>"._('Inactive')."</b></font>) (<a href=\"index.php?app=menu&inc=gateway_smstools&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
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
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='smstools'";
	$db_result = dba_query($db_query);
	$error_string = _('Gateway has been activated');
	header ("Location: index.php?app=menu&inc=gateway_smstools&op=manage&err=".urlencode($error_string));
	break;
}

?>