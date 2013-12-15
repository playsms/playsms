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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$c_username = $core_config['user']['username'];

if (($uname = $_REQUEST['uname']) && auth_isadmin()) {
	$c_username = trim($uname);
	$url_uname = '&uname='.$c_username;
}

switch ($op) {
	case "user_pref":
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		if ($c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('username' => $c_username))) {
			$name = $c_user[0]['name'];
			$email = $c_user[0]['email'];
			$address = $c_user[0]['address'];
			$city = $c_user[0]['city'];
			$state = $c_user[0]['state'];
			$country = $c_user[0]['country'];
			$zipcode = $c_user[0]['zipcode'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
		} else {
			$_SESSION['error_string'] = _('User does not exists').' ('._('username').': '.$uname.')';
			header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
			exit();
		}

		// get country option
		$option_country = "<option value=\"0\">--" . _('Please select') . "--</option>\n";
		$result = dba_search(_DB_PREF_.'_tblUser_country', '*', '', '', array('ORDER BY' => 'country_name'));
		for ($i=0;$i<count($result);$i++) {
			$country_id = $result[$i]['country_id'];
			$country_name = $result[$i]['country_name'];
			$selected = "";
			if ($country_id == $country) {
				$selected = "selected";
			}
			$option_country .= "<option value=\"$country_id\" $selected>$country_name</option>\n";
		}
		if ($uname && auth_isadmin()) {
			$form_title = _('Manage user');
			$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=menu&inc=user_mgmnt&op=user_del" . $url_uname . "')\">";
			$button_back = _back('index.php?app=menu&inc=user_mgmnt&op=' . $referrer);
		} else {
			$form_title = _('Preferences');
		}
		unset($tpl);
		$tpl = array(
		    'name' => 'user_pref',
		    'var' => array(
			'Login information' => _('Login information'),
			'Username' => _('Username'),
			'Password' => _('Password'),
			'Re-type password' => _('Re-type password'),
			'Personal information' => _('Personal information'),
			'Name' => _mandatory('Name'),
			'Email' => _mandatory('Email'),
			'Address' => _('Address'),
			'City' => _('City'),
			'State or Province' => _('State or Province'),
			'Country' => _('Country'),
			'Zipcode' => _('Zipcode'),
			'Save' => _('Save'),
			'ERROR' => $error_content,
			'FORM_TITLE' => $form_title,
			'BUTTON_DELETE' => $button_delete,
			'BUTTON_BACK' => $button_back,
			'URL_UNAME' => $url_uname,
			'c_username' => $c_username,
			'name' => $name,
			'email' => $email,
			'address' => $address,
			'city' => $city,
			'state' => $state,
			'option_country' => $option_country,
			'zipcode' => $zipcode
		    )
		);
		echo tpl_apply($tpl);
		break;
	case "user_pref_save":
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'name', 'email', 'address', 'city', 'state', 'country', 'password', 'zipcode'
		);
		for ($i=0;$i<count($fields);$i++) {
			$up[$fields[$i]] = trim($_POST['up_'.$fields[$i]]);
		}
		$up['username'] = $c_username;
		$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
		if ($up['name'] && $up['email']) {
			$v = user_add_validate($up);
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
					if (dba_update(_DB_PREF_.'_tblUser', $up, array('username' => $c_username))) {
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
		header("Location: index.php?app=menu&inc=user_pref&op=user_pref".$url_uname);
		exit();
		break;
}
