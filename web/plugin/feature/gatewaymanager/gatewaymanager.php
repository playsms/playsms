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

if (!auth_isadmin()) {
	auth_block();
}

$name = $_REQUEST['name'];

switch (_OP_) {
	case 'toggle_status' :
		if (gatewaymanager_set_active($name)) {
			$error_string = '<div class=error_string>' . _('You have enabled gateway plugin') . ' ' . $name . '</div>';
		}
		break;
}

$content = $error_string;
$content .= "<h2>" . _('Manage gateway') . "</h2>";
$content .= gatewaymanager_display();
_p($content);
