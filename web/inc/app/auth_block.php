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
	$_SESSION['error_string'] = _('You have no access to this page');
	logger_print("WARNING: no access or blocked. sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR']." uid:".$user_config['uid']." app:"._APP_." inc:"._INC_." op:"._OP_." route:"._ROUTE_, 2, "auth_block");
}

header("Location: index.php?app=auth&op=block");
exit();
