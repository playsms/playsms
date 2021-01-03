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
		$extras = array(
			'ORDER BY' => 'schedule, name, destination' 
		);
		$conditions = array(
			'schedule_id' => $schedule_id 
		);
		$list = dba_search(_DB_PREF_ . '_featureSchedule_dst', '*', $conditions, '', $extras);
		$data[0] = array(
			_('Name'),
			_('Destination'),
			_('Schedule') 
		);
		for ($i = 0; $i < count($list); $i++) {
			$j = $i + 1;
			if ($j > $plugin_config['schedule']['export_row_limit']) {
				break;
			}
			$data[$j] = array(
				$list[$i]['name'],
				$list[$i]['destination'],
				core_display_datetime($list[$i]['schedule']) 
			);
		}
		$content = core_csv_format($data);
		$fn = 'schedule-' . str_pad($schedule_id, 5, "0", STR_PAD_LEFT) . '-' . $core_config['datetime']['now_stamp'] . '.csv';
		core_download($content, $fn, 'text/csv');
		break;
}
