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

if (auth_isvalid()) {
	user_session_remove($_SESSION['uid'], $_SESSION['sid']);
	logger_print("u:".$_SESSION['username']." uid:".$_SESSION['uid']." status:".$_SESSION['status']." sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR'], 2, "logout");
	@session_destroy();
	$_SESSION['error_string'] = _('You have been logged out');
}

header("Location: ".$core_config['http_path']['base']);
exit();
