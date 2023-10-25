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

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTwilio_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['twilio']['name'] = 'twilio';
	$plugin_config['twilio']['url'] = ($db_row['cfg_url'] ? $db_row['cfg_url'] : 'https://api.twilio.com');
	$plugin_config['twilio']['callback_url'] = ($db_row['cfg_callback_url'] ? $db_row['cfg_callback_url'] : _HTTP_PATH_BASE_ . '/index.php?app=call&cat=gateway&plugin=twilio&access=callback');
	$plugin_config['twilio']['account_sid'] = $db_row['cfg_account_sid'];
	$plugin_config['twilio']['auth_token'] = $db_row['cfg_auth_token'];
	$plugin_config['twilio']['module_sender'] = $db_row['cfg_module_sender'];
	$plugin_config['twilio']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];
}

// smsc configuration
$plugin_config['twilio']['_smsc_config_'] = array(
	'account_sid' => _('Account SID'),
	'auth_token' => _('Auth Token'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
);

//$gateway_number = $plugin_config['twilio']['module_sender'];

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_twilio&op=manage", _('Manage twilio'));
//}
