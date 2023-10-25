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
$data = registry_search(1, 'gateway', 'openvox');
$plugin_config['openvox'] = $data['gateway']['openvox'];
$plugin_config['openvox']['name'] = 'openvox';
$plugin_config['openvox']['gateway_port'] = ($plugin_config['openvox']['gateway_port'] ? $plugin_config['openvox']['gateway_port'] : '80');

// smsc configuration
$plugin_config['openvox']['_smsc_config_'] = array(
	'gateway_host' => _('Gateway host'),
	'gateway_port' => _('Gateway port'),
	'username' => _('Username'),
	'password' => _('Password')
);

//$gateway_number = $plugin_config['openvox']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_openvox&op=manage", _('Manage openvox'));
//}
