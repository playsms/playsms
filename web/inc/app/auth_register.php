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

$ok = FALSE;

if (! auth_isvalid()) {
	$data['name'] = $_REQUEST['name'];
	$data['username'] = $_REQUEST['username'];
	$data['mobile'] = $_REQUEST['mobile'];
	$data['email'] = $_REQUEST['email'];
	$data['status'] = 3; // force non-admin
	$data['password'] = ''; // force generate random password
	$ret = user_add($data);
	$ok = ( $ret['status'] ? TRUE : FALSE );
	$_SESSION['error_string'] = $ret['error_string'];
}

if ($ok) {
	header("Location: ".$core_config['http_path']['base']);
} else {
	header("Location: index.php?app=page&inc=register");
}
exit();
