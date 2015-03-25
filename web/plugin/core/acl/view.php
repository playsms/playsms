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

$id = $_REQUEST['id'];
$acl_name = acl_getname($id);

switch (_OP_) {
	case "user_list":
		$search_var = array(
			_('Registered') => 'register_datetime',
			_('Username') => 'username',
			_('Name') => 'name',
			_('Mobile') => 'mobile' 
		);
		$search = themes_search($search_var, '');
		$conditions = array(
			'flag_deleted' => 0,
			'acl_id' => $id 
		);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblUser', $conditions, $keywords);
		$nav = themes_nav($count, "index.php?app=main&inc=core_acl&route=view&op=user_list&id=" . $id);
		$extras = array(
			'ORDER BY' => 'register_datetime DESC, username',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_tblUser', '*', $conditions, $keywords, $extras);

		$content .= _dialog() . "
			<h2>" . _('Manage ACL') . "</h2>
			<h3>" . _('View report') . "</h3>

			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tr>
					<td class=label-sizer>" . _('ACL ID') . "</td>
					<td>" . $id . "</td>
				</tr>
				<tr>
					<td>" . _('ACL name') . "</td>
					<td>" . $acl_name . "</td>
				</tr>
			</table>			
			<h4>" . _('List of accounts') . "</h4>
			<p>" . $search['form'] . "</p>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='20%'>" . _('Registered') . "</th>
				<th width='20%'>" . _('Username') . "</th>
				<th width='20%'>" . _('Name') . "</th>
				<th width='20%'>" . _('Mobile') . "</th>
				<th width='20%'>" . _('Credit') . "</th>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i = 0; $i < count($list); $i++) {
			
			$action = "";
			
			if ($list[$i]['uid'] != '1' || $list[$i]['uid'] != $user_config['uid']) {
				if (user_banned_get($list[$i]['uid'])) {
					// unban
					$banned_icon = $icon_config['ban'];
				} else {
					// ban
					$banned_icon = '';
				}
			}
			
			$j--;
			$content .= "
				<tr>
					<td>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					" . $parent_column_row . "
					<td>" . $banned_icon . "" . $list[$i]['username'] . " </td>
					<td>" . $list[$i]['name'] . "</td>
					<td>" . $list[$i]['mobile'] . "</td>
					<td>" . rate_getusercredit($list[$i]['username']) . "</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			" . _back('index.php?app=main&inc=core_acl&op=acl_list');
		_p($content);
		break;
}

