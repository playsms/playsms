<?php
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/kannel/config.php";

if ($gateway_module == $kannel_param['name'])
{
    $status_active = "(<font color=green><b>"._('Active')."</b></font>)";
}
else
{
    $status_active = "(<font color=red><b>"._('Inactive')."</b></font>) (<a href=\"menu.php?inc=gateway_kannel&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Manage kannel')."</h2>
	    <p>
	    <form action=menu.php?inc=gateway_kannel&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>kannel</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$kannel_param['username']."\"></td>
	    </tr>	    
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_password value=\"".$kannel_param['password']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Global sender')."</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_global_sender value=\"".$kannel_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Bearerbox hostname or IP')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_bearerbox_host value=\"".$kannel_param['bearerbox_host']."\"> ("._('Kannel specific').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Send SMS port')."</td><td>:</td><td><input type=text size=10 maxlength=10 name=up_sendsms_port value=\"".$kannel_param['sendsms_port']."\"> ("._('Kannel specific').")</td>
	    </tr>	    
	    <tr>
		<td>"._('playSMS web URL')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_playsms_web value=\"".$kannel_param['playsms_web']."\"> ("._('URL to playSMS, empty it to set it to base URL').")</td>
	    </tr>	    
	</table>	    
	    <!--
	    <p>"._('Note').":</br>
	    <p><input type=checkbox name=up_trn $checked> "._('Send SMS message without footer banner')."
	    -->
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>
	";
	echo $content;
	break;
    case "manage_save":
	$up_username = $_POST['up_username'];
	$up_password = $_POST['up_password'];
	$up_global_sender = $_POST['up_global_sender'];
	$up_bearerbox_host = $_POST['up_bearerbox_host'];
	$up_sendsms_port = $_POST['up_sendsms_port'];
	$up_playsms_web = ( $_POST['up_playsms_web'] ? $_POST['up_playsms_web'] : $http_path['base'] );
	$error_string = _('No changes has been made');
	if ($up_username && $up_password && $up_bearerbox_host && $up_sendsms_port)
	{
	    $db_query = "
		UPDATE "._DB_PREF_."_gatewayKannel_config 
		SET c_timestamp='".mktime()."',
		    cfg_username='$up_username',
		    cfg_password='$up_password',
		    cfg_global_sender='$up_global_sender',
		    cfg_bearerbox_host='$up_bearerbox_host',
		    cfg_sendsms_port='$up_sendsms_port',
		    cfg_playsms_web='$up_playsms_web'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = _('Gateway module configurations has been saved');
	    }
	}
	header ("Location: menu.php?inc=gateway_kannel&op=manage&err=".urlencode($error_string));
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='kannel'";
	$db_result = dba_query($db_query);
	$error_string = _('Gateway has been activated');
	header ("Location: menu.php?inc=gateway_kannel&op=manage&err=".urlencode($error_string));
	break;
}

?>
