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

include $core_config['apps_path']['plug'] . "/gateway/uplink/config.php";

switch (_OP_) {
	case "manage":
		if ($plugin_config['uplink']['try_disable_footer']) {
			$selected['yes'] = 'selected';
		} else {
			$selected['no'] = 'selected';
		}
		$option_try_disable_footer = "<option value=\"1\" " . $selected['yes'] . ">" . _('yes') . "</option>";
		$option_try_disable_footer .= "<option value=\"0\" " . $selected['no'] . ">" . _('no') . "</option>";
		
		$content = _dialog() . "
			<h2>" . _('Manage uplink') . "</h2>
			<form action=index.php?app=main&inc=gateway_uplink&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>uplink</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Master URL')) . "</td><td><input type=text maxlength=250 name=up_master value=\"" . $plugin_config['uplink']['master'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Additional URL parameter') . "</td><td><input type=text maxlength=250 name=up_additional_param value=\"" . $plugin_config['uplink']['additional_param'] . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Webservice username')) . "</td><td><input type=text maxlength=30 name=up_username value=\"" . $plugin_config['uplink']['username'] . "\"></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Webservice token')) . "</td><td><input type=text maxlength=32 name=up_token value=\"" . $plugin_config['uplink']['token'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Try to disable SMS footer on master') . "</td><td><select name=up_try_disable_footer>" . $option_try_disable_footer . "</select></td>
				</tr>
				<tr>
					<td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['uplink']['module_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
				</tr>
				<tr>
					<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_datetime_timezone value=\"" . $plugin_config['uplink']['datetime_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>" . _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_master = $_POST['up_master'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_username = $_POST['up_username'];
		$up_token = $_POST['up_token'];
		$up_module_sender = $_POST['up_module_sender'];
		$up_datetime_timezone = $_POST['up_datetime_timezone'];
		$up_try_disable_footer = $_POST['up_try_disable_footer'];
		if ($up_master && $up_username && $up_token) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayUplink_config
				SET c_timestamp='" . mktime() . "',
				cfg_master='$up_master',
				cfg_additional_param='$up_additional_param',
				cfg_username='$up_username',
				cfg_token='$up_token',
				cfg_module_sender='$up_module_sender',
				cfg_datetime_timezone='$up_datetime_timezone',
				cfg_try_disable_footer='$up_try_disable_footer'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Unable to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_uplink&op=manage'));
		exit();
		break;
}
