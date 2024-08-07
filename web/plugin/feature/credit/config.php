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

// admin and users allowed to use this plugin
if (($user_config['status'] == 2) || ($user_config['status'] == 3)) {
	$menutab = $core_config['menutab']['settings'];
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_credit&op=credit_list',
		_('Manage credit')
	];
}

$plugin_config['credit']['db_table'] = _DB_PREF_ . '_featureCredit';
