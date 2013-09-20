<?php
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/messagemedia/config.php";

$gw = gateway_get();

if ($gw == $messagemedia_param['name'])
{
    $status_active = "(<b><font color=green>"._('Active')."</font></b>)";
}
else
{
    $status_active =  "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_messagemedia&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op)
{
    case "manage":
	if ($err)
	{
	    $content = "<div class=error_string>$err</div>";
	}
	$content .= "
	    <h2>"._('Manage messagemedia')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_messagemedia&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Gateway name')."</td><td width=5>:</td><td><b>messagemedia</b> $status_active</td>
	    </tr>
	    <tr>
		<td>"._('API ID')."</td><td>:</td><td><input type=text size=20 maxlength=20 name=up_api_id value=\"".$messagemedia_param['api_id']."\"></td>
	    </tr>	    
	    <tr>
		<td>"._('Username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$messagemedia_param['username']."\"></td>
	    </tr>
	    <tr>
		<td>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> ("._('Fill to change the password').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Delay')."</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_delay value=\"".$messagemedia_param['delay']."\"> ("._('How many seconds in the future the message should be delivered').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$messagemedia_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <tr>
		<td>"._('Messagemedia API URL')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_send_url value=\"".$messagemedia_param['send_url']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>
	    <tr>
		<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$messagemedia_param['additional_param']."\"></td>
	    </tr>
	    <!--
	    <tr>
		<td>"._('Messagemedia incoming path')."</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_incoming_path value=\"".$messagemedia_param['incoming_path']."\"> ("._('No trailing slash')." \"/\")</td>
	    </tr>	    
	    -->
	</table>	    
	
	    <p>"._('Note').":<br>
	    - "._('Your callback URL is')." <b>http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?app=call&cat=gateway&plugin=messagemedia&access=callback</b><br>
	    - "._('Messagemedia is a bulk SMS provider').", <a href=\"http://www.dpbolvw.net/click-4099975-10807974?sid=gwmodtext\" target=\"_blank\">"._('free credits are available for testing purposes')."</a><img src=\"http://www.lduhtrp.net/image-4099975-10807974\" width=\"1\" height=\"1\" border=\"0\"/>
	    <!-- <p><input type=checkbox name=up_trn $checked> "._('Send SMS message without footer banner')." -->
	    <p><input type=submit class=button value="._('Save').">
	    </form>
	";
	echo $content;
	break;
    case "manage_save":
	$up_api_id = $_POST['up_api_id'];
	$up_username = $_POST['up_username'];
	$up_password = $_POST['up_password'];
	$up_delay = $_POST['up_delay'];
	$up_global_timezone = $_POST['up_global_timezone'];
	$up_send_url = $_POST['up_send_url'];
	$up_incoming_path = $_POST['up_incoming_path'];
	$up_additional_param = ( $_POST['up_additional_param'] ? $_POST['up_additional_param'] : "deliv_ack=1&callback=3" );
	$error_string = _('No changes has been made');
	if ($up_api_id && $up_username && $up_send_url)
	{
	    if ($up_password) {
		$password_change = "cfg_password='$up_password',";
	    }
	    $db_query = "
		UPDATE "._DB_PREF_."_gatewayMessagemedia_config 
		SET c_timestamp='".mktime()."',
		    cfg_api_id='$up_api_id',
		    cfg_username='$up_username',
		    ".$password_change."
		    cfg_delay='$up_delay',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_send_url='$up_send_url',
		    cfg_additional_param='$up_additional_param',
		    cfg_incoming_path='$up_incoming_path'
	    ";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = _('Gateway module configurations has been saved');
	    }
	}
	header ("Location: index.php?app=menu&inc=gateway_messagemedia&op=manage&err=".urlencode($error_string));
	break;
    case "manage_activate":
	$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='messagemedia'";
	$db_result = dba_query($db_query);
	$error_string = _('Gateway has been activated');
	header ("Location: index.php?app=menu&inc=gateway_messagemedia&op=manage&err=".urlencode($error_string));
	break;
}

?>
