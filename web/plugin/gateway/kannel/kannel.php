<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/kannel/config.php";

if ($gateway_module == $kannel_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_kannel&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
	case "manage":
		if ($err)
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage kannel')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_kannel&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>kannel</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$kannel_param['username']."\"></td>
	    </tr>	    
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
	    </tr>
	    <tr>
		<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_global_sender value=\"".$kannel_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$kannel_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <tr>
		<td>"._('Bearerbox hostname or IP')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_bearerbox_host value=\"".$kannel_param['bearerbox_host']."\"> ("._('Kannel specific').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Send SMS port')."</td><td>:</td><td><input type=text size=10 maxlength=10 name=up_sendsms_port value=\"".$kannel_param['sendsms_port']."\"> ("._('Kannel specific').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$kannel_param['additional_param']."\"></td>
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
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_bearerbox_host = $_POST['up_bearerbox_host'];
		$up_sendsms_port = $_POST['up_sendsms_port'];
		$up_playsms_web = ( $_POST['up_playsms_web'] ? $_POST['up_playsms_web'] : $http_path['base'] );
		$up_additional_param = ( $_POST['up_additional_param'] ? $_POST['up_additional_param'] : "smsc=default" );
		$error_string = _('No changes has been made');
		if ($up_username && $up_bearerbox_host && $up_sendsms_port)
		{
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayKannel_config 
		SET c_timestamp='".mktime()."',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_global_sender='$up_global_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_bearerbox_host='$up_bearerbox_host',
		    cfg_sendsms_port='$up_sendsms_port',
		    cfg_playsms_web='$up_playsms_web',
		    cfg_additional_param='$up_additional_param'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$error_string = _('Gateway module configurations has been saved');
			}
		}
		header ("Location: index.php?app=menu&inc=gateway_kannel&op=manage&err=".urlencode($error_string));
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='kannel'";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway has been activated');
		header ("Location: index.php?app=menu&inc=gateway_kannel&op=manage&err=".urlencode($error_string));
		break;
}

?>
