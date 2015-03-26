<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/twilio/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>"._('Manage twilio')."</h2>
			<form action=index.php?app=main&inc=gateway_twilio&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tbody>
				<tr><td class=label-sizer>"._('Gateway name')."</td><td>twilio</td></tr>
				<tr><td>"._('Twilio URL')."</td><td>".$plugin_config['twilio']['url']."</td></tr>
				<tr><td>"._('Callback URL')."</td><td><input type=text maxlength=250 name=up_callback_url value=\"".$plugin_config['twilio']['callback_url']."\"></td></tr>
				<tr><td>"._('Account SID')."</td><td><input type=text maxlength=40 name=up_account_sid value=\"".$plugin_config['twilio']['account_sid']."\"></td></tr>
				<tr><td>"._('Auth Token')."</td><td><input type=password maxlength=40 name=up_auth_token value=\"\"> "._hint(_('Fill to change the Auth Token'))."</td></tr>
				<tr><td>"._('Module sender ID')."</td><td><input type=text maxlength=16 name=up_module_sender value=\"".$plugin_config['twilio']['module_sender']."\"> "._hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable'))."</td></tr>
				<tr><td>"._('Module timezone')."</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$plugin_config['twilio']['datetime_timezone']."\"> "._hint(_('Eg: +0700 for Jakarta/Bangkok timezone'))."</td></tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<br />
			"._('Notes').":<br />
			- "._('Your callback URL should be accessible from twilio')."<br />
			- "._('twilio will push DLR and incoming SMS to your callback URL')."<br />
			- "._('twilio is a bulk SMS provider').", <a href=\"http://www.twilio.com\" target=\"_blank\">"._('free credits are available for testing purposes')."</a><br />";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_callback_url = $_POST['up_callback_url'];
		$up_account_sid = $_POST['up_account_sid'];
		$up_auth_token = $_POST['up_auth_token'];
		$up_module_sender = $_POST['up_module_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['dialog']['info'][] = _('No changes have been made');
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
				cfg_module_sender='$up_module_sender',
				cfg_datetime_timezone='$up_global_timezone'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_twilio&op=manage'));
		exit();
		break;
}
