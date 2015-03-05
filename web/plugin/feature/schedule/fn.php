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

function schedule_hook_playsmsd() {
	global $core_config;
	
	// fetch every minutes
	if (!core_playsmsd_timer(60)) {
		return;
	}
	
	// mark a start
	//_log('start scheduler', 2, 'schedule_hook_playsmsd');
	

	// get current server time
	$current_datetime = core_display_datetime(core_get_datetime());
	$current_timestamp = strtotime($current_datetime);
	
	// collect active schedules
	$conditions = array(
		'flag_active' => 1,
		'flag_deleted' => 0 
	);
	$schedules = dba_search(_DB_PREF_ . '_featureSchedule', '*', $conditions);
	foreach ($schedules as $sch) {
		$schedule_id = $sch['id'];
		$uid = $sch['uid'];
		$schedule_name = $sch['name'];
		$schedule_rule = (int) $sch['schedule_rule'];
		
		// collect destinations
		$conditions = array(
			'schedule_id' => $schedule_id 
		);
		$destinations = dba_search(_DB_PREF_ . '_featureSchedule_dst', '*', $conditions, '', $extras);
		foreach ($destinations as $dst) {
			$id = $dst['id'];
			$name = $dst['name'];
			$schedule_message = str_ireplace('#NAME#', $name, $sch['message']);
			$destination = $dst['destination'];
			$schedule = ($dst['schedule'] ? core_display_datetime($dst['schedule']) : '0000-00-00 00:00:00');
			$scheduled = ($dst['scheduled'] ? core_display_datetime($dst['scheduled']) : '0000-00-00 00:00:00');
			if (!$scheduled || $scheduled == '0000-00-00 00:00:00') {
				$scheduled = $schedule;
			}
			$scheduled_timestamp = strtotime($scheduled);
			
			//_log('uid:' . $uid . ' schedule_id:' . $schedule_id . ' id:' . $id . ' rule:' . $schedule_rule . ' current:[' . $current_datetime . '] schedule:[' . $schedule . '] scheduled:[' . $scheduled . ']', 2, 'schedule_hook_playsmsd');			
			

			$continue = FALSE;
			
			if ($current_timestamp >= $scheduled_timestamp) {
				switch ($schedule_rule) {
					// once
					case '0':
						//$scheduled = '2038-01-19 10:14:07';
						$scheduled = '2030-01-19 10:14:07';
						$scheduled = core_adjust_datetime($scheduled);
						$scheduled_timestamp = strtotime($current_datetime);
						$scheduled_display = $current_datetime;
						
						$continue = TRUE;
						break;
					
					// Annually
					case '1':
						$current_schedule = date('Y', $current_timestamp) . '-' . date('m-d H:i:s', strtotime($schedule));
						$next = '';
						if ($current_timestamp > strtotime($current_schedule)) {
							$next = '+1 year';
						}
						$scheduled = date($core_config['datetime']['format'], strtotime($next . ' ' . $current_schedule));
						$scheduled = core_adjust_datetime($scheduled);
						$scheduled_timestamp = strtotime($scheduled);
						$scheduled_display = core_display_datetime($scheduled);
						
						$continue = TRUE;
						break;
					
					// Monthly
					case '2':
						$current_schedule = date('Y-m', $current_timestamp) . '-' . date('d H:i:s', strtotime($schedule));
						$next = '';
						if ($current_timestamp > strtotime($current_schedule)) {
							$next = '+1 month';
						}
						$scheduled = date($core_config['datetime']['format'], strtotime($next . ' ' . $current_schedule));
						$scheduled = core_adjust_datetime($scheduled);
						$scheduled_timestamp = strtotime($scheduled);
						$scheduled_display = core_display_datetime($scheduled);
						
						$continue = TRUE;
						break;
					
					// Weekly
					case '3':
						$current_schedule = date('Y-m-d', $current_timestamp) . ' ' . date('H:i:s', strtotime($schedule));
						$current_day = date('l', strtotime($current_schedule));
						$next = '';
						if ($current_timestamp > strtotime($current_schedule)) {
							$next = 'next ' . $current_day;
						}
						$scheduled = date($core_config['datetime']['format'], strtotime($next . ' ' . $current_schedule));
						$scheduled = core_adjust_datetime($scheduled);
						$scheduled_timestamp = strtotime($scheduled);
						$scheduled_display = core_display_datetime($scheduled);
						
						$continue = TRUE;
						break;
					
					// Daily
					case '4':
						$current_schedule = date('Y-m-d', $current_timestamp) . ' ' . date('H:i:s', strtotime($schedule));
						$next = '';
						if ($current_timestamp > strtotime($current_schedule)) {
							$next = '+1 day';
						}
						$scheduled = date($core_config['datetime']['format'], strtotime($next . ' ' . $current_schedule));
						$scheduled = core_adjust_datetime($scheduled);
						$scheduled_timestamp = strtotime($scheduled);
						$scheduled_display = core_display_datetime($scheduled);
						
						$continue = TRUE;
						break;
				}
			}
			
			if ($continue) {
				// set scheduled to next time
				$items = array(
					'c_timestamp' => mktime(),
					'scheduled' => $scheduled 
				);
				$conditions = array(
					'schedule_id' => $schedule_id,
					'id' => $id 
				);
				if (dba_update(_DB_PREF_ . '_featureSchedule_dst', $items, $conditions, 'AND')) {
					// if the interval is under an hour then go ahead, otherwise expired
					$interval = $current_timestamp - $scheduled_timestamp;
					if ($interval <= 3600) {
						_log('sendsms uid:' . $uid . ' schedule_id:' . $schedule_id . ' id:' . $id . ' rule:' . $schedule_rule . ' schedule:[' . $schedule . '] scheduled:[' . $scheduled_display . ']', 2, 'schedule_hook_playsmsd');
						$username = user_uid2username($uid);
						sendsms_helper($username, $destination, $schedule_message, 'text', $unicode);
					} else {
						_log('expired uid:' . $uid . ' schedule_id:' . $schedule_id . ' id:' . $id . ' rule:' . $schedule_rule . ' schedule:[' . $schedule . '] scheduled:[' . $scheduled_display . '] interval:' . $interval, 2, 'schedule_hook_playsmsd');
					}
				} else {
					_log('fail update uid:' . $uid . ' schedule_id:' . $schedule_id . ' id:' . $id . ' rule:' . $schedule_rule . ' schedule:[' . $schedule . '] scheduled:[' . $scheduled_display . ']', 2, 'schedule_hook_playsmsd');
				}
			}
		}
	}
	
	// mark an end
	//_log('end scheduler', 2, 'schedule_hook_playsmsd');
}
