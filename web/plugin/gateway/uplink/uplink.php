<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/uplink/config.php";

if ($gateway_module == $uplink_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_uplink&op=manage_activate\">"._('click here to activate')."</a>)";
}


switch ($op)
{
	case "manage":
		if ($err)
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage uplink')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_uplink&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>uplink</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('Master URL')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_master value=\"".$uplink_param['master']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$uplink_param['additional_param']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$uplink_param['username']."\"></td>
	    </tr>	    	    
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module sender')."</td><td>:</td><td><input type=text size=11 maxlength=11 name=up_global_sender value=\"".$uplink_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>
	    <tr>
		<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$uplink_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <!--
	    <tr>
		<td>"._('Uplink incoming path')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_incoming_path value=\"".$uplink_param['path']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>	    	    
	    -->
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
		$up_master = $_POST['up_master'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_incoming_path = $_POST['up_incoming_path'];
		$error_string = _('No changes has been made');
                
                /** Fixme Edward, Remove $up_incoming_path From IF **/
		/**if ($up_master && $up_username && $up_incoming_path)**/
                if ($up_master && $up_username)
		{
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			/*$db_query = "
		UPDATE "._DB_PREF_."_gatewayUplink_config 
		SET c_timestamp='".mktime()."',
		    cfg_master='$up_master',
		    cfg_additional_param='$up_additional_param',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_global_sender='$up_global_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_incoming_path='$up_incoming_path'
	    ";*/
                        $db_query = "
		UPDATE "._DB_PREF_."_gatewayUplink_config 
		SET c_timestamp='".mktime()."',
		    cfg_master='$up_master',
		    cfg_additional_param='$up_additional_param',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_global_sender='$up_global_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_incoming_path='$up_incoming_path'";
                        /**End Of Fixing**/
                        
			if (@dba_affected_rows($db_query))
			{
				$error_string = _('Gateway module configurations has been saved');
			}
		}
		header ("Location: index.php?app=menu&inc=gateway_uplink&op=manage&err=".urlencode($error_string));
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='uplink'";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway has been activated');
		header ("Location: index.php?app=menu&inc=gateway_uplink&op=manage&err=".urlencode($error_string));
		break;
}

?>
