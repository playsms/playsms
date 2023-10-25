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

// get kannel config from registry
$data = registry_search(1, 'gateway', 'kannel');
$plugin_config['kannel'] = $data['gateway']['kannel'];
$plugin_config['kannel']['name'] = 'kannel';
$plugin_config['kannel']['bearerbox_host'] = ($plugin_config['kannel']['bearerbox_host'] ? $plugin_config['kannel']['bearerbox_host'] : 'localhost');
$plugin_config['kannel']['sendsms_host'] = ($plugin_config['kannel']['sendsms_host'] ? $plugin_config['kannel']['sendsms_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['sendsms_port'] = (int) ($plugin_config['kannel']['sendsms_port'] ? $plugin_config['kannel']['sendsms_port'] : '13131');
$plugin_config['kannel']['dlr_mask'] = (int) ($plugin_config['kannel']['dlr_mask'] ? $plugin_config['kannel']['dlr_mask'] : '27');
$plugin_config['kannel']['playsms_web'] = ($plugin_config['kannel']['playsms_web'] ? $plugin_config['kannel']['playsms_web'] : _HTTP_PATH_BASE_);
$plugin_config['kannel']['admin_host'] = ($plugin_config['kannel']['admin_host'] ? $plugin_config['kannel']['admin_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['admin_port'] = (int) ($plugin_config['kannel']['admin_port'] ? $plugin_config['kannel']['admin_port'] : '13000');
$plugin_config['kannel']['local_time'] = (int) ($plugin_config['kannel']['local_time'] ? 1 : 0);

// smsc configuration
$plugin_config['kannel']['_smsc_config_'] = array(
	'username' => _('Username'),
	'password' => _('Password'),
	'module_sender' => _('Module sender ID'),
	'module_timezone' => _('Module timezone'),
	'bearerbox_host' => _('Bearerbox hostname or IP'),
	'sendsms_host' => _('Send SMS hostname or IP'),
	'sendsms_port' => _('Send SMS port'),
	'dlr_mask' => _('DLR mask'),
	'additional_param' => _('Additional URL parameter'),
	'playsms_web' => _('playSMS web URL')
);
