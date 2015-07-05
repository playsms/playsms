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

include $core_config['apps_path']['plug'] . "/gateway/smstools/config.php";

switch (_OP_) {
	case "manage":
		$content .= _dialog() . "
			<h2>" . _('Manage smstools') . "</h2>
			<form action=index.php?app=main&inc=gateway_smstools&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
					<tr>
						<td class=label-sizer>" . _('Gateway name') . "</td><td>smstools</td>
					</tr>
					<tr>
						<td>" . _('Default default_queue directory') . "</td><td><input type=text name=up_default_queue value=\"" . $plugin_config['smstools']['default_queue'] . "\"></td>
					</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<p>" . _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_default_queue = trim(core_sanitize_path($_POST['up_default_queue']));
		if (!$up_default_queue) {
			$up_default_queue = "/var/spool/sms";
		}
		
		$items = array(
			'default_queue' => $up_default_queue 
		);
		registry_update(0, 'gateway', 'smstools', $items);
		
		$_SESSION['dialog']['info'][] = _('Changes have been made');
		header("Location: " . _u('index.php?app=main&inc=gateway_smstools&op=manage'));
		exit();
		break;
}
