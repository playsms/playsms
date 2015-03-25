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
				<h3>" . _('Manage schedule') . "</h3>
				" . _CSRF_FORM_ . "
				<input type=hidden name=id value='$id'>
				<table class=playsms-table>
				<tr>
					<td class=label-sizer>" . _('Schedule ID') . "</td><td>" . $id . "</td>
				</tr>
				<tr>
					<td>" . _('Schedule name') . "</td><td>" . $name . "</td>
				</tr>
				<tr>
					<td>" . _('Scheduled message') . "</td><td>" . $message . "</td>
				</tr>
				<tr>
					<td>" . _('Schedule rule') . "</td><td>" . $plugin_config['schedule']['rules_display'][$schedule_rule] . "</td>
				</tr>
				</table>";
			
			// list of destinations
			

			$search_category = array(
				_('Schedule') => 'schedule',
				_('Name') => 'name',
				_('Destination') => 'destination' 
			);
			$base_url = 'index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $id;
			$search = themes_search($search_category, $base_url);
			
			$fields = '*';
			$conditions = array(
				'schedule_id' => $id 
			);
			$keywords = $search['dba_keywords'];
			$count = dba_count(_DB_PREF_ . '_featureSchedule_dst', $conditions, $keywords);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
				'ORDER BY' => 'schedule, name, destination',
				'LIMIT' => $nav['limit'],
				'OFFSET' => $nav['offset'] 
			);
			$list = dba_search(_DB_PREF_ . '_featureSchedule_dst', $fields, $conditions, $keywords, $extras);
			
			$content .= "
				<h3>" . _('List of destinations') . "</h3>
				<form name=fm_schedule_dst_list id=fm_schedule_dst_list action='" . $base_url . "' method=post>
				" . _CSRF_FORM_ . "
				<p>" . $search['form'] . "</p>						
				<a href='" . _u('index.php?app=main&inc=feature_schedule&route=manage&op=dst_add&schedule_id=' . $id) . "'>" . $icon_config['add'] . "</a>
				<a href='" . _u('index.php?app=main&inc=feature_schedule&route=import&op=list&schedule_id=' . $id) . "'>" . $icon_config['import'] . "</a>
				<a href='" . _u('index.php?app=main&inc=feature_schedule&route=export&op=list&schedule_id=' . $id) . "'>" . $icon_config['export'] . "</a>
				<div class=table-responsive>
				<table class=playsms-table-list>
				<thead><tr>
					<th width=30%>" . _('Name') . "</th>
					<th width=30%>" . _('Destination') . "</th>
					<th width=30%>" . _('Schedule') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>
				<tbody>";
			foreach ($list as $db_row) {
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_schedule&route=manage&op=dst_edit&schedule_id=' . $id . '&id=' . $db_row['id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to remove this number from SMS schedule ?') . " (" . $db_row['name'] . " " . $db_row['destination'] . ")','" . _u('index.php?app=main&inc=feature_schedule&route=manage&op=dst_del&schedule_id=' . $id . '&id=' . $db_row['id']) . "')\">" . $icon_config['delete'] . "</a>";
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['name'] . "</td>
						<td>" . $db_row['destination'] . "</td>
						<td>" . core_display_datetime($db_row['schedule']) . "</td>
						<td>" . $action . "</td>
					</tr>";
			}
			$content .= "
				</tbody>
				</table>
				</div>
				<div class=pull-right>" . $nav['form'] . "</div>
				</form>";
			
			$content .= "<p>" . _back('index.php?app=main&inc=feature_schedule&op=list');
		} else {
			auth_block();
		}
		_p($content);
		break;
	
	case "dst_add":
		$schedule_id = $_REQUEST['schedule_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND id='$schedule_id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$schedule_name = $db_row['name'];
		$schedule_message = $db_row['message'];
		if ($schedule_id && $schedule_name && $schedule_message) {
			$content = _dialog() . "
				<h2>" . _('Schedule messages') . "</h2>
				<h3>" . _('Manage schedule') . "</h3>
				<h4>" . _('Add destination') . "</h4>
				<form action=index.php?app=main&inc=feature_schedule&route=manage&op=dst_add_yes method=post>
				" . _CSRF_FORM_ . "
				<input type=hidden name=schedule_id value='" . $schedule_id . "'>
				<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tr>
					<td class=label-sizer>" . _('Schedule name') . "</td><td>" . $schedule_name . "</td>
				</tr>
				<tr>
					<td>" . _('Scheduled message') . "</td><td>" . $schedule_message . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Name')) . "</td><td><input type=text maxlength=250 name=name></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Destination')) . "</td><td><input type=text maxlength=20 name=destination> " . _hint(_('Separate by comma for multiple destinations')) . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Schedule')) . "</td><td><input type=text maxlength=19 name=schedule value=''> " . _hint(_('Format YYYY-MM-DD hh:mm')) . "</td>
				</tr>
				</table>
				<p><input type=submit class=button value=\"" . _('Save') . "\">
				</form>
				" . _back('index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $schedule_id);
		} else {
			auth_block();
		}
		_p($content);
		break;
	
	case "dst_add_yes":
		$schedule_id = $_REQUEST['schedule_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND id='$schedule_id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$schedule_name = $db_row['name'];
		$schedule_message = $db_row['message'];
		if ($schedule_id && $schedule_name && $schedule_message) {
			$name = $_POST['name'];
			$destination = $_POST['destination'];
			$schedule = trim($_POST['schedule']);
			if ($name && $destination && $schedule) {
				$schedule = ($schedule ? core_adjust_datetime($schedule) : '0000-00-00 00:00:00');
				$db_query = "
				INSERT INTO " . _DB_PREF_ . "_featureSchedule_dst (schedule_id,schedule,name,destination)
				VALUES ('$schedule_id','$schedule','$name','$destination')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['dialog']['info'][] = _('New destination has been added');
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add new destination');
				}
			} else {
				$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
			}
			header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=manage&op=dst_add&schedule_id=' . $schedule_id));
			exit();
		} else {
			auth_block();
		}
		break;
	
	case "dst_del":
		$id = $_REQUEST['id']; // destination ID
		$schedule_id = $_REQUEST['schedule_id']; // schedule ID
		if ($id && $schedule_id && dba_isexists(_DB_PREF_ . "_featureSchedule", array(
			'uid' => $user_config['uid'],
			'id' => $schedule_id 
		), 'AND')) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSchedule_dst WHERE schedule_id='$schedule_id' AND id='$id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Destination has been deleted');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete destination');
			}
		} else {
			auth_block();
		}
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $schedule_id));
		exit();
		break;
	
	case "dst_edit":
		$id = $_REQUEST['id']; // destination ID
		$schedule_id = $_REQUEST['schedule_id']; // schedule ID
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND id='$schedule_id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$schedule_name = $db_row['name'];
		$schedule_message = $db_row['message'];
		if ($id && $schedule_id && $schedule_name && $schedule_message) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule_dst WHERE schedule_id='$schedule_id' AND id='$id'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$schedule = $db_row['schedule'];
			$schedule = ($schedule ? core_display_datetime($schedule) : '0000-00-00 00:00:00');
			$name = $db_row['name'];
			$destination = $db_row['destination'];
			
			$content = _dialog() . "
				<h2>" . _('Schedule messages') . "</h2>
				<h3>" . _('Manage schedule') . "</h3>
				<h4>" . _('Edit destination') . "</h4>
				<form action=index.php?app=main&inc=feature_schedule&route=manage&op=dst_edit_yes method=post>
				" . _CSRF_FORM_ . "
				<input type=hidden name=schedule_id value='" . $schedule_id . "'>
				<input type=hidden name=id value='" . $id . "'>
				<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tr>
					<td class=label-sizer>" . _('Schedule name') . "</td><td>" . $schedule_name . "</td>
				</tr>
				<tr>
					<td>" . _('Scheduled message') . "</td><td>" . $schedule_message . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Name')) . "</td><td><input type=text maxlength=250 name=name value='" . $name . "'></td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Destination')) . "</td><td><input type=text maxlength=20 name=destination value='" . $destination . "'> " . _hint(_('Separate by comma for multiple destinations')) . "</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('Schedule')) . "</td><td><input type=text maxlength=19 name=schedule value='" . $schedule . "'> " . _hint(_('Format YYYY-MM-DD hh:mm')) . "</td>
				</tr>
				</table>
				<p><input type=submit class=button value=\"" . _('Save') . "\">
				</form>
				" . _back('index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $schedule_id);
		} else {
			auth_block();
		}
		_p($content);
		break;
	
	case "dst_edit_yes":
		$id = $_REQUEST['id']; // destination ID
		$schedule_id = $_REQUEST['schedule_id']; // schedule ID
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSchedule WHERE uid='" . $user_config['uid'] . "' AND id='$schedule_id' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$schedule_name = $db_row['name'];
		$schedule_message = $db_row['message'];
		if ($id && $schedule_id && $schedule_name && $schedule_message) {
			$name = $_POST['name'];
			$destination = $_POST['destination'];
			$schedule = trim($_POST['schedule']);
			if ($name && $destination && $schedule) {
				$schedule = ($schedule ? core_adjust_datetime($schedule) : '0000-00-00 00:00:00');
				$db_query = "
					UPDATE " . _DB_PREF_ . "_featureSchedule_dst
					SET c_timestamp='" . mktime() . "',name='$name',destination='$destination',schedule='$schedule',scheduled='0000-00-00 00:00:00'
					WHERE schedule_id='$schedule_id' AND id='$id'";
				if (@dba_affected_rows($db_query)) {
					$_SESSION['dialog']['info'][] = _('Destination has been edited');
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to edit destination');
				}
			} else {
				$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
			}
			header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=manage&op=dst_edit&schedule_id=' . $schedule_id . '&id=' . $id));
			exit();
		} else {
			auth_block();
		}
		break;
}
