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

include $core_config['apps_path']['plug'] . "/gateway/jasmin/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		$tpl = array(
			'name' => 'jasmin',
			'vars' => array(
				'DIALOG_DISPLAY' => $error_content,
				'Manage jasmin' => _('Manage jasmin'),
				'Gateway name' => _('Gateway name'),
				'Jasmin send SMS URL' => _mandatory(_('Jasmin send SMS URL')),
				'Callback URL' => _('Callback URL'),
				'API username' => _mandatory(_('API username')),
				'API password' => _('API password'),
				'Module sender ID' => _('Module sender ID'),
				'Module timezone' => _('Module timezone'),
				'Save' => _('Save'),
				'Notes' => _('Notes'),
				'HINT_CALLBACK_URL' => _hint(_('Empty callback URL to set default')),
				'HINT_FILL_PASSWORD' => _hint(_('Fill to change the API password')),
				'HINT_MODULE_SENDER' => _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')),
				'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
				'CALLBACK_URL_IS' => _('Your current callback URL is'),
				'CALLBACK_URL_ACCESSIBLE' => _('Your callback URL should be accessible from Jasmin'),
				'JASMIN_PUSH_DLR' => _('Jasmin will push DLR and incoming SMS to your callback URL'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'status_active' => $status_active,
				'jasmin_param_url' => $plugin_config['jasmin']['url'],
				'jasmin_param_callback_url' => $plugin_config['jasmin']['callback_url'],
				'jasmin_param_api_username' => $plugin_config['jasmin']['api_username'],
				'jasmin_param_module_sender' => $plugin_config['jasmin']['module_sender'],
				'jasmin_param_datetime_timezone' => $plugin_config['jasmin']['datetime_timezone'] 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "manage_save":
		$up_url = ($_REQUEST['up_url'] ? $_REQUEST['up_url'] : $plugin_config['jasmin']['default_url']);
		$up_callback_url = ($_REQUEST['up_callback_url'] ? $_REQUEST['up_callback_url'] : $plugin_config['jasmin']['default_callback_url']);
		$up_api_username = $_REQUEST['up_api_username'];
		$up_api_password = $_REQUEST['up_api_password'];
		$up_module_sender = $_REQUEST['up_module_sender'];
		$up_datetime_timezone = $_REQUEST['up_datetime_timezone'];
		if ($up_url && $up_api_username) {
			$items = array(
				'url' => $up_url,
				'callback_url' => $up_callback_url,
				'api_username' => $up_api_username,
				'module_sender' => $up_module_sender,
				'datetime_timezone' => $up_datetime_timezone 
			);
			if ($up_api_password) {
				$items['api_password'] = $up_api_password;
			}
			if (registry_update(0, 'gateway', 'jasmin', $items)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_jasmin&op=manage'));
		exit();
		break;
}
