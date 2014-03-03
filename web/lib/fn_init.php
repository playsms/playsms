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

/*
 start init functions
 protect from SQL injection when magic_quotes_gpc sets to "Off"
 */
function array_add_slashes($array) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$value = addslashes($value);
				$key = addslashes($key);
				$new_arr[$key] = $value;
			}
			if (is_array($value)) {
				$new_arr[$key] = array_add_slashes($value);
			}
		}
	}
	return $new_arr;
}

function pl_addslashes($data) {
	if (is_array($data)) {
		$data = array_add_slashes($data);
	} else {
		$data = addslashes($data);
	}
	return $data;
}

/**
 * Set the language for the user, if it's no defined just leave it with the default
 * @param string $var_username Username
 * @return boolean TRUE if valid
 */
function setuserlang($username="") {
	global $core_config;
	$language_module = $core_config['module']['language'];
	$db_query = "SELECT language_module FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if (trim($db_row['language_module'])) {
		$language_module = $db_row['language_module'];
	}
	if (defined('LC_MESSAGES')) {
        	// linux
	        setlocale(LC_MESSAGES, $language_module, $language_module.'.utf8', $language_module.'.utf-8', $language_module.'.UTF8', $language_module.'.UTF-8');
	} else {
        	// windows
	        putenv("LC_ALL={$language_module}");
	}
}

// fixme anton
// enforced to declare function _() for gettext replacement if no PHP gettext extension found
// it is also possible to completely remove gettext and change multi-lang with translation array
if (! function_exists('_')) {
	function _($text) {
		return $text;
	}
}

/**
 * Include essential functions
 */
include_once $core_config['apps_path']['libs']."/fn_dba.php";
include_once $core_config['apps_path']['libs']."/fn_core.php";
include_once $core_config['apps_path']['libs']."/fn_registry.php";
include_once $core_config['apps_path']['libs']."/fn_sendmail.php";
include_once $core_config['apps_path']['libs']."/fn_user.php";
include_once $core_config['apps_path']['libs']."/fn_logger.php";
include_once $core_config['apps_path']['libs']."/fn_auth.php";
include_once $core_config['apps_path']['libs']."/fn_tpl.php";
include_once $core_config['apps_path']['libs']."/fn_themes.php";

/*
 * end of init functions
 */
