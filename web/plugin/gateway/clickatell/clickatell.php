<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/clickatell/config.php";

$gw = gateway_get();

if ($gw == $clickatell_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_clickatell&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$callback_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?app=call&cat=gateway&plugin=clickatell&access=callback";
		$callback_url = str_replace("//", "/", $callback_url);
		$callback_url = "http://".$callback_url;
		$content .= "
	    <h2>"._('Manage clickatell')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_clickatell&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>clickatell</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('API ID')."</td><td>:</td><td><input type=text size=20 maxlength=20 name=up_api_id value=\"".$clickatell_param['api_id']."\"></td>
	    </tr>	    
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$clickatell_param['username']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=30 maxlength=16 name=up_sender value=\"".$clickatell_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$clickatell_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <tr>
		<td>"._('Clickatell API URL')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_send_url value=\"".$clickatell_param['send_url']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>
	    <tr>
		<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$clickatell_param['additional_param']."\"></td>
	    </tr>
	    <!--
	    <tr>
		<td>"._('Clickatell incoming path')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_incoming_path value=\"".$clickatell_param['incoming_path']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>	    
	    -->
	</table>	    
	
	    <p>"._('Note').":<br>
	    - "._('Your callback URL is')." <b>$callback_url</b><br>
	    - "._('Clickatell is a bulk SMS provider').", <a href=\"http://www.dpbolvw.net/click-4099975-10807974?sid=gwmodtext\" target=\"_blank\">"._('free credits are available for testing purposes')."</a><img src=\"http://www.lduhtrp.net/image-4099975-10807974\" width=\"1\" height=\"1\" border=\"0\"/>
	    <p><input type=submit class=button value="._('Save').">
	    </form>
	";
		echo $content;
		break;
	case "manage_save":
		$up_api_id = $_POST['up_api_id'];
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_sender = $_POST['up_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_send_url = $_POST['up_send_url'];
		$up_incoming_path = $_POST['up_incoming_path'];
		$up_additional_param = ( $_POST['up_additional_param'] ? $_POST['up_additional_param'] : "deliv_ack=1&callback=3" );
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_api_id && $up_username && $up_send_url)
		{
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayClickatell_config 
		SET c_timestamp='".mktime()."',
		    cfg_api_id='$up_api_id',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_sender='$up_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_send_url='$up_send_url',
		    cfg_additional_param='$up_additional_param',
		    cfg_incoming_path='$up_incoming_path'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_clickatell&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='clickatell'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_clickatell&op=manage");
		exit();
		break;
}

?>