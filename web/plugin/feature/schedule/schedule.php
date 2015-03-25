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
		$content = _dialog() . "
			<h2>" . _('Schedule messages') . "</h2>
			<p>" . _button('index.php?app=main&inc=feature_schedule&op=add', _('Add SMS schedule')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=10%>" . _('ID') . "</th>
				<th width=20%>" . _('Name') . "</th>
				<th width=50%>" . _('Message') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND flag_deleted='0' ORDER BY name";
		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$status_active = "<a href=\"" . _u('index.php?app=main&inc=feature_schedule&op=status&id=' . $db_row['id'] . '&status=0') . "\"><span class=status_enabled /></a>";
			$status_inactive = "<a href=\"" . _u('index.php?app=main&inc=feature_schedule&op=status&id=' . $db_row['id'] . '&status=1') . "\"><span class=status_disabled /></a>";
			$status = ($db_row['flag_active'] == 1 ? $status_active : $status_inactive);
			$action = "<a href=\"" . _u('index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $db_row['id']) . "\">" . $icon_config['manage'] . "</a>&nbsp;";
			$action .= "<a href=\"" . _u('index.php?app=main&inc=feature_schedule&route=edit&op=list&id=' . $db_row['id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS schedule ?') . " (" . _('Schedule ID') . ": " . $db_row['id'] . ")','" . _u('index.php?app=main&inc=feature_schedule&op=del&id=' . $db_row['id']) . "')\">" . $icon_config['delete'] . "</a>";
			$i++;
			$content .= "
					<tr>
						<td>" . $db_row['id'] . "</td>
						<td>" . $db_row['name'] . "</td>
						<td>" . $db_row['message'] . "</td>
						<td>" . $status . "</td>
						<td>" . $action . "</td>
					</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_schedule&op=add', _('Add SMS schedule'));
		_p($content);
		break;
	
	case "add":
		$content = _dialog() . "
			<h2>" . _('Schedule messages') . "</h2>
			<h3>" . _('Add SMS schedule') . "</h3>
			<form action=index.php?app=main&inc=feature_schedule&op=add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=label-sizer>" . _mandatory(_('Schedule name')) . "</td><td><input type=text maxlength=100 name=name></td>
			</tr>
			<tr>
				<td>" . _mandatory(_('Scheduled message')) . "</td><td><input type=text name=message></td>
			</tr>
			<tr>
				<td>" . _('Schedule rule') . "</td><td>" . _select('schedule_rule', $plugin_config['schedule']['rules']) . "</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_schedule&op=list');
		_p($content);
		break;
	
	case "add_yes":
		$name = $_POST['name'];
		$message = $_POST['message'];
		$schedule_rule = (int) $_POST['schedule_rule'];
		if ($name && $message) {
			// flag_active  : 1 active, 2 inactive, 0 considered inactive
			// flag_deleted : 1 deleted, other values considered non-deleted
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_featureSchedule (c_timestamp,uid,name,message,schedule_rule,flag_active,flag_deleted)
				VALUES (" . mktime() . ",'" . $user_config['uid'] . "','$name','$message','$schedule_rule','2','0')";
			if ($new_uid = @dba_insert_id($db_query)) {
				$_SESSION['dialog']['info'][] = _('New SMS schedule been added');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to add new SMS schedule');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&op=add'));
		exit();
		break;
	
	case "status":
		$id = $_REQUEST['id'];
		if ($id && dba_isexists(_DB_PREF_ . "_featureSchedule", array(
			'uid' => $user_config['uid'],
			'id' => $id,
			'flag_deleted' => '0' 
		), 'AND')) {
			$status = ($_REQUEST['status'] == 1 ? 1 : 2);
			$db_query = "UPDATE " . _DB_PREF_ . "_featureSchedule SET c_timestamp='" . mktime() . "', flag_active='$status' WHERE uid='" . $user_config['uid'] . "' AND id='$id'";
			if (@dba_affected_rows($db_query)) {
				if ($status == 1) {
					$_SESSION['dialog']['info'][] = _('SMS schedule has been enabled');
				} else {
					$_SESSION['dialog']['info'][] = _('SMS schedule has been disabled');
				}
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&op=list'));
		exit();
		break;
	
	case "del":
		$id = $_REQUEST['id'];
		if ($id && dba_isexists(_DB_PREF_ . "_featureSchedule", array(
			'uid' => $user_config['uid'],
			'id' => $id 
		), 'AND')) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureSchedule SET c_timestamp='" . mktime() . "', flag_active='2', flag_deleted='1' WHERE uid='" . $user_config['uid'] . "' AND id='$id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('SMS schedule has been deleted');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete SMS schedule');
			}
		} else {
			auth_block();
		}
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&op=list'));
		exit();
		break;
}
