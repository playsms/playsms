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

if (!auth_isuser()) {
	if (!auth_isadmin()) {
		auth_block();
	}
}

if ($_REQUEST['uname']) {
	$subuser_edited = user_getdatabyusername($_REQUEST['uname']);
	if (($subuser_edited['status'] != 4) || ($subuser_edited['parent_uid'] != $user_config['uid'])) {
		auth_block();
	}
}

switch (_OP_) {
	case "subuser_list":
		$search_var = array(
			_('Registered') => 'register_datetime',
			_('Username') => 'username',
			_('Name') => 'name',
			_('Mobile') => 'mobile',
			_('ACL') => 'acl_id' 
		);
		$search = themes_search($search_var, '', array(
			'acl_id' => 'acl_getid' 
		));
		$conditions = array(
			'flag_deleted' => 0,
			'status' => 4,
			'parent_uid' => $user_config['uid'] 
		);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblUser', $conditions, $keywords);
		$nav = themes_nav($count, "index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list");
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
			<h2>" . _('Manage subuser') . "</h2>
			<h3>" . _('List of subusers') . "</h3>
			<p>" . $search['form'] . "</p>			
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_add') . "\">" . $icon_config['add'] . "</a>
				</div>
				<div class=pull-right>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='15%'>" . _('Registered') . "</th>
				<th width='15%'>" . _('Username') . "</th>
				<th width='15%'>" . _('Name') . "</th>
				<th width='15%'>" . _('Mobile') . "</th>
				<th width='10%'>" . _('Credit') . "</th>
				<th width='15%'>" . _('ACL') . "</th>
				<th width='15%'>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i = 0; $i < count($list); $i++) {
			
			$action = "";
			
			// login as
			if ($list[$i]['uid'] != $user_config['uid']) {
				$main_config = $core_config['main'];
				if (!$main_config['disable_login_as'] || auth_isadmin()) {
					$action = "<a href=\"" . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=login_as&uname=' . $list[$i]['username']) . "\">" . $icon_config['login_as'] . "</a>";
				}
			}
			
			// subuser preferences
			$action .= "<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_pref&op=user_pref&uname=' . $list[$i]['username']) . "\">" . $icon_config['user_pref'] . "</a>";
			
			// subuser configurations
			$action .= "<a href=\"" . _u('index.php?app=main&inc=core_user&route=user_config&op=user_config&uname=' . $list[$i]['username']) . "\">" . $icon_config['user_config'] . "</a>";
			
			if ($list[$i]['uid'] != '1' || $list[$i]['uid'] != $user_config['uid']) {
				if (user_banned_get($list[$i]['uid'])) {
					// unban
					$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to unban subuser")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_unban&uname=' . $list[$i]['username']) . "')\">" . $icon_config['unban'] . "</a>";
					$banned_icon = $icon_config['ban'];
				} else {
					// ban
					$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to ban subuser")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_ban&uname=' . $list[$i]['username']) . "')\">" . $icon_config['ban'] . "</a>";
					$banned_icon = '';
				}
			}
			
			// remove subuser
			$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete subuser")) . " " . $list[$i]['username'] . " ?','" . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_del&uname=' . $list[$i]['username']) . "')\">" . $icon_config['user_delete'] . "</a>";
			
			$j--;
			$content .= "
				<tr>
					<td>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					<td>" . $banned_icon . "" . $list[$i]['username'] . " </td>
					<td>" . $list[$i]['name'] . "</td>
					<td>" . $list[$i]['mobile'] . "</td>	
					<td>" . rate_getusercredit($list[$i]['username']) . "</td>
					<td>" . acl_getnamebyuid($list[$i]['uid']) . "</td>	
					<td>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>";
		_p($content);
		break;
	
	case "subuser_add":
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
		
		// get access control list
		$option_acl = _select('add_acl_id', array_flip(acl_getallbyuid($user_config['uid'])));
		
		$content .= "
		<h2>" . _('Manage subuser') . "</h2>
		<h3>" . _('Add subuser') . "</h3>
		<form action='index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_add_yes' method=POST>
		" . _CSRF_FORM_ . "
		<table class=playsms-table>
		<tbody>
		<tr>
			<td class=label-sizer>" . _('Access Control List') . "</td><td>" . $option_acl . "</td>
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
		" . _back('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list');
		_p($content);
		break;
	
	case "subuser_add_yes":
		$add['acl_id'] = (int) $_POST['add_acl_id'];
		$add['email'] = $_POST['add_email'];
		$add['username'] = $_POST['add_username'];
		$add['password'] = $_POST['add_password'];
		$add['mobile'] = $_POST['add_mobile'];
		$add['name'] = $_POST['add_name'];
		$add['footer'] = $_POST['add_footer'];
		$add['datetime_timezone'] = $_POST['add_datetime_timezone'];
		$add['language_module'] = $_POST['add_language_module'];
		
		// subuser settings
		$add['parent_uid'] = $user_config['uid'];
		$add['status'] = 4;
		
		// set credit to 0 by default
		$add['credit'] = 0;
		
		// add user
		$ret = user_add($add);
		
		if (is_array($ret)) {
			$_SESSION['dialog']['info'][] = $ret['error_string'];
		} else {
			$_SESSION['dialog']['info'][] = _('Unable to process subuser addition');
		}
		
		header("Location: " . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_add'));
		exit();
		break;
	
	case "subuser_del":
		$up['username'] = $subuser_edited['username'];
		$del_uid = user_username2uid($up['username']);
		$ret = user_remove($del_uid);
		$_SESSION['dialog']['info'][] = $ret['error_string'];
		header("Location: " . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list'));
		exit();
		break;
	
	case "subuser_unban":
		$uid = $subuser_edited['uid'];
		if ($uid && ($uid == 1 || $uid == $user_config['uid'])) {
			$_SESSION['dialog']['info'][] = _('Account admin or currently logged in administrator cannot be unbanned');
		} else if (user_banned_get($uid)) {
			if (user_banned_remove($uid)) {
				$_SESSION['dialog']['info'][] = _('Account has been unbanned') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
			} else {
				$_SESSION['dialog']['info'][] = _('Unable to unban subuser') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
			}
		} else {
			$_SESSION['dialog']['info'][] = _('User is not on banned subusers list') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
		}
		header("Location: " . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list'));
		exit();
		break;
	
	case "subuser_ban":
		$uid = $subuser_edited['uid'];
		if ($uid && ($uid == 1 || $uid == $user_config['uid'])) {
			$_SESSION['dialog']['info'][] = _('Account admin or currently logged in administrator cannot be unbanned');
		} else if (user_banned_get($uid)) {
			$_SESSION['dialog']['info'][] = _('User is already on banned subusers list') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
		} else {
			if (user_banned_add($uid)) {
				$_SESSION['dialog']['info'][] = _('Account has been banned') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
			} else {
				$_SESSION['dialog']['info'][] = _('Unable to ban subuser') . ' (' . _('username') . ': ' . $subuser_edited['username'] . ')';
			}
		}
		header("Location: " . _u('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list'));
		exit();
		break;
	
	case "login_as":
		user_session_remove($_SESSION['uid'], $_SESSION['sid']);
		$uid = user_username2uid($_REQUEST['uname']);
		auth_login_as($uid);
		if (auth_isvalid()) {
			logger_print("parent login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "subuser_mgmnt");
		} else {
			logger_print("parent fail to login as u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "subuser_mgmnt");
		}
		header('Location: ' . _u(_HTTP_PATH_BASE_));
		exit();
		break;
}
