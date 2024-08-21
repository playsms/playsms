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

// insert to left menu array
$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = [
	"index.php?app=main&inc=feature_schedule&op=list",
	_('Schedule messages'),
	1
];

// plugin config
$plugin_config['schedule']['rules'] = [
	_('Once') => 0,
	_('Annually') => 1,
	_('Monthly') => 2,
	_('Weekly') => 3,
	_('Daily') => 4
];

$plugin_config['schedule']['rules_display'] = array_flip($plugin_config['schedule']['rules']);

$plugin_config['schedule']['import_row_limit'] = 1000;

$plugin_config['schedule']['export_row_limit'] = 1000;
