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

// Schedule ID
$schedule_id = $_REQUEST['schedule_id'];

// validate, if not exists the block
$conditions = array(
	'uid' => $user_config['uid'],
	'id' => $schedule_id,
	'flag_deleted' => 0 
);
if (!dba_isexists(_DB_PREF_ . '_featureSchedule', $conditions)) {
	auth_block();
}

switch (_OP_) {
	case "list":
		$content .= "
			<h2>" . _('Schedule messages') . "</h2>
			<h3>" . _('Manage schedule') . "</h3>
			<h4>" . _('Import') . "</h4>
			<p>Schedule ID : " . $schedule_id . "</p>
			<table class=ps_table>
				<tbody>
					<tr>
						<td>
							<form action='index.php?app=main&inc=feature_schedule&route=import&op=import' enctype='multipart/form-data' method=POST>
							" . _CSRF_FORM_ . "
							<input type='hidden' name='schedule_id' value='$schedule_id'>
							<p>" . _('Please select CSV file') . "</p>
							<p><input type='file' name='fnpb'></p>
							<p class=text-info>" . _('format') . " : " . _('Name') . ", " . _('Destination') . ", " . _('Schedule') . "</p>
							<p><input type='submit' value='" . _('Import') . "' class='button'></p>
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			" . _back('index.php?app=main&inc=feature_schedule&route=manage&op=list&id=' . $schedule_id);
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	case "import":
		$fnpb = $_FILES['fnpb'];
		$fnpb_tmpname = $_FILES['fnpb']['tmp_name'];
		$content = "
			<h2>" . _('Schedule messages') . "</h2>
			<h3>" . _('Import confirmation') . "</h3>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='5%'>*</th>
				<th width='35%'>" . _('Name') . "</th>
				<th width='30%'>" . _('Destination') . "</th>
				<th width='30%'>" . _('Schedule') . "</th>
			</tr></thead><tbody>";
		if (file_exists($fnpb_tmpname)) {
			
			ini_set('auto_detect_line_endings', TRUE);
			if (($fp = fopen($fnpb_tmpname, "r")) !== FALSE) {
				$i = 0;
				while ($c_entry = fgetcsv($fp, 1000, ',', '"', '\\')) {
					if ($i > $plugin_config['schedule']['import_row_limit']) {
						break;
					}
					if ($i > 0) {
						$entries[$i] = $c_entry;
					}
					$i++;
				}
				array_unique($entries);
				$session_import = 'schedule_' . _PID_;
				$_SESSION['tmp'][$session_import] = array();
				$i = 0;
				foreach ($entries as $entry) {
					if ($entry[0] && $entry[1] && $entry[2]) {
						$i++;
						$content .= "
							<tr>
							<td>$i.</td>
							<td>$entry[0]</td>
							<td>$entry[1]</td>
							<td>$entry[2]</td>
							</tr>";
						$k = $i - 1;
						$_SESSION['tmp'][$session_import][$k] = $entry;
					}
				}
			}
			ini_set('auto_detect_line_endings', FALSE);
			
			$content .= "
				</tbody></table>
				</div>
				<p>" . _('Import above destination entries ?') . "</p>
				<form action='index.php?app=main&inc=feature_schedule&route=import&op=import_yes' method=POST>
				" . _CSRF_FORM_ . "
				<input type='hidden' name='schedule_id' value='$schedule_id'>
				<input type='hidden' name='number_of_row' value='$j'>
				<input type='hidden' name='session_import' value='" . $session_import . "'>
				<p><input type='submit' class='button' value='" . _('Import') . "'></p>
				</form>
				" . _back('index.php?app=main&inc=feature_schedule&route=import&op=list&schedule_id=' . $schedule_id);
			_p($content);
		} else {
			$_SESSION['dialog']['info'][] = _('Fail to upload CSV file');
			header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=import&op=list&schedule_id=' . $schedule_id));
			exit();
		}
		break;
	case "import_yes":
		@set_time_limit(0);
		$num = $_POST['number_of_row'];
		$session_import = $_POST['session_import'];
		$data = $_SESSION['tmp'][$session_import];
		foreach ($data as $d) {
			$name = trim($d[0]);
			$destination = trim($d[1]);
			$schedule = trim($d[2]);
			if ($name && $destination && $schedule) {
				$schedule = core_adjust_datetime($schedule);
				// add destiantions, replace existing entry with the same name
				if (dba_isexists(_DB_PREF_ . '_featureSchedule_dst', array(
					'schedule_id' => $schedule_id,
					'name' => $name 
				), 'AND')) {
					// update
					$items = array(
						'c_timestamp' => mktime(),
						'schedule' => $schedule,
						'scheduled' => '0000-00-00 00:00:00' 
					);
					$conditions = array(
						'schedule_id' => $schedule_id,
						'name' => $name,
						'destination' => $destination 
					);
					dba_update(_DB_PREF_ . '_featureSchedule_dst', $items, $conditions);
				} else {
					// insert
					$items = array(
						'schedule_id' => $schedule_id,
						'schedule' => $schedule,
						'scheduled' => '0000-00-00 00:00:00',
						'name' => $name,
						'destination' => $destination 
					);
					dba_add(_DB_PREF_ . '_featureSchedule_dst', $items);
				}
			}
		}
		$_SESSION['dialog']['info'][] = _('Entries in CSV file have been imported');
		header("Location: " . _u('index.php?app=main&inc=feature_schedule&route=import&op=list&schedule_id=' . $schedule_id));
		exit();
		break;
}
