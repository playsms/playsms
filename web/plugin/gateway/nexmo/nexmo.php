<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/nexmo/config.php";

$gw = gateway_get();

if ($gw == $nexmo_param['name']) {
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
} else {
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_nexmo&op=manage_activate\">"._('click here to activate')."</a>)";
}

$callback_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/plugin/gateway/nexmo/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://".$callback_url;

switch ($op) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage nexmo')."</h2>
			<p>
			<form action=index.php?app=menu&inc=gateway_nexmo&op=manage_save method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tbody>
				<tr><td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>nexmo</b> $status_active</td></tr>
				<tr><td>"._('Nexmo URL')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_url value=\"".$nexmo_param['url']."\"> (json)</td></tr>
				<tr><td>"._('API key')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_api_key value=\"".$nexmo_param['api_key']."\"></td></tr>
				<tr><td>"._('API secret')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_api_secret value=\"\"> ("._('Fill to change the API secret').")</td></tr>
				<tr><td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$nexmo_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td></tr>
				<tr><td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$nexmo_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td></tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>	
			"._('Notes').":<br />
			- "._('Your callback URL is')." <b>".$callback_url."</b><br />
			- "._('Your callback URL should be accessible from Nexmo')."<br />
			- "._('Nexmo will push DLR and incoming SMS to your callback URL')."<br />
			- "._('Nexmo is a bulk SMS provider').", <a href=\"http://www.nexmo.com\" target=\"_blank\">"._('free credits are available for testing purposes')."</a><br />";
		echo $content;
		break;
	case "manage_save":
		$up_url = $_POST['up_url'];
		$up_api_key = $_POST['up_api_key'];
		$up_api_secret = $_POST['up_api_secret'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_url && $up_api_key) {
			if ($up_api_secret) {
				$api_secret_change = "cfg_api_secret='$up_api_secret',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayNexmo_config 
				SET c_timestamp='".mktime()."',
				cfg_url='$up_url',
				cfg_api_key='$up_api_key',
				".$api_secret_change."
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_nexmo&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='nexmo'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_nexmo&op=manage");
		exit();
		break;
}

?>