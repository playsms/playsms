<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
}

$callback_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/clickatell/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://" . $callback_url;

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage clickatell') . "</h2>
			<form action=index.php?app=main&inc=gateway_clickatell&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('Gateway name') . "</td><td>clickatell</td>
			</tr>
			<tr>
				<td>" . _('API ID') . "</td><td><input type=text maxlength=20 name=up_api_id value=\"" . $plugin_config['clickatell']['api_id'] . "\"></td>
			</tr>
			<tr>
				<td>" . _('Username') . "</td><td><input type=text maxlength=30 name=up_username value=\"" . $plugin_config['clickatell']['username'] . "\"></td>
			</tr>
			<tr>
				<td>" . _('Password') . "</td><td><input type=password maxlength=30 name=up_password value=\"\"> " . _hint(_('Fill to change the password')) . "</td>
			</tr>
			<tr>
				<td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['clickatell']['module_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
			</tr>
			<tr>
				<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $plugin_config['clickatell']['datetime_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
			</tr>
			<tr>
				<td>" . _('Clickatell API URL') . "</td><td><input type=text maxlength=250 name=up_send_url value=\"" . $plugin_config['clickatell']['send_url'] . "\"> " . _hint(_('No trailing slash') . " \"/\"") . "</td>
			</tr>
			<tr>
				<td>" . _('Additional URL parameter') . "</td><td><input type=text maxlength=250 name=up_additional_param value=\"" . $plugin_config['clickatell']['additional_param'] . "\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<br />
			" . _('Notes') . ":<br />
			- " . _('Your callback URL is') . " " . $callback_url . "<br />
			- " . _('Your callback URL should be accessible from Clickatell') . "<br />
			- " . _('Clickatell will push DLR and incoming SMS to your callback URL') . "<br />
			- " . _('Clickatell is a bulk SMS provider') . ", <a href=\"https://www.clickatell.com/register/\" target=\"_blank\">" . _('free credits are available for testing purposes') . "</a><br />";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_api_id = $_POST['up_api_id'];
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_module_sender = $_POST['up_module_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_send_url = $_POST['up_send_url'];
		$up_incoming_path = $_POST['up_incoming_path'];
		$up_additional_param = ($_POST['up_additional_param'] ? $_POST['up_additional_param'] : "deliv_ack=1&callback=3");
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_api_id && $up_username && $up_send_url) {
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayClickatell_config
				SET c_timestamp='" . mktime() . "',
				cfg_api_id='$up_api_id',
				cfg_username='$up_username',
				" . $password_change . "
				cfg_module_sender='$up_module_sender',
				cfg_datetime_timezone='$up_global_timezone',
				cfg_send_url='$up_send_url',
				cfg_additional_param='$up_additional_param',
				cfg_incoming_path='$up_incoming_path'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=main&inc=gateway_clickatell&op=manage");
		exit();
		break;
}
