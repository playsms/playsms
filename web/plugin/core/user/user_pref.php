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

$view = $_REQUEST['view'];

$uname = $_REQUEST['uname'];

if ((!$uname) || ($uname && $uname == $user_config['username'])) {
	$user_edited = $user_config;
	$c_username = $user_config['username'];
} else if (auth_isadmin()) {
	$user_edited = user_getdatabyusername($uname);
	$c_username = $uname;
	$url_uname = '&uname=' . $uname;
} else {
	$user_edited = user_getdatabyusername($uname);
	$c_username = $uname;
	$url_uname = '&uname=' . $uname;
	if ($user_edited['parent_uid'] == $user_config['uid']) {
		$is_parent = TRUE;
	} else {
		auth_block();
	}
}

$show_status_hint = FALSE;
$allow_edit_status = FALSE;
$allow_edit_parent = FALSE;

if (auth_isadmin()) {
	// if edited user IS NOT currently logged in admin or admin with user ID 1 (username: admin) or status is admin
	if (!(($user_edited['uid'] == $user_config['uid']) || ($user_edited['uid'] == 1) || ($user_edited['status'] == 2))) {
		$allow_edit_status = TRUE;
	}

	$list = user_getsubuserbyuid($user_edited['uid']);
	if (count($list) > 0) {
		$show_status_hint = TRUE;
		$allow_edit_status = FALSE;
	}

	if ($user_edited['status'] == 4) {
		$allow_edit_parent = TRUE;
	}
}

switch (_OP_) {
	case "user_pref" :
		if ($c_user = dba_search(_DB_PREF_ . '_tblUser', '*', array(
			'username' => $c_username
		))) {
			if ($allow_edit_status) {
				$status = $c_user[0]['status'];
			}
			if ($allow_edit_parent) {
				$parent_uid = $c_user[0]['parent_uid'];
			}
			$name = $c_user[0]['name'];
			$email = $c_user[0]['email'];
			$mobile = $c_user[0]['mobile'];
			$address = $c_user[0]['address'];
			$city = $c_user[0]['city'];
			$state = $c_user[0]['state'];
			$country = $c_user[0]['country'];
			$zipcode = $c_user[0]['zipcode'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
		} else {
			$_SESSION['error_string'] = _('User does not exist') . ' (' . _('username') . ': ' . $uname . ')';
			header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view));
			exit();
		}

		if ($allow_edit_status) {
			if ($user_edited['status'] == 3) {
				$selected_users = 'selected';
			} else {
				$selected_subusers = 'selected';
			}
			$option_status = "
				<option value='3' " . $selected_users . ">" . _('User') . "</option>
				<option value='4' " . $selected_subusers . ">" . _('Subuser') . "</option>
			";
			$select_status = '<select name="up_status">' . $option_status . '</select>';
		}

		// when allowed to edit parents of subusers
		if ($allow_edit_parent) {
			// get list of users as parents
			$option_parents = '<option value="0">--' . _('Select parent user for subuser') . '--</option>';

			// get admins
			$list = user_getallwithstatus(2);
			foreach ($list as $parent ) {
				if ($parent['uid'] == $user_edited['parent_uid']) {
					$selected = 'selected';
				}
				$option_parents .= '<option value="' . $parent['uid'] . '" ' . $selected . '>' . $parent['username'] . ' - ' . _('Administrator') . '</option>';
				$selected = '';
			}

			// get users
			$list = user_getallwithstatus(3);
			foreach ($list as $parent ) {
				if ($parent['uid'] == $user_edited['parent_uid']) {
					$selected = 'selected';
				}
				$option_parents .= '<option value="' . $parent['uid'] . '" ' . $selected . '>' . $parent['username'] . '</option>';
				$selected = '';
			}
			$select_parents = '<select name="up_parent_uid">' . $option_parents . '</select>';
		}

		// enhance privacy for subusers
		$enhance_privacy = TRUE;
		$main_config = $core_config['main'];
		if(!auth_isadmin() && $main_config['enhance_privacy_subuser']){
			$enhance_privacy = FALSE;
		}

		// get country option
		$option_country = "<option value=\"0\">--" . _('Please select') . "--</option>\n";
		$result = country_search();
		for($i = 0; $i < count($result); $i++) {
			$country_id = $result[$i]['country_id'];
			$country_name = $result[$i]['country_name'];
			$selected = "";
			if ($country_id == $country) {
				$selected = "selected";
			}
			$option_country .= "<option value=\"$country_id\" $selected>$country_name</option>\n";
		}

		// admin or users
		if ($uname && (auth_isadmin() || $is_parent)) {
			$form_title = _('Manage user');
			if ($is_parent) {
				$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete subuser ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_del" . $url_uname . "')\">";
				$button_back = _back('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list');
			} else {
				$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=main&inc=core_user&route=user_mgmnt&op=user_del" . $url_uname . "&view=" . $view . "')\">";
				$button_back = _back('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view);
			}
		} else {
			$form_title = _('Preferences');
		}

		// error string
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}

		$tpl = array(
			'name' => 'user_pref',
			'vars' => array(
				'User status' => _('User status'),
				'Parent user' => _('Parent user') . " (" . _('for subuser only') . ")",
				'Login information' => _('Login information'),
				'Username' => _('Username'),
				'Password' => _('Password'),
				'Re-type password' => _('Re-type password'),
				'Personal information' => _('Personal information'),
				'Name' => _mandatory(_('Name')),
				'Email' => _mandatory(_('Email')),
				'Mobile' => _('Mobile'),
				'Address' => _('Address'),
				'City' => _('City'),
				'State or Province' => _('State or Province'),
				'Country' => _('Country'),
				'Zipcode' => _('Zipcode'),
				'Save' => _('Save'),
				'HINT_STATUS' => _hint(_('Cannot change status when user have subusers')),
				'HINT_PARENT' => _hint(_('Parent user is mandatory for subusers only. If no value is given then the subuser will be automatically assigned to user admin')),
				'STATUS' => _('User'),
				'ERROR' => $error_content,
				'FORM_TITLE' => $form_title,
				'BUTTON_DELETE' => $button_delete,
				'BUTTON_BACK' => $button_back,
				'URL_UNAME' => $url_uname,
				'VIEW' => $view,
				'select_status' => $select_status,
				'select_parents' => $select_parents,
				'c_username' => $c_username,
				'name' => $name,
				'email' => $email,
				'mobile' => $mobile,
				'address' => $address,
				'city' => $city,
				'state' => $state,
				'option_country' => $option_country,
				'zipcode' => $zipcode
			),
			'ifs' => array(
				'edit_status' => $allow_edit_status,
				'edit_parent' => $allow_edit_parent,
				'edit_status_hint' => $show_status_hint,
				'enhance_privacy' => $enhance_privacy
			)
		);
		_p(tpl_apply($tpl));
		break;
	case "user_pref_save" :
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'name',
			'email',
			'mobile',
			'address',
			'city',
			'state',
			'country',
			'password',
			'zipcode'
		);

		if ($allow_edit_status) {
			_log('saving username:' . $c_username . ' status:' . $_POST['up_status'], 3, 'user_pref');
			$fields[] = 'status';
		}

		if ($allow_edit_parent) {
			_log('saving username:' . $c_username . ' parent_uid:' . $_POST['up_parent_uid'], 3, 'user_pref');
			$fields[] = 'parent_uid';
		}

		for($i = 0; $i < count($fields); $i++) {
			$up[$fields[$i]] = trim($_POST['up_' . $fields[$i]]);
		}

		// subuser's parent uid, by default its uid=1
		if ($_POST['up_parent_uid']) {
			$up['parent_uid'] = ($user_edited['status'] == 4 ? $_POST['up_parent_uid'] : 1);
		} else {
			$up['parent_uid'] = 1;
		}

		$up['username'] = $c_username;
		$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
		if ($up['name']) {
			$v = user_edit_validate($up);
			if ($v['status']) {
				$continue = true;
				if ($up['password'] && $_POST['up_password_conf']) {
					if ($up['password'] == $_POST['up_password_conf']) {
						$up['password'] = md5($up['password']);
					} else {
						$_SESSION['error_string'] = _('Password does not match');
						$continue = false;
					}
				} else {
					unset($up['password']);
				}
				if ($continue) {
					if (dba_update(_DB_PREF_ . '_tblUser', $up, array(
						'username' => $c_username
					))) {
						if ($up['password']) {
							$_SESSION['error_string'] = _('Preferences has been saved and password updated');
						} else if ($up['token']) {
							$_SESSION['error_string'] = _('Preferences has been saved and webservices token updated');
						} else {
							$_SESSION['error_string'] = _('Preferences has been saved');
						}
					} else {
						$_SESSION['error_string'] = _('Fail to save preferences');
					}
				}
			} else {
				$_SESSION['error_string'] = $v['error_string'];
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all field');
		}
		_log('saving username:' . $c_username . ' error_string:' . $_SESSION['error_string'], 2, 'user_pref');
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_pref&op=user_pref' . $url_uname . '&view=' . $view));
		exit();
		break;
}
