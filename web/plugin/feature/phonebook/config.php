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

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_phonebook&op=phonebook_list',
	_('Phonebook'),
	2
];

$phonebook_row_limit = 5000;

$phonebook_flag_sender = [
	"<span class='playsms-icon glyphicon glyphicon-eye-close' alt='" . _('Me only') . "' title='" . _('Me only') . "'></span>",
	"<span class='playsms-icon glyphicon glyphicon-eye-open' alt='" . _('Members') . "' title='" . _('Members') . "'></span>",
	"<span class='playsms-icon glyphicon glyphicon-globe' alt='" . _('Anyone') . "' title='" . _('Anyone') . "'></span>",
];
