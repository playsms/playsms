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

$username = trim($_REQUEST['username']);
$password = trim($_REQUEST['password']);

if ($username && $password) {
	if (auth_validate_login($username,$password)) {
		$c_user = user_getdatabyusername($username);
		$_SESSION['sid'] = session_id();
		$_SESSION['username'] = $c_user['username'];
		$_SESSION['uid'] = $c_user['uid'];
		$_SESSION['status'] = $c_user['status'];
		$_SESSION['valid'] = true;
		// save session in registry
		if (! $core_config['daemon_process']) {
			user_session_set($c_user['uid']);
		}
		logger_print("u:".$_SESSION['username']." status:".$_SESSION['status']." sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
	} else {
		$_SESSION['error_string'] = _('Invalid username or password');
	}
}

header("Location: "._u($core_config['http_path']['base']));
exit();
