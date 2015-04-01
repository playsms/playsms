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
		$search_category = array(
			_('Name') => 'A.name',
			_('Mobile') => 'mobile',
			_('Email') => 'email',
			_('Tags') => 'tags',
			_('Group code') => 'code' 
		);
		$base_url = 'index.php?app=main&inc=feature_phonebook&op=phonebook_list';
		$search = themes_search($search_category, $base_url);
		
		$fields = 'DISTINCT A.id AS pid, A.uid AS uid, A.name AS name, A.mobile AS mobile, A.email AS email, A.tags AS tags';
		$join = 'LEFT JOIN ' . _DB_PREF_ . '_featurePhonebook_group_contacts AS C ON A.id=C.pid ';
		$join .= 'LEFT JOIN ' . _DB_PREF_ . '_featurePhonebook_group AS B ON B.id=C.gpid';
		$conditions = array(
			'( A.uid' => $user_config['uid'] . "' OR B.id in (
				SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
				" . $join . "
				WHERE A.mobile LIKE '%" . core_mobile_matcher_format($user_config['mobile']) . "'
				AND B.flag_sender='1'
				) OR ( A.uid <>'" . $user_config['uid'] . "' AND B.flag_sender>'1' ) ) AND '1'='1" 
		);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_featurePhonebook AS A', $conditions, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'ORDER BY' => 'A.name, mobile',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_featurePhonebook AS A', $fields, $conditions, $keywords, $extras, $join);
		
		$phonebook_groups = phonebook_getgroupbyuid($user_config['uid']);
		foreach ($phonebook_groups as $group) {
			$action_move_options .= '<option value=move_' . $group['gpid'] . '>' . _('Move to') . ' ' . $group['gp_name'] . ' (' . $group['gp_code'] . ')</option>';
		}
		
		$content = "
			<h2>" . _('Phonebook') . "</h2>
			<p>" . $search['form'] . "</p>
			<form name=fm_phonebook_list id=fm_phonebook_list action='index.php?app=main&inc=feature_phonebook' method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden id=action_route name=route value=''>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
					<td colspan=6>
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
							<div class=pull-right>
								<select name=op class=search_input_category>
									<option value=>" . _('Select') . "</option>
									<option value=delete>" . _('Delete') . "</option>
									" . $action_move_options . "
								</select>
								<a href='#' id=action_go>" . $icon_config['go'] . "</a>
							</div>
						</div>
					</td>
			</tr>
			<tr>
				<th width=20%>" . _('Name') . "</th>
				<th width=20%>" . _('Mobile') . "</th>
				<th width=25%>" . _('Email') . "</th>
				<th width=15%>" . _('Group code') . "</th>
				<th width=15%>" . _('Tags') . "</th>
				<th width=5%><input type=checkbox onclick=CheckUncheckAll(document.fm_phonebook_list)></th>
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$pid = $list[$j]['pid'];
			$name = $list[$j]['name'];
			$mobile = $list[$j]['mobile'];
			$email = $list[$j]['email'];
			$tags = $list[$j]['tags'];
			$group_code = "";
			$groupfields = 'B.id AS id, B.uid AS uid, B.code AS code, B.flag_sender AS flag_sender';
			$groupconditions = array(
				'C.pid' => $list[$j]['pid'],
				'( B.uid' => $user_config['uid'] . "' OR B.id in (
					SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
					" . $join . "
					WHERE A.mobile LIKE '%" . core_mobile_matcher_format($user_config['mobile']) . "'
					AND B.flag_sender='1'
					) OR ( B.uid<>'" . $user_config['uid'] . "' AND B.flag_sender>'1' ) ) AND '1'='1" 
			);
			$groupextras = array(
				'ORDER BY' => 'B.code ASC',
				'LIMIT' => $nav['limit'] 
			);
			$groupjoin = 'INNER JOIN ' . _DB_PREF_ . '_featurePhonebook_group_contacts AS C ON C.gpid = B.id';
			$grouplist = dba_search(_DB_PREF_ . '_featurePhonebook_group AS B', $groupfields, $groupconditions, '', $groupextras, $groupjoin);
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
					<td>
						<input type=hidden name=itemid[" . $j . "] value=\"$pid\">
						<input type=checkbox name=checkid[" . $j . "]>
					</td>
				</tr>";
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>";
		
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	case "phonebook_add":
		$phone = trim(urlencode($_REQUEST['phone']));
		$uid = $user_config['uid'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$list_of_group = "<option value=0 selected>-- " . _('No group') . " --</option>";
		while ($db_row = dba_fetch_array($db_result)) {
			$list_of_group .= "<option value=" . $db_row['id'] . ">" . $db_row['name'] . " - " . _('code') . ": " . $db_row['code'] . "</option>";
		}
		$content = _dialog() . "
			<h2>" . _('Phonebook') . "</h2>
			<h3>" . _('Add contact') . "</h3>
			<form action=\"index.php?app=main&inc=feature_phonebook&op=actions&go=add\" name=fm_addphone method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tbody>
			<tr><td class=label-sizer>" . _('Group') . "</td><td><select name=gpids[] multiple>$list_of_group</select></td></tr>
			<tr><td>" . _mandatory(_('Name')) . "</td><td><input type=text name=name></td></tr>
			<tr><td>" . _mandatory(_('Mobile')) . "</td><td><input type=text name=mobile maxlength=20 value=\"" . $phone . "\"></td></tr>
			<tr><td>" . _('Email') . "</td><td><input type=text name=email></td></tr>
			<tr><td>" . _('Tags') . "</td><td><input type=text name=tags> " . _hint(_('Multiple entries separated by space')) . "</td></tr>
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
		$list = dba_search(_DB_PREF_ . '_featurePhonebook', '*', array(
			'id' => $pid,
			'uid' => $uid 
		));
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$list_of_group = "<option value=0>-- " . _('No group') . " --</option>";
		while ($db_row = dba_fetch_array($db_result)) {
			$selected = '';
			$conditions = array(
				'gpid' => $db_row['id'],
				'pid' => $pid 
			);
			if (dba_isexists(_DB_PREF_ . '_featurePhonebook_group_contacts', $conditions, 'AND')) {
				$selected = 'selected';
			}
			$list_of_group .= "<option value=" . $db_row['id'] . " $selected>" . $db_row['name'] . " - " . _('code') . ": " . $db_row['code'] . "</option>";
		}
		$content = "
			<h2>" . _('Phonebook') . "</h2>
			<h3>" . _('Edit contact') . "</h3>
			<form action=\"index.php?app=main&inc=feature_phonebook&op=actions&go=edit\" name=fm_addphone method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=pid value=\"" . $pid . "\">
			<table class=playsms-table>
			<tbody>
			<tr><td class=label-sizer>" . _('Group') . "</td><td><select name=gpids[] multiple>$list_of_group</select></td></tr>
			<tr><td>" . _mandatory(_('Name')) . "</td><td><input type=text name=name value=\"" . $list[0]['name'] . "\"></td></tr>
			<tr><td>" . _mandatory(_('Mobile')) . "</td><td><input type=text name=mobile maxlength=20 value=\"" . $list[0]['mobile'] . "\"></td></tr>
			<tr><td>" . _('Email') . "</td><td><input type=text name=email value=\"" . $list[0]['email'] . "\"></td></tr>
			<tr><td>" . _('Tags') . "</td><td><input type=text name=tags value=\"" . $list[0]['tags'] . "\"> " . _hint(_('Multiple entries separated by space')) . "</td></tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\"></p>
			</form>
			" . _back('index.php?app=main&inc=feature_phonebook&op=phonebook_list');
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$fields = 'DISTINCT A.id AS pid, A.uid AS uid, A.name AS name, A.mobile AS mobile, A.email AS email, B.code AS code, A.tags AS tags';
				$join = 'LEFT JOIN ' . _DB_PREF_ . '_featurePhonebook_group_contacts AS C ON A.id=C.pid ';
				$join .= 'LEFT JOIN ' . _DB_PREF_ . '_featurePhonebook_group AS B ON B.id=C.gpid';
				$conditions = array(
					'( A.uid' => $user_config['uid'] . "' OR B.id in (
						SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
						" . $join . "
						WHERE A.mobile LIKE '%" . core_mobile_matcher_format($user_config['mobile']) . "'
						AND B.flag_sender='1'
						) OR ( A.uid <>'" . $user_config['uid'] . "' AND B.flag_sender>'1' ) ) AND '1'='1" 
				);
				$keywords = $search['dba_keywords'];
				$extras = array(
					'ORDER BY' => 'A.name, mobile',
					'LIMIT' => $phonebook_row_limit 
				);
				$list = dba_search(_DB_PREF_ . '_featurePhonebook AS A', $fields, $conditions, $keywords, $extras, $join);
				$data[0] = array(
					_('Name'),
					_('Mobile'),
					_('Email'),
					_('Group code'),
					_('Tags') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['name'],
						sendsms_getvalidnumber($list[$i]['mobile']),
						$list[$i]['email'],
						$list[$i]['code'],
						phonebook_tags_clean($list[$i]['tags']) 
					);
				}
				$content = core_csv_format($data);
				$fn = 'phonebook-' . $core_config['datetime']['now_stamp'] . '.csv';
				core_download($content, $fn, 'text/csv');
				break;
			case 'add':
				$uid = $user_config['uid'];
				$gpids = $_POST['gpids'];
				$save_to_group = FALSE;
				$name = str_replace("\'", "", $_POST['name']);
				$name = str_replace("\"", "", $name);
				$mobile = str_replace("\'", "", $_POST['mobile']);
				$mobile = sendsms_getvalidnumber(str_replace("\"", "", $mobile));
				$email = str_replace("\'", "", $_POST['email']);
				$email = str_replace("\"", "", $email);
				$tags = phonebook_tags_clean($_POST['tags']);
				if ($mobile && $name) {
					$list = dba_search(_DB_PREF_ . '_featurePhonebook', 'id', array(
						'uid' => $uid,
						'mobile' => $mobile 
					));
					if ($c_pid = $list[0]['id']) {
						$save_to_group = TRUE;
					} else {
						$items = array(
							'uid' => $uid,
							'name' => $name,
							'mobile' => $mobile,
							'email' => $email,
							'tags' => $tags 
						);
						if ($c_pid = dba_add(_DB_PREF_ . '_featurePhonebook', $items)) {
							$save_to_group = TRUE;
						} else {
							logger_print('fail to add contact pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email . ' tags:[' . $tags . ']', 3, 'phonebook_add');
						}
					}
					foreach ($gpids as $gpid) {
						if ($save_to_group) {
							$items = array(
								'gpid' => $gpid,
								'pid' => $c_pid 
							);
							if (dba_isavail(_DB_PREF_ . '_featurePhonebook_group_contacts', $items, 'AND')) {
								if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $items)) {
									logger_print('contact added to group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_add');
								} else {
									logger_print('contact added but fail to save in group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_add');
								}
							}
						}
					}
					$_SESSION['dialog']['info'][] = _('Contact has been added');
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill required fields');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_add'));
				exit();
				break;
			case 'edit':
				$uid = $user_config['uid'];
				$c_pid = $_POST['pid'];
				$gpids = $_POST['gpids'];
				$maps = '';
				$save_to_group = FALSE;
				$mobile = str_replace("\'", "", $_POST['mobile']);
				$mobile = sendsms_getvalidnumber(str_replace("\"", "", $mobile));
				$name = str_replace("\'", "", $_POST['name']);
				$name = str_replace("\"", "", $name);
				$email = str_replace("\'", "", $_POST['email']);
				$email = str_replace("\"", "", $email);
				$tags = phonebook_tags_clean($_POST['tags']);
				if ($c_pid && $mobile && $name) {
					$items = array(
						'name' => $name,
						'mobile' => $mobile,
						'email' => $email,
						'tags' => $tags 
					);
					$conditions = array(
						'id' => $c_pid,
						'uid' => $uid 
					);
					dba_update(_DB_PREF_ . '_featurePhonebook', $items, $conditions, 'AND');
					logger_print('contact edited pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill mandatory fields');
					header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_list'));
					exit();
					break;
				}
				foreach ($gpids as $gpid) {
					$maps[][$c_pid] = $gpid;
				}
				dba_remove(_DB_PREF_ . '_featurePhonebook_group_contacts', array(
					'pid' => $c_pid 
				));
				foreach ($maps as $map) {
					foreach ($map as $key => $val) {
						$gpid = $val;
						$c_pid = $key;
						$items = array(
							'gpid' => $gpid,
							'pid' => $c_pid 
						);
						if (dba_isavail(_DB_PREF_ . '_featurePhonebook_group_contacts', $items, 'AND')) {
							if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $items)) {
								logger_print('contact added to group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
							} else {
								logger_print('contact edited but fail to save in group gpid:' . $gpid . ' pid:' . $c_pid . ' m:' . $mobile . ' n:' . $name . ' e:' . $email, 3, 'phonebook_edit');
							}
						}
					}
				}
				$_SESSION['dialog']['info'][] = _('Contact has been edited');
				header("Location: " . _u('index.php?app=main&inc=feature_phonebook&op=phonebook_list'));
				exit();
				break;
		}
		break;
}
