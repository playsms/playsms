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
$reg = [];
$regs = registry_search(0, 'gateway', 'generic');
if (isset($regs['gateway']['generic']) && $regs = $regs['gateway']['generic']) {
	foreach ( $regs as $key => $val ) {
		$reg[$key] = $val;
	}
}

// plugin configuration
$plugin_config['generic'] = [
	'name' => 'generic',
	'default_url' => 'http://example.com/?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}',
	'default_callback_url' => gateway_callback_url('generic'),
	'url' => isset($reg['url']) && $reg['url'] ? $reg['url'] : $plugin_config['generic']['default_url'],
	'callback_url' => isset($reg['callback_url']) && $reg['callback_url'] ? $reg['callback_url'] : $plugin_config['generic']['default_callback_url'],
	'callback_url_authcode' => isset($reg['callback_url_authcode']) && $reg['callback_url_authcode'] ? $reg['callback_url_authcode'] : core_random(),
	'module_sender' => isset($data['gateway']['module_sender']) ? $data['gateway']['module_sender'] : '',
	'datetime_timezone' => isset($data['gateway']['datetime_timezone']) ? $data['gateway']['datetime_timezone'] : '',
];

// smsc configuration
$plugin_config['generic']['_smsc_config_'] = [
	'url' => _('Generic send SMS URL'),
	'api_username' => _('API username'),
	'api_password' => _('API password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
];
