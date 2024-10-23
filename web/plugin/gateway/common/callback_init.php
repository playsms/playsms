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

// -------------------- START OF CALLBACK INIT --------------------

// reload init and functions
if ($core_config['init']['cwd'] = getcwd()) {
	if (chdir('../../../')) {
		$core_config['init']['ignore_csrf'] = true; // ignore CSRF
		if (is_file('init.php')) { // load init && functions
			include 'init.php';
			if (isset($core_config['apps_path']['libs']) && $core_config['apps_path']['libs'] && is_file($core_config['apps_path']['libs'] . '/function.php')) {
				include $core_config['apps_path']['libs'] . '/function.php';
			}
		}
		if (!(function_exists('core_sanitize_alphanumeric') && function_exists('gateway_decide_smsc'))) { // double check
			exit();
		}
		if (!(isset($core_config['init']['cwd']) && chdir($core_config['init']['cwd']))) { // go back
			exit();
		}
	} else {
		exit();
	}
} else {
	exit();
}

// -------------------- END OF CALLBACK INIT --------------------