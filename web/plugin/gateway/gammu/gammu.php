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

include $core_config['apps_path']['plug'] . "/gateway/gammu/config.php";

switch (_OP_) {
	case "manage":
		$tpl = [
			'name' => 'gammu',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage' => _('Manage'),
				'Gateway' => _('Gateway'),
				'Receiver number' => _('Receiver number'),
				'Spool folder' => _mandatory(_('Spool folder')),
				'Delivery reports' => _('Delivery reports'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'gateway_name' => $plugin_config['gammu']['name'],
				'sms_receiver' => $plugin_config['gammu']['sms_Receiver'],
				'path' => $plugin_config['gammu']['path'],
				'dlr' => _yesno('dlr', (bool) $plugin_config['gammu']['dlr']),
			]
		];
		_p(tpl_apply($tpl));
		break;

	case "manage_save":
		$sms_receiver = isset($_REQUEST['sms_receiver']) ? core_sanitize_sender($_REQUEST['sms_receiver']) : '';
		$path = isset($_REQUEST['path']) ? core_sanitize_path($_REQUEST['path']) : '';
		$dlr = isset($_REQUEST['dlr']) && (bool) $_REQUEST['dlr'] ? 1 : 0;
		if ($path) {
			$items = [
				'sms_receiver' => $sms_receiver,
				'path' => $path,
				'dlr' => $dlr,
			];
			if (registry_update(0, 'gateway', 'gammu', $items)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_gammu&op=manage'));
		exit();
}
