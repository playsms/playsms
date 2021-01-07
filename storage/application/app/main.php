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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

$continue = TRUE;

// load plugin
if ($continue && _INC_) {
	$p = explode('_', _INC_, 2);
	$plugin_category = $p[0];
	$plugin_name = $p[1];
	$plugin_dir = _APPS_PATH_PLUG_.'/'.$plugin_category.'/'.$plugin_name;
	$file_name = ( _ROUTE_ ? _ROUTE_.'.php' : $plugin_name.'.php' );
	$plugin_file = $plugin_dir.'/'.$file_name;
	if (file_exists($plugin_file)) {
		include_once $plugin_file;
	}
}

$content = ob_get_clean();

_p(themes_apply($content));
