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
$data = registry_search(0, 'gateway', 'jasmin');

// plugin configuration
$plugin_config['jasmin'] = $data['gateway']['jasmin'];
$plugin_config['jasmin']['name'] = 'jasmin';
$plugin_config['jasmin']['default_url'] = 'https://127.0.0.1:1401/send';
$plugin_config['jasmin']['default_callback_url'] = gateway_callback_url('jasmin');
if (!trim($plugin_config['jasmin']['url'])) {
	$plugin_config['jasmin']['url'] = $plugin_config['jasmin']['default_url'];
}
if (!trim($plugin_config['jasmin']['callback_url'])) {
	$plugin_config['jasmin']['callback_url'] = $plugin_config['jasmin']['default_callback_url'];
}

// smsc configuration
$plugin_config['jasmin']['_smsc_config_'] = array(
	'url' => _('Jasmin send SMS URL'),
	'callback_url' => _('Callback URL'),
	'api_username' => _('API username'),
	'api_password' => _('API password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
);
