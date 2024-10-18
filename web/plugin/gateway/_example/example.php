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

include $core_config['apps_path']['plug'] . "/gateway/example/config.php";

switch (_OP_) {
	case "manage":
		$tpl = [
			'name' => 'example',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage' => _('Manage'),
				'Gateway' => _('Gateway'),
				'API Account ID' => _mandatory(_('API Account ID')),
				'API Token' => _('API Token'),
				'Module sender ID' => _('Module sender ID'),
				'Module timezone' => _('Module timezone'),
				'Save' => _('Save'),
				'Notes' => _('Notes'),
				'HINT_FILL_PASSWORD' => _hint(_('Fill to change the API Token')),
				'HINT_MODULE_SENDER' => _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')),
				'API_CALLBACK_URL_IS' => _('Your current API Callback URL is'),
				'MUST_ACCESSIBLE' => _('Your API Callback URL must be accessible by API server'),
				'PUSH_DLR' => _('API server may push DLR and incoming SMS to your API Callback URL'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'gateway_name' => $plugin_config['example']['name'],
				'api_callback_url' => $plugin_config['example']['api_callback_url'],
				'api_account_id' => $plugin_config['example']['api_account_id'],
				'module_sender' => $plugin_config['example']['module_sender'],
				'datetime_timezone' => $plugin_config['example']['datetime_timezone'],
			]
		];
		_p(tpl_apply($tpl));
		break;

	case "manage_save":
		$api_account_id = core_sanitize_alphanumeric($_REQUEST['api_account_id']);
		$api_token = core_sanitize_alphanumeric($_REQUEST['api_token']);
		$module_sender = core_sanitize_sender($_REQUEST['module_sender']);
		$datetime_timezone = $_REQUEST['datetime_timezone'];
		if ($api_account_id) {
			$items = [
				'api_account_id' => $api_account_id,
				'module_sender' => $module_sender,
				'datetime_timezone' => $datetime_timezone,
			];
			if ($api_token) {
				$items['api_token'] = $api_token;
			}
			if (registry_update(0, 'gateway', 'example', $items)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_example&op=manage'));
		exit();
}
