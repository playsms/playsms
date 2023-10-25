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

// get gammu config from registry
$data = registry_search(0, 'gateway', 'smstools');

$plugin_config['smstools']['name'] = 'smstools';
$plugin_config['smstools']['default_queue'] = trim(core_sanitize_path($data['gateway']['smstools']['default_queue']));
if (!$plugin_config['smstools']['default_queue']) {
	$plugin_config['smstools']['default_queue'] = "/var/spool/sms";
}

// smsc configuration
$plugin_config['smstools']['_smsc_config_'] = array(
	'sms_receiver' => _('Receiver number'),
	'queue' => _('Queue directory')
);

// insert to left menu array
//if (isadmin()) {
//	$menutab_gateway = $core_config['menutab']['gateway'];
//	$menu_config[$menutab_gateway][] = array("index.php?app=main&inc=gateway_smstools&op=manage", _('Manage smstools'));
//}
