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
	case "credit_list":
		$db_table = $plugin_config['credit']['db_table'];
		$search_category = [
			_('Transaction datetime') => 'create_datetime'
		];
		$base_url = 'index.php?app=main&inc=feature_report&route=credit&op=credit_list';
		$search = themes_search($search_category, $base_url);
		$conditions = [
			'uid' => $user_config['uid'],
			'flag_deleted' => 0
		];

		$keywords = $search['dba_keywords'];
		$count = dba_count($db_table, $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = [
			'ORDER BY' => 'id DESC',
			'LIMIT' => (int) $nav['limit'],
			'OFFSET' => (int) $nav['offset']
		];
		$list = dba_search($db_table, '*', $conditions, $keywords, $extras);

		$content = _dialog() . "
			<h2>" . _('Report') . "</h2>
			<h3>" . _('List of my credit transactions') . "</h3>
			<p>" . $search['form'] . "</p>
			<form id=fm_feature_credit name=fm_feature_credit action=\"" . _u('index.php?app=main&inc=feature_report&route=credit&op=actions') . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=credit&op=actions&go=export') . "\">" . $icon_config['export'] . "</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=40%>" . _('Transaction datetime') . "</th>
				<th width=60%>" . _('Amount') . "</th>
			</tr>
			</thead>
			<tbody>";

		$j = 0;
		foreach ( $list as $row ) {
			$row = _display($row);
			$content .= "
				<tr>
					<td>" . core_display_datetime($row['create_datetime']) . "</td>
					<td>" . core_display_credit($row['amount']) . "</td>
				</tr>";
			$j++;
		}

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>";

		_p($content);
		break;

	case "actions":
		$db_table = $plugin_config['credit']['db_table'];
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = [
					'uid' => $user_config['uid'],
					'flag_deleted' => 0
				];

				$list = dba_search($db_table, '*', $conditions, $search['dba_keywords']);
				$data[0] = [
					_('Transaction datetime'),
					_('Amount')
				];
				$c_count = count($list);
				for ($i = 0; $i < $c_count; $i++) {
					$j = $i + 1;
					$data[$j] = [
						core_display_datetime($list[$i]['create_datetime']),
						core_display_credit($list[$i]['amount']),
					];
				}
				$content = core_csv_format($data);
				$fn = 'credit-' . $core_config['datetime']['now_stamp'] . '.csv';
				core_download($content, $fn, 'text/csv');
				break;
		}
}
