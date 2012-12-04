<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "user_pref":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		if ($c_user = user_getall(array('uid' => $core_config['user']['uid']))) {
			$address = $c_user[0]['address'];
			$city = $c_user[0]['city'];
			$state = $c_user[0]['state'];
			$country = $c_user[0]['country'];
			$zipcode = $c_user[0]['zipcode'];
			$sender = $c_user[0]['sender'];
			$footer = $c_user[0]['footer'];
			$datetime_timezone = $c_user[0]['datetime_timezone'];
			$language_module = $c_user[0]['language_module'];
			$fwd_to_inbox = $c_user[0]['fwd_to_inbox'];
			$fwd_to_email = $c_user[0]['fwd_to_email'];
			$fwd_to_mobile = $c_user[0]['fwd_to_mobile'];
			$replace_zero = $c_user[0]['replace_zero'];
			$plus_sign_remove = $c_user[0]['plus_sign_remove'];
			$plus_sign_add = $c_user[0]['plus_sign_add'];
			$credit = rate_getusercredit($c_username);
		}
		// select fwd_to_inbox
		if ($fwd_to_inbox) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_fwd_to_inbox = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_fwd_to_inbox .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// select fwd_to_email
		if ($fwd_to_email) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_fwd_to_email = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_fwd_to_email .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// select fwd_to_mobile
		if ($fwd_to_mobile) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_fwd_to_mobile = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_fwd_to_mobile .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// select plus_sign_remove
		if ($plus_sign_remove) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_plus_sign_remove = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_plus_sign_remove .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// select plus_sign_add
		if ($plus_sign_add) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_plus_sign_add = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_plus_sign_add .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// get country option
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser_country ORDER BY country_name";
		$db_result = dba_query($db_query);
		$option_country = "<option value=\"0\">--" . _('Please select') . "--</option>\n";
		while ($db_row = dba_fetch_array($db_result)) {
			$country_id = $db_row['country_id'];
			$country_name = $db_row['country_name'];
			$selected = "";
			if ($country_id == $country) {
				$selected = "selected";
			}
			$option_country .= "<option value=\"$country_id\" $selected>$country_name</option>\n";
		}

		// get language options
		$lang_list = '';
		for ($i=0;$i<count($core_config['languagelist']);$i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $core_config['plugins']['language'][$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		$option_language_module .= "<option value=\"\">"._('Default')."</option>";
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == $language_module) $selected = "selected";
				$option_language_module .= "<option value=\"".$val."\" $selected>".$key."</option>";
				$selected = "";
			}
		}

		$content .= "
			<h2>" . _('Preferences') . "</h2>
			<p>
			<form action=index.php?app=menu&inc=user_pref&op=user_pref_save method=post enctype=\"multipart/form-data\">
			<table width=100% cellpadding=1 cellspacing=1 border=0>
			<tr><td colspan=3><h2>" . _('Login information') . "</h2><hr></td></tr>
			<tr><td width=200>" . _('Username') . "</td><td>:</td><td><b>".$core_config['user']['username']."</b></td></tr>
			<tr><td width=200>" . _('Password') . "</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password></td></tr>
			<tr><td width=200>" . _('Re-Type Password') . "</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password_conf></td></tr>
			<tr><td colspan=3>&nbsp;</td></tr>
			<tr><td colspan=3><h2>" . _('Personal information') . "</h2><hr></td></tr>
			<tr><td width=200>" . _('Name') . " $nd</td><td>:</td><td><input type=text size=30 maxlength=100 name=up_name value=\"$name\"></td></tr>
			<tr><td width=200>" . _('Email') . " $nd</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_email value=\"$email\"></td></tr>
			<tr><td width=200>" . _('Mobile') . "</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_mobile value=\"$mobile\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td></tr>
			<tr><td width=200>" . _('Address') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_address value=\"$address\"></td></tr>
			<tr><td width=200>" . _('City') . "</td><td>:</td><td><input type=text size=30 maxlength=100 name=up_city value=\"$city\"></td></tr>
			<tr><td width=200>" . _('State or Province') . "</td><td>:</td><td><input type=text size=30 maxlength=100 name=up_state value=\"$state\"></td></tr>
			<tr><td width=200>" . _('Country') . "</td><td>:</td><td><select name=up_country>$option_country</select></td></tr>
			<tr><td width=200>" . _('Zipcode') . "</td><td>:</td><td><input type=text size=10 maxlength=10 name=up_zipcode value=\"$zipcode\"></td></tr>
			<tr><td width=200>Active language</td><td>:</td><td><select name=up_language_module>$option_language_module</select></td></tr>
			<tr><td colspan=3>&nbsp;</td></tr>
			<tr><td colspan=3><h2>" . _('Application options') . "</h2><hr></td></tr>
			<tr><td width=200>" . _('Timezone') . "</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_datetime_timezone value=\"$datetime_timezone\"> (" . _('Eg: +0700 for Jakarta/Bangkok timezone') . ")</td></tr>
			<tr><td width=200>" . _('SMS sender ID') . "</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_sender value=\"$sender\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td></tr>
			<tr><td width=200>" . _('SMS footer') . "</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_footer value=\"$footer\"> (" . _('Max. 30 alphanumeric characters') . ")</td></tr>
			<tr><td width=200>" . _('Credit') . "</td><td>:</td><td><b>$credit</b></td></tr>
			<tr><td width=200>" . _('Forward SMS to inbox') . "</td><td>:</td><td><select name='up_fwd_to_inbox'>" . $option_fwd_to_inbox . "</select></td></tr>
			<tr><td width=200>" . _('Forward SMS to email') . "</td><td>:</td><td><select name='up_fwd_to_email'>" . $option_fwd_to_email . "</select></td></tr>
			<tr><td width=200>" . _('Forward SMS to mobile') . "</td><td>:</td><td><select name='up_fwd_to_mobile'>" . $option_fwd_to_mobile . "</select></td></tr>
			<tr><td width=200>" . _('Auto replace prefix 0') . "</td><td>:</td><td><input type=text size=5 maxlength=5 name='up_replace_zero' value=\"$replace_zero\"> (" . _('Prefix or country code') . ")</td></tr>
			<tr><td width=200>" . _('Auto remove plus sign') . "</td><td>:</td><td><select name='up_plus_sign_remove'>" . $option_plus_sign_remove . "</select></td></tr>
			<tr><td width=200>" . _('Always add plus sign') . "</td><td>:</td><td><select name='up_plus_sign_add'>" . $option_plus_sign_add . "</select></td></tr>
			<tr><td colspan=3>&nbsp;</td></tr>
			<tr><td colspan=3><hr></td></tr>
			<tr><td width=200><input type=submit class=button value='" . _('Save') . "'></td></tr>
			</table>
			</form>";
		echo $content;
		break;
	case "user_pref_save":
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'name', 'email', 'address', 'city', 'state', 'country', 'mobile',
			'sender', 'footer', 'password', 'zipcode', 'datetime_timezone', 
			'language_module', 'fwd_to_inbox', 'fwd_to_email', 'fwd_to_mobile',
			'replace_zero', 'plus_sign_remove', 'plus_sign_add'
		);
		for ($i=0;$i<count($fields);$i++) {
			$up[$fields[$i]] = trim($_POST['up_'.$fields[$i]]);
		}
		$up['username'] = $core_config['user']['username'];
		$up['lastupdate_datetime'] = core_adjust_datetime($core_config['datetime']['now']);
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
					if (user_update($up, array('username' => $up['username']))) {
						if ($up['password']) {
							$_SESSION['error_string'] = _('Preferences has been saved and password updated');
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
		header("Location: index.php?app=menu&inc=user_pref&op=user_pref");
		exit();
		break;
}
?>