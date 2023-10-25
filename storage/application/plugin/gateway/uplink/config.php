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

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayUplink_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['uplink']['name'] = 'uplink';
	$plugin_config['uplink']['master'] = $db_row['cfg_master'];
	$plugin_config['uplink']['username'] = $db_row['cfg_username'];
	$plugin_config['uplink']['token'] = $db_row['cfg_token'];
	$plugin_config['uplink']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['uplink']['path'] = $db_row['cfg_incoming_path'];
	$plugin_config['uplink']['additional_param'] = $db_row['cfg_additional_param'];
	$plugin_config['uplink']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
	$plugin_config['uplink']['try_disable_footer'] = $db_row['cfg_try_disable_footer'];
}

// smsc configuration
$plugin_config['uplink']['_smsc_config_'] = array(
	'master' => _('Master URL'),
	'username' => _('Webservice username'),
	'token' => _('Webservice token'),
	'additional_param' => _('Additional URL parameter'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
);

//$gateway_number = $plugin_config['uplink']['module_sender'];
// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_uplink&op=manage", _('Manage uplink'));
//}
