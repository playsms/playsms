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

$poll_id = (int) $_REQUEST['poll_id'];

if ($poll_id && !sms_poll_check_id($poll_id)) {
	auth_block();
}

switch (_OP_) {
	case 'list':
		$conditions['poll_id'] = $poll_id;
		$list = dba_search(_DB_PREF_ . '_featurePoll', '*', $conditions);
		if (!(isset($list[0]['poll_keyword']) && $poll_keyword = $list[0]['poll_keyword'])) {
			$_SESSION['dialog']['danger'][] = _('Unknown error cannot find the data in database');
			header("Location: " . _u('index.php?app=main&inc=feature_sms_poll&op=sms_poll_list'));
			exit();
		}

		$content = sms_poll_export_csv($poll_id, $poll_keyword);
		$filename = 'sms-poll-' . $poll_keyword . '-' . $core_config['datetime']['now_stamp'] . '.csv';
		core_download($content, $filename, 'text/csv');
		break;
}
