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

// get playnet config from registry
$data = registry_search(0, 'gateway', 'playnet');
$plugin_config['playnet'] = $data['gateway']['playnet'];
$plugin_config['playnet']['name'] = 'playnet';
$plugin_config['playnet']['poll_interval'] = 2;
$plugin_config['playnet']['poll_limit'] = 400;

// smsc configuration
$plugin_config['playnet']['_smsc_config_'] = array(
	'local_playnet_username' => _('Local playnet username'),
	'local_playnet_password' => _('Local playnet password'),
	'remote_on' => _('Remote is on'),
	'remote_playsms_url' => _('Remote playSMS URL'),
	'remote_playnet_smsc' => _('Remote playnet SMSC name'),
	'remote_playnet_username' => _('Remote playnet username'),
	'remote_playnet_password' => _('Remote playnet password'),
	'sendsms_username' => _('Send SMS from remote using local username'),
	'module_sender' => _('Module sender ID'),
	'module_timezone' => _('Module timezone')
);
