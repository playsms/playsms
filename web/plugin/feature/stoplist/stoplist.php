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
	case "stoplist_list":
		$search_category = array(
			_('Mobile') => 'mobile',
		);
		$base_url = 'index.php?app=main&inc=feature_stoplist&op=stoplist_list';
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_featureStoplist', [], $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'ORDER BY' => 'mobile',
			'LIMIT' => (int) $nav['limit'],
			'OFFSET' => (int) $nav['offset']
		);
		$list = dba_search(_DB_PREF_ . '_featureStoplist', '*', [], $keywords, $extras);

		$content = _dialog() . "
			<h2>" . _('Manage stoplist') . "</h2>
			<p>" . $search['form'] . "</p>
			<form name=fm_stoplist_list id=fm_stoplist_list action='index.php?app=main&inc=feature_stoplist&op=actions' method=post>
			" . _CSRF_FORM_ . "
			<div class=table-responsive>
			<table class=playsms-table-list>
				<thead>
					<tr>
						<td colspan=3>
							<div class=actions_box>
								<div class=pull-left>
									<a href='" . _u('index.php?app=main&inc=feature_stoplist&op=stoplist_add') . "'>" . $icon_config['add'] . "</a>
								</div>
								<script type='text/javascript'>
									$(document).ready(function() {
										$('#action_go').click(function(){
											$('#fm_stoplist_list').submit();
										});
									});
								</script>
								<div class=pull-right>
									<select name=go class=search_input_category>
										<option value=>" . _('Select') . "</option>
										<option value=delete>" . _('Delete') . "</option>
									</select>
									<a href='#' id=action_go>" . $icon_config['go'] . "</a>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th width=95%>" . _('Blocked mobile') . "</th>
						<th width=5%><input type=checkbox onclick=CheckUncheckAll(document.fm_stoplist_list)></th>
					</tr>
				</thead>
			<tbody>";

		$list = _display($list);
		foreach ( $list as $db_row ) {
			$pid = $db_row['id'];
			$mobile = $db_row['mobile'];
			$content .= "
				<tr>
					<td>$mobile</td>
					<td>
						<input type=checkbox name=itemid[] value=\"$pid\">
					</td>
				</tr>";
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
		$is_removed = false;
		$items = isset($_REQUEST['itemid']) ? $_REQUEST['itemid'] : [];
		$go = strtolower(core_sanitize_alphanumeric($_REQUEST['go']));
		switch ($go) {
			case 'delete':
				foreach ( $items as $item ) {
					if (dba_remove(_DB_PREF_ . '_featureStoplist', ['id' => $item])) {
						$is_removed = true;
					}
				}
				break;
		}

		$search = themes_search_session();
		$nav = themes_nav_session();

		if ($is_removed) {
			$_SESSION['dialog']['info'][] = _('Mobile numbers have been deleted');
		}

		$ref = $search['url'] . '&search_keyword=' . $search['keyword'] . '&search_category=' . $search['category'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
		header("Location: " . _u($ref));
		exit();

	case "stoplist_add":
		$content = _dialog() . "
			<h2>" . _('Manage stoplist') . "</h2>
			<h3>" . _('Add blocked mobile numbers') . " " . _hint(_('Multiple mobile numbers must be comma-separated')) . "</h3>
			<form action='index.php?app=main&inc=feature_stoplist&op=stoplist_add_yes' method='post'>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _mandatory(_('Mobile numbers')) . "</td>
				<td><input type=text name='add_mobile' required> " . _hint(_('Comma separated values for multiple mobile numbers')) . "
				</td>
			</tr>
			</table>
			<p><input type='submit' class='button' value='" . _('Save') . "'></p>
			</form>
			" . _back('index.php?app=main&inc=feature_stoplist&op=stoplist_list');
		_p($content);
		break;

	case "stoplist_add_yes":
		$add_mobile = preg_replace('/[^\d\+,]+/', '', $_POST['add_mobile']);
		if ($add_mobile) {
			$mobiles = explode(',', $add_mobile);
			$mobiles = array_unique($mobiles);
			sort($mobiles);
			foreach ( $mobiles as $mobile ) {
				blacklist_mobile_add(core_sanitize_mobile($mobile));
			}
			$_SESSION['dialog']['info'][] = _('Mobile numbers have been blocked');
		} else {
			$_SESSION['dialog']['danger'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_stoplist&op=stoplist_add'));
		exit();
}
