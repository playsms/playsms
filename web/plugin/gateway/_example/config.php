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
$data = registry_search(0, 'gateway', 'example');

// plugin configuration
$plugin_config['example'] = [
	'name' => 'example',
	'api_url' => 'https://example.com/?account={API_ACCOUNT_ID}&token={API_TOKEN}&sender={SENDER_ID}',
	'api_account_id' => isset($data['gateway']['api_account_id']) ? $data['gateway']['api_account_id'] : '',
	'api_token' => isset($data['gateway']['api_token']) ? $data['gateway']['api_token'] : '',
	'sender_id' => isset($data['gateway']['sender_id']) ? $data['gateway']['sender_id'] : '',
];

// smsc configuration
$plugin_config['example']['_smsc_config_'] = [
	'api_account_id' => _('API Account ID'),
	'api_token' => _('API Token'),
	'sender_id' => _('Sender ID'),
];

// insert API callback URL to $plugin_config
$plugin_config['example']['api_callback_url'] = gateway_callback_url('example');
