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

/**
 * Filter inputs
 * 
 * @param array $main_config Main configuration array
 * @return array Filtered
 */
function main_config_filter($main_config = [])
{
	foreach ( $main_config as $key => $val ) {
		switch ($key) {
			case 'information_content':
			case 'edit_information_content':
			case 'layout_footer':
			case 'edit_layout_footer':
				$main_config[$key] = _h($val);
				break;
			default:
				$main_config[$key] = _t($val);
		}
	}

	return $main_config;
}
