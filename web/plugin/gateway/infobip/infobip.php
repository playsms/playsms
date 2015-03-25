<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
}

$callback_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/infobip/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://" . $callback_url;

$dlr_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/infobip/dlr.php";
$dlr_url = str_replace("//", "/", $dlr_url);
$dlr_url = "http://" . $dlr_url;

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage infobip') . "</h2>
			<form action=index.php?app=main&inc=gateway_infobip&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('Gateway name') . "</td><td>infobip</td>
			</tr>
			<tr>
				<td>" . _('Username') . "</td><td><input type=text maxlength=30 name=up_username value=\"" . $plugin_config['infobip']['username'] . "\"></td>
			</tr>
			<tr>
				<td>" . _('Password') . "</td><td><input type=password maxlength=30 name=up_password value=\"\"> " . _hint(_('Fill to change the password')) . "</td>
			</tr>
			<tr>
				<td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['infobip']['module_sender'] . "\">" . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
			</tr>
			<tr>
				<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $plugin_config['infobip']['datetime_timezone'] . "\">" . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
			</tr>
			<tr>
				<td>" . _('Infobip API URL') . "</td><td><input type=text maxlength=250 name=up_send_url value=\"" . $plugin_config['infobip']['send_url'] . "\">" . _hint(_('No trailing slash') . " \"/\"") . "</td>
			</tr>
			<tr>
				<td>" . _('Additional URL parameter') . "</td><td><input type=text maxlength=250 name=up_additional_param value=\"" . $plugin_config['infobip']['additional_param'] . "\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<br />
			" . _('Notes') . ":<br />
			- " . _('Your callback URL is') . " " . $callback_url . "<br />
			- " . _('Your DLR URL is') . " " . $dlr_url . "<br />
			- " . _('Your callback URL should be accessible from Infobip') . "<br />
			- " . _('Infobip will push DLR and incoming SMS to above URL') . "<br />
			- " . _('Infobip is a bulk SMS provider') . ", <a href=\"http://www.infobip.com\" target=\"_blank\">" . _('create an account to send SMS') . "</a>";
		$content .= '<p>' . _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_module_sender = $_POST['up_module_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_send_url = $_POST['up_send_url'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_nopush = '0';
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_username && $up_send_url) {
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayInfobip_config
				SET c_timestamp='" . mktime() . "',
				cfg_username='$up_username',
				" . $password_change . "
				cfg_module_sender='$up_module_sender',
				cfg_datetime_timezone='$up_global_timezone',
				cfg_send_url='$up_send_url',
				cfg_additional_param='$up_additional_param',
				cfg_dlr_nopush='$up_nopush'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=main&inc=gateway_infobip&op=manage");
		break;
}
