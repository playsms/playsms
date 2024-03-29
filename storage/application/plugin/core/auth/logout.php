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
	user_session_remove($_SESSION['uid']);

	// if old_login exists then try to return to it
	if (auth_login_as_check()) {
		// try to return
		auth_login_return();

		if (auth_isvalid()) {
			_log("re-login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . _REMOTE_ADDR_, 2, "auth logout");
		} else {
			_log("fail to re-login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . _REMOTE_ADDR_, 2, "auth logout");
		}
		header('Location: ' . _u(_HTTP_PATH_BASE_));
	} else {
		_log("u:" . $_SESSION['username'] . " uid:" . $_SESSION['uid'] . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . _REMOTE_ADDR_, 2, "auth logout");

		// destroy user session for complete logout
		auth_session_destroy();

		$_SESSION['dialog']['info'][] = _('You have been logged out');
	}
}

header("Location: " . _u($core_config['http_path']['base']));
exit();