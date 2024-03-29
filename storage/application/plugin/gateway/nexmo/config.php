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

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayNexmo_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['nexmo']['name'] = 'nexmo';
	$plugin_config['nexmo']['url'] = ($db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://rest.nexmo.com/sms/json');
	$plugin_config['nexmo']['api_key'] = $db_row['cfg_api_key'];
	$plugin_config['nexmo']['api_secret'] = $db_row['cfg_api_secret'];
	$plugin_config['nexmo']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['nexmo']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['nexmo']['_smsc_config_'] = array(
	'api_key' => _('API key'),
	'api_secret' => _('API secret'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
);

//$gateway_number = $plugin_config['nexmo']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_nexmo&op=manage", _('Manage nexmo'));
//}
