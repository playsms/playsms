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

// sms_command bin path should be secured from unwanted access
$plugin_config['sms_command']['bin'] = '/var/lib/playsms/sms_command';

// set to true will allow regular users in playSMS to access this feature
// since 1.0 by default its false (read: https://github.com/antonraharja/playSMS/pull/146)
$plugin_config['sms_command']['allow_user_access'] = false;

if (auth_isadmin() || $plugin_config['sms_command']['allow_user_access']) {
	// insert to left menu array
	$menutab = $core_config['menutab']['features'];
	$menu_config[$menutab][] = [
		"index.php?app=main&inc=feature_sms_command&op=sms_command_list",
		_('Manage command')
	];
}
