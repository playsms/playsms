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

include $core_config['apps_path']['plug'] . "/gateway/template/config.php";

switch (_OP_) {
	case "manage":
		$content .= _dialog() . "
			<h2 class=page-header-title>" . _('Manage template') . "</h2>
			<form action=index.php?app=main&inc=gateway_template&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=playsms-label-sizer>" . _('Gateway name') . "</td><td>template</td>
			</tr>
			<tr>
				<td>" . _('Template installation path') . "</td><td><input type=text maxlength=250 name=up_path value=\"" . $template_param['path'] . "\"> (" . _('No trailing slash') . " \"/\")</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>";
		_p($content);
		break;

	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_path) {
			$db_query = "UPDATE " . _DB_PREF_ . "_gatewayTemplate_config SET c_timestamp='" . time() . "',cfg_path=?";
			if (dba_affected_rows($db_query, [$up_path])) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['info'][] = _('No changes has been made');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_template&op=manage'));
		exit();
}
