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
$regs = registry_search(0, 'gateway', 'gammu');
if (isset($regs['gateway']['gammu']) && $regs = $regs['gateway']['gammu']) {
	foreach ( $regs as $key => $val ) {
		$reg[$key] = $val;
	}
}

// plugin configuration
$plugin_config['gammu'] = [
	'name' => 'gammu',
	'sms_receiver' => isset($reg['sms_receiver']) && $reg['sms_receiver'] ? core_sanitize_mobile($reg['sms_receiver']) : '',
	'path' => isset($reg['path']) && $reg['path'] ? core_sanitize_path($reg['path']) : '/var/spool/gammu',
	'dlr' => isset($reg['dlr']) && $reg['dlr'] ? 1 : 0,
];

// smsc configuration
$plugin_config['gammu']['_smsc_config_'] = [
	'sms_receiver' => _('Receiver number'),
	'path' => _('Spool folder'),
];
