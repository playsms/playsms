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

$view = ($_REQUEST['view'] ? $_REQUEST['view'] : 'admin');

switch (_OP_) {
	case "user_list":
		if ($view == 'admin') {
			$conditions = array(
				'flag_deleted' => 0,
				'status' => 2 
			);
			$form_sub_title = "<h3>" . _('List of administrators') . "</h3>";
			$disabled_on_admin = 'disabled';
		} else if ($view == 'users') {
			$conditions = array(
				'flag_deleted' => 0,
				'status' => 3 
			);
			$form_sub_title = "<h3>" . _('List of users') . "</h3>";
			$disabled_on_users = 'disabled';
		} else if ($view == 'subusers') {
			$conditions = array(
				'flag_deleted' => 0,
				'status' => 4 
			);
			$form_sub_title = "<h3>" . _('List of subusers') . "</h3>";
			$disabled_on_subusers = 'disabled';
			$parent_column_title = "<th width='12%'>" . _('Parent') . "</th>";
		}
		
		$search_var = array(
			_('Registered') => 'register_datetime',
			_('Username') => 'username',
			_('Name') => 'name',
			_('Mobile') => 'mobile',
			_('ACL') => 'acl_id' 
		);
		if ($view == 'subusers') {
			$search_var[_('Parent account')] = 'parent_uid';
		}
		
		$search = themes_search($search_var, '', array(
			'parent_uid' => 'user_username2uid',
			'acl_id' => 'acl_getid' 
		));
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblUser', $conditions, $keywords);
		$nav = themes_nav($count, "index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=" . $view);
		$extras = array(
			'ORDER BY' => 'register_datetime DESC, username',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_tblUser', '*', $conditions, $keywords, $extras);
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage account') . "</h2>
			<input type='button' " . $disabled_on_admin . " value='" . _('Administrators') . "' onClick=\"javascript:linkto('" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=admin') . "')\" class=\"button\" />
			<input type='button' " . $disabled_on_users . " value='" . _('Users') . "' onClick=\"javascript:linkto('" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=users') . "')\" class=\"button\" />
			<input type='button' " . $disabled_on_subusers . " value='" . _('Subusers') . "' onClick=\"javascript:linkto('" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=subusers') . "')\" class=\"button\" />
			" . $form_sub_title . "
			<p>" . $search['form'] . "</p>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_add&view=' . $view) . "\">" . $icon_config['add'] . "</a>
				</div>
				<div class=pull-right>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='14%'>" . _('Registered') . "</th>
				" . $parent_column_title . "
				<th width='12%'>" . _('Username') . "</th>
				<th width='14%'>" . _('Name') . "</th>
				<th width='14%'>" . _('Mobile') . "</th>
				<th width='10%'>" . _('Credit') . "</th>
				<th width='12%'>" . _('ACL') . "</th>										
				<th width='12%'>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i = 0; $i < count($list); $i++) {
			
			$action = "";
			
			// login as
			if ($list[$i]['uid'] != $user_config['uid']) {
				$action .= "<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=login_as&uname=' . $list[$i]['username']) . "\">" . $icon_config['login_as'] . "</a>";
			}
			
			// user preferences
			$action .= "<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_pref&op=user_pref&uname=' . $list[$i]['username']) . "&view=" . $view . "\">" . $icon_config['user_pref'] . "</a>";
			
			// user configurations
			$action .= "<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_config&op=user_config&uname=' . $list[$i]['username']) . "&view=" . $view . "\">" . $icon_config['user_config'] . "</a>";
			
			if ($list[$i]['uid'] != '1' || $list[$i]['uid'] != $user_config['uid']) {
				if (user_banned_get($list[$i]['uid'])) {
					// unban
					$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to unban account")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_unban&uname=' . $list[$i]['username']) . "&view=" . $view . "')\">" . $icon_config['unban'] . "</a>";
					$banned_icon = $icon_config['ban'];
				} else {
					// ban
					$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to ban account")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_ban&uname=' . $list[$i]['username']) . "&view=" . $view . "')\">" . $icon_config['ban'] . "</a>";
					$banned_icon = '';
				}
			}
			
			// remove user except those who still have subusers
			$subusers = user_getsubuserbyuid($list[$i]['uid']);
			if (count($subusers) > 0) {
				$action .= _hint(_('Please remove all subusers from this user to delete'));
			} else {
				$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete user")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_del&uname=' . $list[$i]['username']) . "&view=" . $view . "')\">" . $icon_config['user_delete'] . "</a>";
			}
			
			// subuser shows parent column
			if ($list[$i]['status'] == 4) {
				$isadmin = (user_getfieldbyuid($list[$i]['parent_uid'], 'status') == 2 ? $icon_config['admin'] : '');
				$parent_column_row = "<td>" . user_uid2username($list[$i]['parent_uid']) . " " . $isadmin . "</td>";
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
					<td>" . acl_getnamebyuid($list[$i]['uid']) . "</td>
					<td>" . $action . "</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>";
		_p($content);
		break;
	
	case "user_add":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$add_datetime_timezone = $_REQUEST['add_datetime_timezone'];
		$add_datetime_timezone = ($add_datetime_timezone ? $add_datetime_timezone : core_get_timezone());
		
		// get language options
		$lang_list = '';
		for ($i = 0; $i < count($core_config['languagelist']); $i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $plugin_config[$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == core_lang_get()) $selected = "selected";
				$option_language_module .= "<option value=\"" . $val . "\" $selected>" . $key . "</option>";
				$selected = "";
			}
		}
		
		// get list of users as parents
		$default_parent_uid = ($parent_uid && ($parent['uid'] == $user_edited['parent_uid']) ? $parent['uid'] : $core_config['main']['default_parent']);
		$select_parents = themes_select_account_level_single(3, 'add_parent_uid', $default_parent_uid);
		
		if ($view == 'admin') {
			$selected_admin = 'selected';
		} else if ($view == 'users') {
			$selected_users = 'selected';
		} else if ($view == 'subusers') {
			$selected_subusers = 'selected';
		}
		
		$option_status = "
			<option value='2' " . $selected_admin . ">" . _('Administrator') . "</option>
			<option value='3' " . $selected_users . ">" . _('User') . "</option>
			<option value='4' " . $selected_subusers . ">" . _('Subuser') . "</option>
		";
		
		// get access control list
		$option_acl = _select('add_acl_id', array_flip(acl_getall()));
		
		$content .= "
		<h2>" . _('Manage account') . "</h2>
		<h3>" . _('Add account') . "</h3>
		<form action='index.php?app=main&inc=core_user&route=user_mgmnt&op=user_add_yes&view=" . $view . "' method=POST>
		" . _CSRF_FORM_ . "
		<table class=playsms-table>
		<tbody>
		<tr>
			<td class=label-sizer>" . _('Account status') . "</td><td><select name='add_status'>$option_status</select></td>
		</tr>
		<tr>
			<td>" . _('Access Control List') . "</td><td>" . $option_acl . "</td>
		</tr>
		<tr>
			<td>" . _('Parent account') . " (" . _('for subuser only') . ") </td><td>" . $select_parents . " " . _hint(_('Parent account is mandatory for subusers only. If no value is given then the subuser will be automatically assigned to user admin')) . "</td>
		</tr>
		<tr>
			<td>" . _mandatory(_('Username')) . "</td><td><input type='text' maxlength='30' name='add_username' value=\"$add_username\"></td>
		</tr>
		<tr>
			<td>" . _mandatory(_('Password')) . "</td><td><input type='password' maxlength='30' name='add_password' value=\"$add_password\"></td>
		</tr>
		<tr>
			<td>" . _mandatory(_('Full name')) . "</td><td><input type='text' maxlength='100' name='add_name' value=\"$add_name\"></td>
		</tr>
		<tr>
			<td>" . _mandatory(_('Email')) . "</td><td><input type='text' maxlength='250' name='add_email' value=\"$add_email\"></td>
		</tr>
		<tr>
			<td>" . _('Mobile') . "</td><td><input type='text' size='16' maxlength='16' name='add_mobile' value=\"$add_mobile\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric characters')) . "</td>
		</tr>
		<tr>
			<td>" . _('SMS footer') . "</td><td><input type='text' maxlength='30' name='add_footer' value=\"$add_footer\"> " . _hint(_('Max. 30 alphanumeric characters')) . "</td>
		</tr>
		<tr>
			<td>" . _('Timezone') . "</td><td><input type='text' size='5' maxlength='5' name='add_datetime_timezone' value=\"$add_datetime_timezone\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
		</tr>
		<tr>
			<td>" . _('Active language') . "</td><td><select name='add_language_module'>$option_language_module</select></td>
		</tr>
		</tbody>
		</table>
		<p><input type='submit' class='button' value='" . _('Save') . "'></p>
		</form>
		" . _back('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view);
		_p($content);
		break;
	
	case "user_add_yes":
		$add['email'] = $_POST['add_email'];
		$add['status'] = $_POST['add_status'];
		$add['acl_id'] = (int) $_POST['add_acl_id'];
		$add['username'] = $_POST['add_username'];
		$add['password'] = $_POST['add_password'];
		$add['mobile'] = $_POST['add_mobile'];
		$add['name'] = $_POST['add_name'];
		$add['footer'] = $_POST['add_footer'];
		$add['datetime_timezone'] = $_POST['add_datetime_timezone'];
		$add['language_module'] = $_POST['add_language_module'];
		
		// subuser's parent uid, by default its uid=1
		if ($_POST['add_parent_uid']) {
			$add['parent_uid'] = ($add['status'] == 4 ? $_POST['add_parent_uid'] : $core_config['main']['default_parent']);
		} else {
			$add['parent_uid'] = $core_config['main']['default_parent'];
		}
		
		// set credit to 0 by default
		$add['credit'] = 0;
		
		// add user
		$ret = user_add($add);
		
		if (is_array($ret)) {
			$_SESSION['dialog']['info'][] = $ret['error_string'];
		} else {
			$_SESSION['dialog']['info'][] = _('Unable to process user addition');
		}
		
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_add&view=' . $view));
		exit();
		break;
	
	case "user_del":
		$up['username'] = $_REQUEST['uname'];
		$del_uid = user_username2uid($up['username']);
		
		// users cannot be removed if they still have subusers
		$subusers = user_getsubuserbyuid($del_uid);
		if (count($subusers) > 0) {
			$ret['error_string'] = _('Unable to delete this user until all subusers under this user have been removed');
		} else {
			$ret = user_remove($del_uid);
		}
		
		$_SESSION['dialog']['info'][] = $ret['error_string'];
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view));
		exit();
		break;
	
	case "user_unban":
		$uid = user_username2uid($_REQUEST['uname']);
		if (user_banned_get($uid)) {
			if (user_banned_remove($uid)) {
				$_SESSION['dialog']['info'][] = _('Account has been unbanned') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
			} else {
				$_SESSION['dialog']['info'][] = _('Unable to unban account') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
			}
		} else {
			$_SESSION['dialog']['info'][] = _('User is not on banned users list') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
		}
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view));
		exit();
		break;
	
	case "user_ban":
		$uid = user_username2uid($_REQUEST['uname']);
		if ($uid && ($uid == 1 || $uid == $user_config['uid'])) {
			$_SESSION['dialog']['info'][] = _('Account admin or currently logged in administrator cannot be banned');
		} else if (user_banned_get($uid)) {
			$_SESSION['dialog']['info'][] = _('User is already on banned users list') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
		} else {
			if (user_banned_add($uid)) {
				$_SESSION['dialog']['info'][] = _('Account has been banned') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
			} else {
				$_SESSION['dialog']['info'][] = _('Unable to ban account') . ' (' . _('username') . ': ' . $_REQUEST['uname'] . ')';
			}
		}
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view));
		exit();
		break;
	
	case "login_as":
		user_session_remove($_SESSION['uid'], $_SESSION['sid']);
		$uid = user_username2uid($_REQUEST['uname']);
		auth_login_as($uid);
		if (auth_isvalid()) {
			logger_print("login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "user_mgmnt");
		} else {
			logger_print("fail to login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "user_mgmnt");
		}
		header('Location: ' . _u(_HTTP_PATH_BASE_));
		exit();
		break;
}
