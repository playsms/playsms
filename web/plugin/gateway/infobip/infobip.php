<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/infobip/config.php";

$gw = gateway_get();

if ($gw == $infobip_param['name'])
{
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_infobip&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
	case "manage":

	        /* Handle DLR options config
		   &nopush=1 ie all DLR-s with nopush=1 wilt not be pushed. and will be available for pull.
		   &nopush=0 ie all DLR-s with nopush=0 will be pushed, as usual.
		*/
		if( $infobip_param['dlr_nopush'] == '0' ) {
		  $dlr_push_selected = "checked";
		  $dlr_pull_selected = "";
		} else {
		  $dlr_push_selected = "";
		  $dlr_pull_selected = "checked";
		}
		
	        $up_dlr_box = "<input type='radio' name='up_nopush' value='0' ".$dlr_push_selected."> Push request";
	        $up_dlr_box .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	        $up_dlr_box .= "<input type='radio' name='up_nopush' value='1' ".$dlr_pull_selected."> Pull request";
	        // end of Handle DLR options config

		if ($err)
		{
			$content = "<div class=error_string>$err</div>";
		}
		$callback_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?app=call&cat=gateway&plugin=infobip&access=callback";
		$callback_url = str_replace("//", "/", $callback_url);
		$callback_url = "http://".$callback_url;
		$dlr_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?app=call&cat=gateway&plugin=infobip&access=dlr";
		$dlr_url = str_replace("//", "/", $dlr_url);
		$dlr_url = "http://".$dlr_url;
		$content .= "
	    <h2>"._('Manage infobip')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_infobip&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>infobip</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$infobip_param['username']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_sender value=\"".$infobip_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$infobip_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <tr>
		<td>"._('Infobip API URL')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_send_url value=\"".$infobip_param['send_url']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>
            <tr>
                <td>"._('Delivery Report')."</td><td>:</td><td>$up_dlr_box</td>
            </tr>
	    <tr>
		<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$infobip_param['additional_param']."\"></td>
	    </tr>
	    <!--
	    <tr>
		<td>"._('Infobip incoming path')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_incoming_path value=\"".$infobip_param['incoming_path']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>	    
	    -->
	</table>	    
	
	    <p>"._('Note').":<br>
	    - "._('Your callback URL is')." <b>$callback_url</b><br>
	    - "._('Your dlr URL is')." <b>$dlr_url</b><br>
	    - "._('Infobip is a bulk SMS provider').", <a href=\"http://www.infobip.com\" target=\"_blank\">"._('Create an account to send SMS')."</a>
	    <p><input type=submit class=button value="._('Save').">
	    </form>
	";
		echo $content;
		break;

	case "manage_save":

		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_sender = $_POST['up_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_send_url = $_POST['up_send_url'];
		$up_incoming_path = $_POST['up_incoming_path'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_nopush = $_POST['up_nopush']; 
		$error_string = _('No changes has been made');

		if ($up_username && $up_send_url)
		{
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayInfobip_config 
		SET c_timestamp='".mktime()."',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_sender='$up_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_send_url='$up_send_url',
		    cfg_additional_param='$up_additional_param',
		    cfg_incoming_path='$up_incoming_path',
		    cfg_dlr_nopush='$up_nopush'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$error_string = _('Gateway module configurations has been saved');
			}
		}
		header ("Location: index.php?app=menu&inc=gateway_infobip&op=manage&err=".urlencode($error_string));
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='infobip'";
		$db_result = dba_query($db_query);
		$error_string = _('Gateway has been activated');
		header ("Location: index.php?app=menu&inc=gateway_infobip&op=manage&err=".urlencode($error_string));
		break;
}

?>
