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

// gateway configuration in registry
$reg = gateway_get_registry('example');

// plugin configuration
$plugin_config['example'] = [
	'name' => 'example',
	'api_callback_url' => gateway_callback_url('example'),
	'api_url' => 'https://example.com/?account={API_ACCOUNT_ID}&token={API_TOKEN}&sender={SENDER_ID}',
	'api_account_id' => isset($reg['api_account_id']) ? core_sanitize_alphanumeric($reg['api_account_id']) : '',
	'api_token' => isset($reg['api_token']) ? core_sanitize_alphanumeric($reg['api_token']) : '',
	'module_sender' => isset($reg['module_sender']) ? core_sanitize_sender($reg['module_sender']) : '',
	'datetime_timezone' => isset($reg['datetime_timezone']) ? $reg['datetime_timezone'] : '',
];

// smsc configuration
$plugin_config['example']['_smsc_config_'] = [
	'api_account_id' => _('API Account ID'),
	'api_token' => _('API Token'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone'),
];
