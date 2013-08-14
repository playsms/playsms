<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/twilio/config.php";

$gw = gateway_get();

if ($gw == $core_config['plugin']['twilio']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

switch ($op) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage twilio')."</h2>
			<form action=index.php?app=menu&inc=gateway_twilio&op=manage_save method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tbody>
				<tr><td width=270>"._('Gateway name')."</td><td>twilio $status_active</td></tr>
				<tr><td>"._('Twilio URL')."</td><td>".$core_config['plugin']['twilio']['url']."</td></tr>
				<tr><td>"._('Callback URL')."</td><td><input type=text size=30 maxlength=250 name=up_callback_url value=\"".$core_config['plugin']['twilio']['callback_url']."\"></td></tr>
				<tr><td>"._('Account SID')."</td><td><input type=text size=30 maxlength=40 name=up_account_sid value=\"".$core_config['plugin']['twilio']['account_sid']."\"></td></tr>
				<tr><td>"._('Auth Token')."</td><td><input type=password size=30 maxlength=40 name=up_auth_token value=\"\"> "._hint('Fill to change the Auth Token')."</td></tr>
				<tr><td>"._('Module sender ID')."</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$core_config['plugin']['twilio']['global_sender']."\"> "._hint('Max. 16 numeric or 11 alphanumeric char. empty to disable')."</td></tr>
				<tr><td>"._('Module timezone')."</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$core_config['plugin']['twilio']['datetime_timezone']."\"> "._hint('Eg: +0700 for Jakarta/Bangkok timezone')."</td></tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<br />
			"._('Notes').":<br />
			- "._('Your callback URL should be accessible from twilio')."<br />
			- "._('twilio will push DLR and incoming SMS to your callback URL')."<br />
			- "._('twilio is a bulk SMS provider').", <a href=\"http://www.twilio.com\" target=\"_blank\">"._('free credits are available for testing purposes')."</a><br />";
		echo $content;
		break;
	case "manage_save":
		$up_callback_url = $_POST['up_callback_url'];
		$up_account_sid = $_POST['up_account_sid'];
		$up_auth_token = $_POST['up_auth_token'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_account_sid) {
			if ($up_auth_token) {
				$auth_token_change = "cfg_auth_token='$up_auth_token',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayTwilio_config 
				SET c_timestamp='".mktime()."',
				cfg_callback_url='$up_callback_url',
				cfg_account_sid='$up_account_sid',
				".$auth_token_change."
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_twilio&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='twilio'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_twilio&op=manage");
		exit();
		break;
}

?>