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

include $core_config['apps_path']['plug'] . "/gateway/generic/config.php";

switch (_OP_) {
	case "manage":
		$tpl = [
			'name' => 'generic',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage' => _('Manage'),
				'Gateway' => _('Gateway'),
				'Generic send SMS URL' => _mandatory(_('Generic send SMS URL')),
				'Callback URL' => _('Callback URL'),
				'Callback authcode' => _('Callback authcode'),
				'Callback server' => _('Callback server'),
				'API username' => _('API username'),
				'API password' => _('API password'),
				'Module sender ID' => _('Module sender ID'),
				'Module timezone' => _('Module timezone'),
				'Save' => _('Save'),
				'Notes' => _('Notes'),
				'HINT_CALLBACK_URL' => _hint(_('Empty callback URL to set default')),
				'HINT_CALLBACK_AUTHCODE' => _hint(_('Fill with at least 16 alphanumeric authentication code to secure callback URL')),
				'HINT_CALLBACK_SERVER' => _hint(_('Fill with server IP addresses (separated by comma) to limit access to callback URL')),
				'HINT_FILL_PASSWORD' => _hint(_('Fill to change the API password')),
				'HINT_MODULE_SENDER' => _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')),
				'HINT_TIMEZONE' => _hint(_('Eg: +0700 for UTC+7 or Jakarta/Bangkok timezone')),
				'CALLBACK_URL_ACCESSIBLE' => _('Your callback URL must be accessible from remote gateway'),
				'CALLBACK_AUTHCODE' => sprintf(_('You have to include callback authcode as query parameter %s'), ': <strong>authcode</strong>'),
				'CALLBACK_SERVER' => _('Your callback requests must be coming from callback server IP addresses'),
				'REMOTE_PUSH_DLR' => _('Remote gateway or callback server will push DLR and incoming SMS to your callback URL'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'gateway_name' => $plugin_config['generic']['name'],
				'url' => $plugin_config['generic']['url'],
				'callback_url' => gateway_callback_url('generic'),
				'callback_authcode' => $plugin_config['generic']['callback_authcode'],
				'callback_server' => $plugin_config['generic']['callback_server'],
				'api_username' => $plugin_config['generic']['api_username'],
				'module_sender' => $plugin_config['generic']['module_sender'],
				'datetime_timezone' => $plugin_config['generic']['datetime_timezone']
			]
		];
		_p(tpl_apply($tpl));
		break;

	case "manage_save":
		$url = isset($_REQUEST['url']) && $_REQUEST['url'] ? $_REQUEST['url'] : $plugin_config['generic']['default_url'];
		$callback_url = gateway_callback_url('generic');
		$callback_authcode = isset($_REQUEST['callback_authcode']) && core_sanitize_alphanumeric($_REQUEST['callback_authcode'])
			? core_sanitize_alphanumeric($_REQUEST['callback_authcode']) : '';
		$callback_server = isset($_REQUEST['callback_server']) ? preg_replace('/[^0-9a-zA-Z\.,_\-\/]+/', '', trim($_REQUEST['callback_server'])) : '';
		$callback_server = preg_replace('/[,]+/', ',', $callback_server);
		$api_username = $_REQUEST['api_username'];
		$api_password = $_REQUEST['api_password'];
		$module_sender = core_sanitize_sender($_REQUEST['module_sender']);
		$datetime_timezone = $_REQUEST['datetime_timezone'];
		if ($url) {
			$items = [
				'url' => $url,
				'callback_url' => $callback_url,
				'callback_authcode' => $callback_authcode,
				'callback_server' => $callback_server,
				'api_username' => $api_username,
				'module_sender' => $module_sender,
				'datetime_timezone' => $datetime_timezone
			];
			if ($api_password) {
				$items['api_password'] = $api_password;
			}
			if (registry_update(0, 'gateway', 'generic', $items)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_generic&op=manage'));
		exit();
}
