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
$regs = registry_search(0, 'gateway', 'infobip');
if (isset($regs['gateway']['infobip']) && $regs = $regs['gateway']['infobip']) {
	foreach ( $regs as $key => $val ) {
		$reg[$key] = $val;
	}
}

// plugin configuration
$plugin_config['infobip'] = [
	'name' => 'infobip',
	'default_url' => 'http://api.infobip.com/api/v3',
	'default_callback_url' => gateway_callback_url('infobip'),
	'send_url' => isset($reg['send_url']) && $reg['send_url'] ? $reg['send_url'] : $plugin_config['infobip']['default_url'],
	'callback_url' => isset($reg['callback_url']) && $reg['callback_url'] ? $reg['callback_url'] : $plugin_config['infobip']['default_callback_url'],
	'callback_url_authcode' => isset($reg['callback_url_authcode']) && $reg['callback_url_authcode'] ? $reg['callback_url_authcode'] : core_random(),
	'username' => isset($reg['username']) ? $reg['username'] : '',
	'password' => isset($reg['password']) ? $reg['password'] : '',
	'additional_param' => isset($reg['additional_param']) ? $reg['additional_param'] : '',
	'dlr_nopush' => isset($reg['dlr_nopush']) && $reg['dlr_nopush'] ? 1 : 0,
	'module_sender' => isset($data['gateway']['module_sender']) ? core_sanitize_sender($data['gateway']['module_sender']) : '',
	'datetime_timezone' => isset($data['gateway']['datetime_timezone']) ? $data['gateway']['datetime_timezone'] : '',
];

// smsc configuration
$plugin_config['infobip']['_smsc_config_'] = [
	'username' => _('Username'),
	'password' => _('Password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone'),
];
