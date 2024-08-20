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
		$search_category = array(
			_('Name') => 'name',
			_('Group code') => 'code'
		);
		$base_url = 'index.php?app=main&inc=feature_phonebook&route=group&op=list';
		$search = themes_search($search_category, $base_url);
		$conditions = [
			'uid' => $user_config['uid']
		];
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_featurePhonebook_group', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = [
			'ORDER BY' => 'name',
			'LIMIT' => (int) $nav['limit'],
			'OFFSET' => (int) $nav['offset']
		];
		$fields = 'id, name, code, flag_sender';
		$list = dba_search(_DB_PREF_ . '_featurePhonebook_group', $fields, $conditions, $keywords, $extras);

		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<h3 class=page-header-subtitle>" . _('Group') . "</h3>
			<p>" . $search['form'] . "</p>
			<form id=fm_phonebook_group_list name=fm_phonebook_group_list action='index.php?app=main&inc=feature_phonebook&route=group&op=actions' method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href='" . _u('index.php?app=main&inc=feature_phonebook&route=group&op=add') . "'>" . $icon_config['add'] . "</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=50%>" . _('Name') . "</th>
				<th width=47%>" . _('Group code') . "</th>
				<th width=3% nowrap>" . $icon_config['action'] . "</th>
			</tr>
			</thead>
			<tbody>";

		if (isset($list) && is_array($list)) {
			$list = _display($list);

			$j = 0;
			$c_count = count($list);
			for ($j = 0; $j < $c_count; $j++) {
				$gpid = $list[$j]['id'];
				$name = $list[$j]['name'];
				$code = $list[$j]['code'];
				$flag_sender = (int) $list[$j]['flag_sender'];
				$i++;
				$content .= "
					<tr>
						<td><a href='" . _u('index.php?app=main&inc=feature_phonebook&route=group&op=edit&gpid=' . $gpid) . "'>" . $name . "</a></td>
						<td>" . $phonebook_flag_sender[$flag_sender] . " " . $code . "</td>
						<td nowrap>
							<a href='#' onClick=\"return ConfirmURL('" . _('Are you sure you want to delete this group ?') . " (" . _('group') . " : " . $name . ")" . "', '" . _u('index.php?app=main&inc=feature_phonebook&route=group&op=actions&go=delete&gpid=' . $gpid) . "');\">" . $icon_config['delete'] . "</a>
						</td>
					</tr>";
			}
		}

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&op=phonebook_list');

		_p($content);
		break;

	case "add":
		$option_flag_sender = "
			<option value='0'>" . _('User') . "</option>
			<option value='1'>" . _('Group') . "</option>
			<option value='2'>" . _('All users') . "</option>";
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<h3 class=page-header-subtitle>" . _('Add group') . "</h3>
			<p>
			<form action=\"index.php?app=main&inc=feature_phonebook&route=group&op=actions&go=add\" method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tbody>
				<tr>
					<td class=playsms-label-sizer>" . _('Group name') . "</td>
					<td><input type=text name=group_name value=\"" . _lastpost('group_name') . "\"></td>
				</tr>
				<tr>
					<td>" . _('Group code') . "</td>
					<td><input type=text name=group_code size=10 value=\"" . _lastpost('group_code') . "\"> " . _hint(_('Group code may be used to broadcast SMS to this group') . ". " . _('Please use alphanumeric only and make it short')) . "</td>
				</tr>
				<tr>
					<td>" . _('Share phonebook and allow broadcast') . "</td>
					<td><select name=flag_sender>" . $option_flag_sender . "</select> " . _hint(_('Share this phonebook group and set permission for broadcasting SMS to this group from mobile phone')) . "</td>
				</tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&route=group&op=list');
		_p($content);
		break;

	case "edit":
		$gpid = (int) $_REQUEST['gpid'];
		$group = phonebook_getgroupbyid($gpid);
		${'selected_' . $group['flag_sender']} = 'selected';
		$option_flag_sender = "
			<option value='0' $selected_0>" . _('Me only') . "</option>
			<option value='1' $selected_1>" . _('Members') . "</option>
			<option value='2' $selected_2>" . _('Anyone') . "</option>";
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<h3 class=page-header-subtitle>" . _('Edit group') . "</h3>
			<p>
			<form action=\"index.php?app=main&inc=feature_phonebook&route=group&op=actions&go=edit\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=gpid value=\"$gpid\">
			<table class=playsms-table>
			<tbody>
			<tr>
				<td class=playsms-label-sizer>" . _('Group name') . "</td>
				<td><input type=text name=group_name value=\"" . _display(phonebook_groupid2name($user_config['uid'], $gpid)) . "\"></td>
			</tr>
			<tr>
				<td>" . _('Group code') . "</td>
				<td><input type=text name=group_code value=\"" . phonebook_groupid2code($user_config['uid'], $gpid) . "\" size=10> " . _hint(_('Please use uppercase and make it short')) . "</td>
			</tr>
			<tr>
				<td>" . _('Share phonebook and allow broadcast') . "</td>
				<td><select name=flag_sender>" . $option_flag_sender . "</select> " . _hint(_('Share this phonebook group and set permission for broadcasting SMS to this group from mobile phone')) . "</td>
			</tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&route=group&op=list');
		_p($content);
		break;

	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'delete':
				if ($gpid = $_REQUEST['gpid']) {
					if (
						!dba_count(
							_DB_PREF_ . '_featurePhonebook_group_contacts',
							[
								'gpid' => $gpid
							]
						)
					) {
						if (
							dba_remove(
								_DB_PREF_ . '_featurePhonebook_group',
								[
									'uid' => $user_config['uid'],
									'id' => $gpid
								]
							)
						) {
							$_SESSION['dialog']['info'][] = _('Selected group has been deleted');
						} else {
							$_SESSION['dialog']['danger'][] = _('Fail to delete group');
						}
					} else {
						$_SESSION['dialog']['danger'][] = _('Unable to delete group until the group is empty');
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&search_category=' . $search['category'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				header("Location: " . _u($ref));
				exit();

			case 'add':
				$group_name = $_POST['group_name'];
				$group_code = strtoupper(phonebook_code_clean($_POST['group_code']));
				$flag_sender = (int) $_POST['flag_sender'];
				$uid = $user_config['uid'];
				if ($group_name && $group_code) {
					$ret = phonebook_group_add($uid, $group_name, $group_code, $flag_sender);
					if ($ret) {
						$_SESSION['dialog']['info'][] = _('Group code has been added') . " (" . _('group') . ": $group_name, " . _('code') . ": $group_code)";
						_lastpost_empty();
					} else if ($ret === 0) {
						$_SESSION['dialog']['danger'][] = _('Group code already exists') . " (" . _('code') . ": $group_code)";
					} else {
						$_SESSION['dialog']['danger'][] = _('Unable to add group') . " (" . _('code') . ": $group_code)";
					}
				} else {
					$_SESSION['dialog']['danger'][] = _('You must fill all field');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&route=group&op=add'));
				exit();

			case 'edit':
				$gpid = $_POST['gpid'];
				$group_name = $_POST['group_name'];
				$group_code = strtoupper(phonebook_code_clean($_POST['group_code']));
				$flag_sender = (int) $_POST['flag_sender'];
				$uid = $user_config['uid'];
				if ($gpid && $group_name && $group_code) {
					$db_query = "SELECT code FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid=? AND code=? AND NOT id=?";
					$db_result = dba_query($db_query, [$uid, $group_code, $gpid]);
					if ($db_row = dba_fetch_array($db_result)) {
						$_SESSION['dialog']['danger'][] = _('No changes have been made');
					} else {
						$string_group_edit = _('Group has been edited');

						// check whether or not theres a group code with the same name and flag_sender <> 0
						if ($flag_sender > 0) {
							$db_query = "SELECT flag_sender FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE code=? AND flag_sender<>0 AND NOT id=?";
							$db_result = dba_query($db_query, [$group_code, $gpid]);
							if ($db_row = dba_fetch_array($db_result)) {
								$flag_sender = 0;
								$string_group_edit = _('Group has been edited but unable to set broadcast from members or anyone');
							}
						}

						// update data
						$db_query = "UPDATE " . _DB_PREF_ . "_featurePhonebook_group SET c_timestamp='" . time() . "',name=?,code=?,flag_sender=? WHERE uid=? AND id=?";
						$db_result = dba_query($db_query, [$group_name, $group_code, $flag_sender, $uid, $gpid]);

						$_SESSION['dialog']['info'][] = $string_group_edit . " (" . _('group') . ": $group_name, " . _('code') . ": $group_code)";
					}
				} else {
					$_SESSION['dialog']['danger'][] = _('You must fill all field');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&route=group&op=edit&gpid=' . $gpid));
				exit();
		}
		break;
}