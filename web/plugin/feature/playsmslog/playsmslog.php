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

switch (_OP_) {
	case "playsmslog_list":
		$playsmsd = [];
		$is_running = false;

		$list = registry_search(0, 'core', 'playsmsd', 'last_update');
		if (isset($list['core']['playsmsd']['last_update']) && $last_update = (int) $list['core']['playsmsd']['last_update']) {
			if (time() - $last_update > 30) {
				$is_running = false;
			} else {
				$list = registry_search(0, 'core', 'playsmsd', 'data');
				if (isset($list['core']['playsmsd']['data']) && $json = $list['core']['playsmsd']['data']) {
					$playsmsd = json_decode($json, true);
					if (isset($playsmsd['IS_RUNNING']) && $playsmsd['IS_RUNNING']) {
						$is_running = true;
					}
				}
			}
		}

		// get playsmsd status
		if ($is_running) {
			$is_running_label = '<span class=status_enabled title="' . _('playSMS daemon is running') . '"></span>';
		} else {
			$is_running_label = '<span class=status_disabled title="' . _('playSMS daemon is NOT running') . '"></span>';
		}

		$tpl = [
			'name' => 'playsmslog',
			'vars' => [
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'REFRESH_BUTTON' => _button('#', _('Refresh'), '', 'playsmslog_refresh'),
				'REFRESH_URL' => _u('index.php?app=main&inc=feature_playsmslog&op=playsmslog_log'),
				'PLAYSMSD_IS_RUNNING' => $is_running_label,
				'LOG' => playsmslog_view(),
				'Daemon status' => _('playSMS daemon status'),
				'View log' => _('View log')
			],
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case "playsmslog_log":
		ob_clean();
		_p(playsmslog_view());
		exit();
}
