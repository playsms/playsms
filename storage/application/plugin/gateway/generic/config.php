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

$callback_url = '';
if (!$core_config['daemon_process']) {
	$callback_url = _HTTP_PATH_BASE_ . "/index.php?app=call&cat=gateway&plugin=generic&access=callback";
}

$data = registry_search(0, 'gateway', 'generic');
$plugin_config['generic'] = $data['gateway']['generic'];
$plugin_config['generic']['name'] = 'generic';
$plugin_config['generic']['default_url'] = 'http://api.example.com/handler.php?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}';
$plugin_config['generic']['default_callback_url'] = $callback_url;
if (!trim($plugin_config['generic']['url'])) {
	$plugin_config['generic']['url'] = $plugin_config['generic']['default_url'];
}
if (!trim($plugin_config['generic']['callback_url'])) {
	$plugin_config['generic']['callback_url'] = $plugin_config['generic']['default_callback_url'];
}
if (!trim($plugin_config['generic']['callback_url_authcode'])) {
	$plugin_config['generic']['callback_url_authcode'] = md5(core_get_random_string());
}

// smsc configuration
$plugin_config['generic']['_smsc_config_'] = array(
	'url' => _('Generic send SMS URL'),
	'api_username' => _('API username'),
	'api_password' => _('API password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone')
);
