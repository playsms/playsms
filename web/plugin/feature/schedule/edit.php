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

if (!auth_isvalid()) {
	auth_block();
}

switch (_OP_) {
	case "list":
		$id = $_REQUEST['id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND id='$id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
		$message = $db_row['message'];
		$schedule_rule = $db_row['schedule_rule'];
		if ($id && $name && $message) {
			$content = _dialog() . "
			<h2>" . _('Schedule messages') . "</h2>
			<h3>" . _('Edit schedule') . "</h3>
			<form action=index.php?app=main&inc=feature_schedule&route=edit&op=edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=id value='$id'>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('Schedule ID') . "</td><td>" . $id . "</td>
			</tr>
			<tr>
				<td>" . _mandatory(_('Schedule name')) . "</td><td><input type=text maxlength=100 name=name value=\"" . $name . "\"></td>
			</tr>
			<tr>
				<td>" . _mandatory(_('Scheduled message')) . "</td><td><input type=text name=message value=\"" . $message . "\"></td>
			</tr>
			<tr>
				<td>" . _('Schedule rule') . "</td><td>" . _select('schedule_rule', $plugin_config['schedule']['rules'], $schedule_rule) . "</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_schedule&op=list');
		} else {
			auth_block();
		}
		_p($content);
		break;
	
	case "edit_yes":
		$id = $_POST['id'];
		$name = $_POST['name'];
		$message = $_POST['message'];
		$schedule_rule = (int) $_POST['schedule_rule'];
		if ($id && $name && $message) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureSchedule
				SET c_timestamp='" . mktime() . "',name='$name',message='$message', schedule_rule='$schedule_rule'
				WHERE uid='" . $user_config['uid'] . "' AND id='$id' AND flag_deleted='0'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('SMS schedule been saved');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit SMS schedule');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=edit&op=list&id=' . $id));
		exit();
		break;
}
