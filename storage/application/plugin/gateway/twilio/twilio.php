<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

include $core_config['apps_path']['plug'] . "/gateway/twilio/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2 class=page-header-title>" . _('Manage twilio') . "</h2>
			<form action=index.php?app=main&inc=gateway_twilio&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tbody>
				<tr><td class=playsms-label-sizer>" . _('Gateway name') . "</td><td>twilio</td></tr>
				<tr><td>" . _('Twilio URL') . "</td><td>" . $plugin_config['twilio']['url'] . "</td></tr>
				<tr><td>" . _('Callback URL') . "</td><td><input type=text maxlength=250 name=up_callback_url value=\"" . $plugin_config['twilio']['callback_url'] . "\"></td></tr>
				<tr><td>" . _mandatory(_('Account SID')) . "</td><td><input type=text maxlength=40 name=up_account_sid value=\"" . $plugin_config['twilio']['account_sid'] . "\"></td></tr>
				<tr><td>" . _('Auth Token') . "</td><td><input type=password maxlength=40 name=up_auth_token value=\"\"> " . _hint(_('Fill to change the Auth Token')) . "</td></tr>
				<tr><td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['twilio']['module_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td></tr>
				<tr><td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $plugin_config['twilio']['datetime_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td></tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			<p class='lead'>" . _('Notes') . ":</p>
			<ul>
				<li>" . _('Your callback URL should be accessible from twilio') . "</li>
				<li>" . _('twilio will push DLR and incoming SMS to your callback URL') . "</li>
			</ul>";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;

	case "manage_save":
		$up_callback_url = $_POST['up_callback_url'];
		$up_account_sid = $_POST['up_account_sid'];
		$up_auth_token = $_POST['up_auth_token'];
		$up_module_sender = $_POST['up_module_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		if ($up_account_sid) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayTwilio_config
				SET c_timestamp='" . time() . "',
				cfg_callback_url=?,
				cfg_account_sid=?,
				cfg_module_sender=?,
				cfg_datetime_timezone=?";
			$db_argv = [
				$up_callback_url,
				$up_account_sid,
				$up_module_sender,
				$up_global_timezone
			];
			if (dba_affected_rows($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');

				if ($up_auth_token) {
					$db_query = "UPDATE " . _DB_PREF_ . "_gatewayTwilio_config SET cfg_auth_token=?";
					if (dba_affected_rows($db_query, [$up_auth_token]) === 0) {
						$_SESSION['dialog']['info'][] = _('Fail to update auth token');
					}
				}
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_twilio&op=manage'));
		exit();
}
