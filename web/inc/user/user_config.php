<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$c_username = $core_config['user']['username'];

if (($uname = $_REQUEST['uname']) && isadmin()) {
	$c_username = trim($uname);
	$url_uname = '&uname='.$c_username;
}

switch ($op) {
	case "user_config":
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		if ($c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('username' => $c_username))) {
			$token = $c_user[0]['token'];
			$webservices_ip = $c_user[0]['webservices_ip'];
			$enable_webservices = $c_user[0]['enable_webservices'];
			$mobile = $c_user[0]['mobile'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
			$footer = $c_user[0]['footer'];
			$datetime_timezone = core_get_timezone($c_username);
			$language_module = $c_user[0]['language_module'];
			$fwd_to_inbox = $c_user[0]['fwd_to_inbox'];
			$fwd_to_email = $c_user[0]['fwd_to_email'];
			$fwd_to_mobile = $c_user[0]['fwd_to_mobile'];
			$local_length = $c_user[0]['local_length'];
			$replace_zero = $c_user[0]['replace_zero'];
			$plus_sign_remove = $c_user[0]['plus_sign_remove'];
			$plus_sign_add = $c_user[0]['plus_sign_add'];
			$send_as_unicode = $c_user[0]['send_as_unicode'];
			$credit = rate_getusercredit($c_username);
		} else {
			$_SESSION['error_string'] = _('User does not exists').' ('._('username').': '.$uname.')';
			header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
			exit();
		}

		// select enable_webservices
		if ($enable_webservices) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_enable_webservices = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_enable_webservices .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

		// select token
		if ($new_token) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_new_token = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_new_token .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

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

		// select send_as_unicode
		if ($send_as_unicode) {
			$selected_1 = 'selected';
			$selected_0 = '';
		} else {
			$selected_1 = '';
			$selected_0 = 'selected';
		}
		$option_send_as_unicode = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option>";
		$option_send_as_unicode .= "<option value='0' " . $selected_0 . ">" . _('no') . "</option>";

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
		//SMS sender ID
		if ($core_config['denycustomsender']) {
			$option_sender_id = "<tr><td width=200>" . _('SMS sender ID') . "</td><td>:</td><td><b>".sendsms_get_sender($c_username)."</b></td></tr>";
		} else {
			if($uname && isadmin()){
				$option_sender_id = "<tr><td width=200>" . _('SMS sender ID') . "</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_sender value=\"$sender\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td></tr>";
			}else{
				$option_sender_id = "<tr><td width=200>" . _('SMS sender ID') . "</td><td>:</td><td><b>".sendsms_get_sender($c_username)."</b></td></tr>";
			}
		}
		
		if ($uname && isadmin()) {
			$content .= "<h2>" . _('Manage user') . "</h2>";
			$option_credit = "<tr><td>" . _('Credit') . "</td><td><input type=text size=10 maxlength=10 name=up_credit value=\"$credit\"></td></tr>";
			$button_delete = "<input type=button class=button value='". _('Delete') ."' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=menu&inc=user_mgmnt&op=user_del".$url_uname."')\">";
			$button_back = _b('index.php?app=menu&inc=user_mgmnt&op='.$referrer);
		} else {
			$content .= "<h2>" . _('User configuration') . "</h2>";
			$option_credit = "<tr><td>" . _('Credit') . "</td><td>$credit</td></tr>";
		}
		$content .= "
			<form action=\"index.php?app=menu&inc=user_config&op=user_config_save".$url_uname."\" method=post enctype=\"multipart/form-data\">
			<table width=100%>
			<tbody>
			<tr><td colspan=2><h3>" . _('Application options') . "</h3></td></tr>
			<tr><td width=270>" . _('Username') . "</td><td>".$c_username."</td></tr>
			<tr><td>" . _('Mobile') . " $nd</td><td><input type=text size=16 maxlength=16 name=up_mobile value=\"$mobile\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric characters')) . "</td></tr>
			".$option_sender_id."
			<tr><td>" . _('SMS footer') . "</td><td><input type=text size=30 maxlength=30 name=up_footer value=\"$footer\"> " . _hint(_('Max. 30 alphanumeric characters')) . "</td></tr>
			".$option_credit."
			<tr><td>" . _('Webservices username') . "</td><td>".$c_username."</td></tr>
			<tr><td>" . _('Webservices token') . "</td><td>".$token."</td></tr>
			<tr><td>" . _('New webservices token') . "</td><td><select name='up_new_token'>" . $option_new_token . "</select></td></tr>
			<tr><td>" . _('Enable webservices') . "</td><td><select name='up_enable_webservices'>" . $option_enable_webservices . "</select></td></tr>
			<tr><td>" . _('Webservices IP range') . "</td><td><input type=text size=30 maxlength=100 name=up_webservices_ip value=\"$webservices_ip\"> "._hint(_('Comma seperated'))."</td></tr>
			<tr><td>Active language</td><td><select name=up_language_module>$option_language_module</select></td></tr>
			<tr><td>" . _('Timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_datetime_timezone value=\"$datetime_timezone\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td></tr>
			<tr><td>" . _('Forward SMS to inbox') . "</td><td><select name='up_fwd_to_inbox'>" . $option_fwd_to_inbox . "</select></td></tr>
			<tr><td>" . _('Forward SMS to email') . "</td><td><select name='up_fwd_to_email'>" . $option_fwd_to_email . "</select></td></tr>
			<tr><td>" . _('Forward SMS to mobile') . "</td><td><select name='up_fwd_to_mobile'>" . $option_fwd_to_mobile . "</select></td></tr>
			<tr><td>" . _('Local number length') . "</td><td><input type=text size=5 maxlength=5 name='up_local_length' value=\"$local_length\"> " . _hint(_('Min length to detect missing country code')) . "</td></tr>
			<tr><td>" . _('Prefix or country code') . "</td><td><input type=text size=5 maxlength=5 name='up_replace_zero' value=\"$replace_zero\"> " . _hint(_('Replace prefix 0 or padding local numbers')) . "</td></tr>
			<tr><td>" . _('Auto remove plus sign') . "</td><td><select name='up_plus_sign_remove'>" . $option_plus_sign_remove . "</select></td></tr>
			<tr><td>" . _('Always add plus sign') . "</td><td><select name='up_plus_sign_add'>" . $option_plus_sign_add . "</select></td></tr>
			<tr><td>" . _('Always choose to send as unicode') . "</td><td><select name='up_send_as_unicode'>" . $option_send_as_unicode . "</select></td></tr>
			</tbody>
			</table>
			<input type=submit class=button value='" . _('Save') . "'> ".$button_delete."
			</form>
			".$button_back;
		echo $content;
		break;
	case "user_config_save":
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'mobile', 'footer', 'datetime_timezone', 'language_module',
			'fwd_to_inbox', 'fwd_to_email', 'fwd_to_mobile', 'local_length',
			'replace_zero', 'plus_sign_remove', 'plus_sign_add', 'send_as_unicode',
			'new_token', 'enable_webservices', 'webservices_ip'
		);
		if (!$core_config['denycustomsender']) {
			if ($uname && isadmin()) {
				$fields[] = 'sender';
			}
		}
		if ($uname && isadmin()) {
			$fields[] = 'credit';
		}
		for ($i=0;$i<count($fields);$i++) {
			$up[$fields[$i]] = trim($_POST['up_'.$fields[$i]]);
		}
		$up['username'] = $c_username;
		$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
		if ($up['mobile']) {
			$v = user_add_validate($up);
			if ($v['status']) {
				$continue = true;
				if ($up['new_token']) {
					$up['token'] = md5(mktime().$c_username.$up['email']);
				}
				unset($up['new_token']);
				if ($continue) {
					if (dba_update(_DB_PREF_.'_tblUser', $up, array('username' => $c_username))) {
						if ($up['password']) {
							$_SESSION['error_string'] = _('User configuration has been saved and password updated');
						} else if ($up['token']) {
							$_SESSION['error_string'] = _('User configuration has been saved and webservices token updated');
						} else {
							$_SESSION['error_string'] = _('User configuration has been saved');
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
		header("Location: index.php?app=menu&inc=user_config&op=user_config".$url_uname);
		exit();
		break;
}
?>