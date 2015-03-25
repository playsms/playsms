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
	case "queuelog_list":
		$nav = themes_nav($count, "index.php?app=main&inc=feature_queuelog&op=queuelog_list");
		
		$content = _dialog() . "
			<h2>" . _('View SMS queue') . "</h2>";
		
		$count = queuelog_countall();
		if ($count) {
			$content .= "<p><a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete ALL queues")) . " ?','" . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_delete_all') . "')\">" . $icon_config['delete'] . _("Delete ALL queues") . " ($count)</a></p>";
		}
		
		$content .= "<div align=center>" . $nav['form'] . "</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
		";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>" . _('Queue Code') . "</th>
				<th width=15%>" . _('User') . "</th>
			";
		} else {
			$content .= "
				<th width=30%>" . _('Queue Code') . "</th>
			";
		}
		$content .= "
				<th width=15%>" . _('Scheduled') . "</th>
				<th width=10%>" . _('Count') . "</th>
				<th width=30%>" . _('Message') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c = count($data) - 1; $c >= 0; $c--) {
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_scheduled = core_display_datetime($data[$c]['datetime_scheduled']);
			$c_username = user_uid2username($data[$c]['uid']);
			
			// total number of SMS in queue
			$c_count = $data[$c]['sms_count'];
			
			$c_message = stripslashes(core_display_text($data[$c]['message']));
			$c_action = "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete queue")) . " " . $c_queue_code . " ?','" . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_delete&queue=' . $c_queue_code) . "')\">" . $icon_config['delete'] . "</a>";
			$content .= "
				<tr>
			";
			if (auth_isadmin()) {
				$content .= "
					<td>" . $c_queue_code . "</td>
					<td>" . $c_username . "</td>
				";
			} else {
				$content .= "
					<td>" . $c_queue_code . "</td>
				";
			}
			$content .= "
					<td>" . $c_datetime_scheduled . "</td>
					<td>" . $c_count . "</td>
					<td>" . $c_message . "</td>
					<td>" . $c_action . "</td>
				</tr>
			";
		}
		$content .= "
			</tbody></table>
			</div>
			<div align=center>" . $nav['form'] . "</div>
		";
		_p($content);
		break;
	case "queuelog_delete":
		if ($queue = $_REQUEST['queue']) {
			if (queuelog_delete($queue)) {
				$_SESSION['dialog']['info'][] = _('Queue has been removed');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_list'));
		exit();
		break;
	case "queuelog_delete_all":
		if (queuelog_delete_all($queue)) {
			$_SESSION['dialog']['info'][] = _('All queues have been removed');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_list'));
		exit();
		break;
}
