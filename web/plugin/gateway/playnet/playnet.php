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

include $core_config['apps_path']['plug'] . "/gateway/playnet/config.php";

switch (_OP_) {
	case "manage":
		$content = _dialog() . "
			<h2>" . _('Manage playnet') . "</h2>
			<form action=index.php?app=main&inc=gateway_playnet&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>playnet</td>
				</tr>
				<tr>
					<td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['playnet']['module_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
				</tr>
				<tr>
					<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_module_timezone value=\"" . $plugin_config['playnet']['module_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>" . _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$items = array(
			'module_sender' => $_POST['up_module_sender'],
			'module_timezone' => $_POST['up_module_timezone'] 
		);
		if ($_POST['up_password']) {
			$items['password'] = $_POST['up_password'];
		}
		if ($_POST['up_admin_password']) {
			$items['admin_password'] = $_POST['up_admin_password'];
		}
		registry_update(0, 'gateway', 'playnet', $items);
		$_SESSION['dialog']['info'][] = _('Changes have been made');
		header("Location: " . _u('index.php?app=main&inc=gateway_playnet&op=manage'));
		exit();
		break;
}
