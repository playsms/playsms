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
	case "phonebook_list":
		$db_argv = [];
		$search_category = array(
			_('Name') => 'A.name',
			_('Mobile') => 'mobile',
			_('Email') => 'email',
			_('Tags') => 'tags',
			_('Group code') => 'code'
		);
		$base_url = 'index.php?app=main&inc=feature_phonebook&op=phonebook_list';
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$keywords_sql = "";
		foreach ( $keywords as $key => $val ) {
			$keywords_sql .= " OR " . $key . " LIKE ?";
			$db_argv[] = $val;
		}
		if ($keywords_sql) {
			$keywords_sql = preg_replace('/^\sOR\s/i', '', $keywords_sql, 1); // remove first " OR "
			$keywords_sql .= " AND "; // adds trailing AND for next condition
		}

		$db_query = "
			SELECT DISTINCT A.id AS pid, A.uid AS uid, A.name AS name, A.mobile AS mobile, A.email AS email, A.tags AS tags 
			FROM " . _DB_PREF_ . "_featurePhonebook AS A 
			LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid 
			LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid 
			WHERE " . $keywords_sql . " 
				(
					A.uid = ? 
					OR B.id IN (
						SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
						LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid 
						LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid 
						WHERE A.mobile LIKE ? AND B.flag_sender=1
					) 
					OR (A.uid<>? AND B.flag_sender>1)
				)";
		$db_argv[] = (int) $user_config['uid'];
		$db_argv[] = "%" . core_mobile_matcher_format($user_config['mobile']);
		$db_argv[] = (int) $user_config['uid'];
		$count = dba_num_rows($db_query, $db_argv);
		$nav = themes_nav($count, $search['url']);

		$db_query .= " ORDER BY A.name, mobile LIMIT " . (int) $nav['limit'] . " OFFSET " . (int) $nav['offset'];
		$db_result = dba_query($db_query, $db_argv);

		$list = [];
		while ($db_row = dba_fetch_array($db_result)) {
			$list[] = $db_row;
		}

		$phonebook_groups = phonebook_getgroupbyuid($user_config['uid']);
		foreach ( $phonebook_groups as $group ) {
			$action_move_options .= '<option value=move_' . $group['gpid'] . '>' . _('Move to') . ' ' . $group['gp_name'] . ' (' . $group['gp_code'] . ')</option>';
		}

		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<p>" . $search['form'] . "</p>
			<form name=fm_phonebook_list id=fm_phonebook_list action='index.php?app=main&inc=feature_phonebook' method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden id=action_route name=route value=''>
				<div class=actions_box>
					<div class=pull-left>
						<a href='" . _u('index.php?app=main&inc=feature_phonebook&route=group&op=list') . "'>" . $icon_config['group'] . "</a>
						<a href='" . _u('index.php?app=main&inc=feature_phonebook&route=import&op=list') . "'>" . $icon_config['import'] . "</a>
						<a href='" . _u('index.php?app=main&inc=feature_phonebook&op=actions&go=export') . "'>" . $icon_config['export'] . "</a>
						<a href='" . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_add') . "'>" . $icon_config['add'] . "</a>
					</div>
					<script type='text/javascript'>
						$(document).ready(function() {
							$('#action_go').click(function(){
								$('input[name=route]').attr('value','phonebook_go');
								$('#fm_phonebook_list').submit();
							});
						});
					</script>
					<div align=right>
						<select name=op class=search_input_category>
							<option value=>" . _('Select') . "</option>
							<option value=delete>" . _('Delete') . "</option>
							" . $action_move_options . "
						</select>
						<a href='#' id=action_go>" . $icon_config['go'] . "</a>
					</div>
				</div>
				<div class=table-responsive>
				<table class=playsms-table-list>
				<thead>
				<tr>
					<th width=20%>" . _('Name') . "</th>
					<th width=20%>" . _('Mobile') . "</th>
					<th width=27%>" . _('Email') . "</th>
					<th width=15%>" . _('Group code') . "</th>
					<th width=15%>" . _('Tags') . "</th>
					<th width=3% nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_phonebook_list)></th>
				</tr>
				</thead>
				<tbody>";

		$i = $nav['top'];
		$j = 0;
		$list = _display($list);
		$c_count = count($list);
		for ($j = 0; $j < $c_count; $j++) {
			$pid = $list[$j]['pid'];
			$name = $list[$j]['name'];
			$mobile = $list[$j]['mobile'];
			$email = $list[$j]['email'];
			$tags = phonebook_tags_clean($list[$j]['tags']);

			$db_query = "
				SELECT B.id AS id, B.uid AS uid, B.code AS code, B.flag_sender AS flag_sender 
				FROM " . _DB_PREF_ . "_featurePhonebook_group AS B 
				INNER JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON C.gpid = B.id
				WHERE
					C.pid = ? 
					AND (
						B.uid = ? 
						OR B.id IN (
							SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
							LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid 
							LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid 
							WHERE A.mobile LIKE ?
							AND B.flag_sender=1
						) OR ( B.uid<>? AND B.flag_sender>1 ) 
					)
				ORDER BY B.code ASC
				LIMIT " . (int) $nav['limit'];
			$db_argv = [
				(int) $list[$j]['pid'],
				(int) $user_config['uid'],
				"%" . core_mobile_matcher_format($user_config['mobile']),
				(int) $user_config['uid']
			];
			$db_result = dba_query($db_query, $db_argv);

			$grouplist = [];
			while ($db_row = dba_fetch_array($db_result)) {
				$grouplist[] = $db_row;
			}

			$group_code = "";
			for ($k = 0; $k < count($grouplist); $k++) {
				if ($grouplist[$k]['uid'] == $user_config['uid']) {
					$group_code .= $phonebook_flag_sender[$grouplist[$k]['flag_sender']] . "<a href=\"" . _u('index.php?app=main&inc=feature_phonebook&route=group&op=edit&gpid=' . $grouplist[$k]['id']) . "\">" . strtoupper($grouplist[$k]['code']) . "</a><br />";
				} else {
					$group_code .= $phonebook_flag_sender[$grouplist[$k]['flag_sender']] . strtoupper($grouplist[$k]['code']) . "<br />";
				}
			}
			$i--;
			$c_i = "<a href=\"" . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_edit&id=' . $pid) . "\">" . $i . ".</a>";
			if ($list[$j]['uid'] == $user_config['uid']) {
				$name = "<a href='" . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_edit&pid=' . $pid) . "'>" . $name . "</a>";
			}
			$content .= "
				<tr>
					<td>$name</td>
					<td>$mobile</td>
					<td>$email</td>
					<td>$group_code</td>
					<td>$tags</td>
					<td nowrap>
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

	case "phonebook_add":
		$phone = trim(urlencode($_REQUEST['phone']));
		$uid = $user_config['uid'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid=?";
		$db_result = dba_query($db_query, [$uid]);
		$list_of_group = [];
		while ($db_row = dba_fetch_array($db_result)) {
			$list_of_group[] = '<input type=hidden name=gpids[' . $db_row['id'] . '] value=0>
				<label><input type=checkbox name=gpids[' . $db_row['id'] . '] value=1> ' . $db_row['name'] . ' - ' . _('code') . ': ' . $db_row['code'] . '</label>';
		}
		if ($list_of_group) {
			$list_of_group = "<input type=hidden name=gpids[0] value=0>\n" . implode("<br>\n", $list_of_group);
		} else {
			$list_of_group = _('No group');
		}
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<h3 class=page-header-subtitle>" . _('Add contact') . "</h3>
			<form action=\"index.php?app=main&inc=feature_phonebook&op=actions&go=add\" name=fm_addphone method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tbody>
			<tr><td class=playsms-label-sizer>" . _('Group') . "</td><td>$list_of_group</td></tr>
			<tr><td>" . _mandatory(_('Name')) . "</td><td><input type=text name=name></td></tr>
			<tr><td>" . _mandatory(_('Mobile')) . "</td><td><input type=text name=mobile maxlength=20 value=\"" . $phone . "\"></td></tr>
			<tr><td>" . _('Email') . "</td><td><input type=text name=email></td></tr>
			<tr><td>" . _('Tags') . "</td><td><input type=text name=tags> " . _hint(_('Multiple entries separated by comma')) . "</td></tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&op=phonebook_list');
		_p($content);
		break;

	case "phonebook_edit":
		$uid = $user_config['uid'];
		$pid = $_REQUEST['pid'];
		$list = dba_search(_DB_PREF_ . '_featurePhonebook', '*', ['id' => $pid, 'uid' => $uid]);

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid=?";
		$db_result = dba_query($db_query, [$uid]);
		$list_of_group = [];
		while ($db_row = dba_fetch_array($db_result)) {
			$checked = '';
			$conditions = [
				'gpid' => $db_row['id'],
				'pid' => $pid
			];
			if (dba_isexists(_DB_PREF_ . '_featurePhonebook_group_contacts', $conditions, 'AND')) {
				$checked = ' checked';
			}
			$list_of_group[] = "\n<input type=hidden name=gpids[" . $db_row['id'] . '] value=0>
				<label><input type=checkbox name=gpids[' . $db_row['id'] . "] value=1$checked> " . $db_row['name'] . ' - ' . _('code') . ': ' . $db_row['code'] . '</label>';
		}
		if ($list_of_group) {
			$list_of_group = "<input type=hidden name=gpids[0] value=0>\n" . implode("<br>\n", $list_of_group);
		} else {
			$list_of_group = _('No group');
		}
		$list = _display($list);
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Phonebook') . "</h2>
			<h3 class=page-header-subtitle>" . _('Edit contact') . "</h3>
			<form action=\"index.php?app=main&inc=feature_phonebook&op=actions&go=edit\" name=fm_addphone method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=pid value=\"" . $pid . "\">
			<table class=playsms-table>
			<tbody>
			<tr><td class=playsms-label-sizer>" . _('Group') . "</td><td>$list_of_group</td></tr>
			<tr><td>" . _mandatory(_('Name')) . "</td><td><input type=text name=name value=\"" . $list[0]['name'] . "\"></td></tr>
			<tr><td>" . _mandatory(_('Mobile')) . "</td><td><input type=text name=mobile maxlength=20 value=\"" . $list[0]['mobile'] . "\"></td></tr>
			<tr><td>" . _('Email') . "</td><td><input type=text name=email value=\"" . $list[0]['email'] . "\"></td></tr>
			<tr><td>" . _('Tags') . "</td><td><input type=text name=tags value=\"" . phonebook_tags_clean($list[0]['tags']) . "\"> " . _hint(_('Multiple entries separated by comma')) . "</td></tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&op=phonebook_list');
		_p($content);
		break;

	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$db_argv = [];
				$keywords = $search['dba_keywords'];
				$keywords_sql = "";
				foreach ( $keywords as $key => $val ) {
					$keywords_sql .= " OR " . $key . " LIKE ?";
					$db_argv[] = $val;
				}
				if ($keywords_sql) {
					$keywords_sql = preg_replace('/^\sOR\s/i', '', $keywords_sql, 1); // remove first " OR "
					$keywords_sql .= " AND "; // adds trailing AND for next condition
				}
				$db_query = "
					SELECT DISTINCT A.id AS pid, A.uid AS uid, A.name AS name, A.mobile AS mobile, A.email AS email, B.code AS code, A.tags AS tags 
					FROM " . _DB_PREF_ . "_featurePhonebook AS A 
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid 
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid 
					WHERE " . $keywords_sql . " 
						(
							A.uid = ? 
							OR B.id IN (
								SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
								LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid 
								LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid 
								WHERE A.mobile LIKE ? AND B.flag_sender=1
							) 
							OR (A.uid <>? AND B.flag_sender>1)
						)
					ORDER BY A.name, mobile
					LIMIT " . (int) $phonebook_row_limit;
				$db_argv[] = (int) $user_config['uid'];
				$db_argv[] = "%" . core_mobile_matcher_format($user_config['mobile']);
				$db_argv[] = (int) $user_config['uid'];
				$db_result = dba_query($db_query, $db_argv);
				$list = [];
				while ($db_row = dba_fetch_array($db_result)) {
					$list[] = $db_row;
				}

				$data[0] = [
					_('Name'),
					_('Mobile'),
					_('Email'),
					_('Group code'),
					_('Tags')
				];

				$c_count = count($list);
				for ($i = 0; $i < $c_count; $i++) {
					$j = $i + 1;
					$data[$j] = [
						$list[$i]['name'],
						core_sanitize_mobile($list[$i]['mobile']),
						$list[$i]['email'],
						$list[$i]['code'],
						phonebook_tags_clean($list[$i]['tags'])
					];
				}
				$content = core_csv_format($data);
				$fn = 'phonebook-' . $core_config['datetime']['now_stamp'] . '.csv';
				core_download($content, $fn, 'text/csv');
				break;

			case 'add':
				$uid = $user_config['uid'];
				$gpids = $_POST['gpids'];
				unset($gpids[0]);
				$save_to_group = false;
				$name = $_POST['name'];
				$mobile = core_sanitize_mobile($_POST['mobile']);
				$email = $_POST['email'];
				$tags = phonebook_tags_clean($_POST['tags']);
				if ($mobile && $name) {
					$list = dba_search(_DB_PREF_ . '_featurePhonebook', 'id', ['uid' => $uid, 'mobile' => $mobile]);
					// fixme anton - temporary - contacts not unique
					$save_to_group = false;
					$items = [
						'uid' => $uid,
						'name' => $name,
						'mobile' => $mobile,
						'email' => $email,
						'tags' => $tags
					];
					if ($c_pid = dba_add(_DB_PREF_ . '_featurePhonebook', $items)) {
						$save_to_group = true;
					} else {
						_log('fail to add contact pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email . ' tags:[' . $tags . ']', 3, 'phonebook_add');
					}
					foreach ( $gpids as $gpid => $add ) {
						if ($save_to_group && $add) {
							$items = [
								'gpid' => $gpid,
								'pid' => $c_pid
							];
							if (dba_isavail(_DB_PREF_ . '_featurePhonebook_group_contacts', $items, 'AND')) {
								if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $items)) {
									_log('contact added to group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_add');
								} else {
									_log('contact added but fail to save in group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_add');
								}
							}
						}
					}
					$_SESSION['dialog']['info'][] = _('Contact has been added');
				} else {
					$_SESSION['dialog']['danger'][] = _('You must fill required fields');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_add'));
				exit();

			case 'edit':
				$uid = $user_config['uid'];
				$c_pid = $_POST['pid'];
				$gpids = $_POST['gpids'];
				unset($gpids[0]);
				$save_to_group = false;
				$name = $_POST['name'];
				$mobile = core_sanitize_mobile($_POST['mobile']);
				$email = $_POST['email'];
				$tags = phonebook_tags_clean($_POST['tags']);
				if ($c_pid && $mobile && $name) {
					$items = [
						'name' => $name,
						'mobile' => $mobile,
						'email' => $email,
						'tags' => $tags
					];
					$conditions = [
						'id' => $c_pid,
						'uid' => $uid
					];
					dba_update(_DB_PREF_ . '_featurePhonebook', $items, $conditions, 'AND');
					_log('contact edited pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill mandatory fields');
					header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_list'));
					exit();
				}
				foreach ( $gpids as $gpid => $add ) {
					if ($add) {
						$items = [
							'gpid' => $gpid,
							'pid' => $c_pid
						];
						if (dba_isavail(_DB_PREF_ . '_featurePhonebook_group_contacts', $items, 'AND')) {
							if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $items)) {
								_log('contact added to group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
							} else {
								_log('contact edited but fail to save in group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
							}
						}
					} else {
						dba_remove(
							_DB_PREF_ . '_featurePhonebook_group_contacts',
							[
								'gpid' => $gpid,
								'pid' => $c_pid
							]
						);
					}
				}
				$_SESSION['dialog']['info'][] = _('Contact has been edited');
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_list'));
				exit();
		}
		break;
}