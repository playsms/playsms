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

$callback_url = _HTTP_PATH_BASE_ . "/index.php?app=call&cat=gateway&plugin=infobip&access=callback";

$dlr_url = _HTTP_PATH_BASE_ . "/index.php?app=call&cat=gateway&plugin=infobip&access=dlr";

switch (_OP_) {
	case "manage":
		$content .= _dialog() . "
			<h2 class=page-header-title>" . _('Manage infobip') . "</h2>
			<form action=index.php?app=main&inc=gateway_infobip&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>" . _('Gateway name') . "</td><td>infobip</td>
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
			<p class='lead'>" . _('Notes') . ":</p>
			<ul>
				<li>" . _('Your callback URL is') . " <strong>" . $callback_url . "</strong></li>
				<li>" . _('Your DLR URL is') . " <strong>" . $dlr_url . "</strong></li>
				<li>" . _('Your callback URL should be accessible from Infobip') . "</li>
				<li>" . _('Infobip will push DLR and incoming SMS to above URL') . "</li>
			</ul>";
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
				SET c_timestamp='" . time() . "',cfg_username=?,cfg_module_sender=?,cfg_datetime_timezone=?,
				cfg_send_url=?,cfg_additional_param=?,cfg_dlr_nopush=?";
			$db_argv = [
				$up_username,
				$up_module_sender,
				$up_global_timezone,
				$up_send_url,
				$up_additional_param,
				$up_nopush
			];
			if (dba_affected_rows($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');

				if ($up_password) {
					$db_query = "UPDATE " . _DB_PREF_ . "_gatewayInfobip_config SET cfg_password=?";
					if (dba_affected_rows($db_query, [$up_password]) === 0) {
						$_SESSION['dialog']['info'][] = _('Fail to update password');
					}
				}
			} else {
				$_SESSION['dialog']['info'][] = _('No changes has been made');
			}
		}
		header("Location: index.php?app=main&inc=gateway_infobip&op=manage");
		exit();
}
