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

$c_username = $user_config['username'];

if (($uname = $_REQUEST['uname']) && auth_isadmin()) {
	$c_username = trim($uname);
	$url_uname = '&uname='.$c_username;
}

switch (_OP_) {
	case "user_config":
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		if ($c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('username' => $c_username))) {
			$token = $c_user[0]['token'];
			$webservices_ip = $c_user[0]['webservices_ip'];
			$enable_webservices = $c_user[0]['enable_webservices'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
			$footer = $c_user[0]['footer'];
			$datetime_timezone = core_get_timezone($c_username);
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
			header("Location: "._u('index.php?app=main&inc=user_mgmnt&op='.$referrer));
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
			$c_language_title = $plugin_config[$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		$option_language_module .= "<option value=\"\">"._('Default')."</option>";
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == core_lang_get()) $selected = "selected";
				$option_language_module .= "<option value=\"".$val."\" $selected>".$key."</option>";
				$selected = "";
			}
		}

		if ($uname && auth_isadmin()) {
			$content .= "<h2>" . _('Manage user') . "</h2>";
			$option_credit = "<tr><td>" . _('Credit') . "</td><td><input type=text size=10 maxlength=10 name=up_credit value=\"$credit\"></td></tr>";
			$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=main&inc=user_mgmnt&op=user_del" . $url_uname . "')\">";
			$button_back = _back('index.php?app=main&inc=user_mgmnt&op=' . $referrer);
		} else {
			$content .= "<h2>" . _('User configuration') . "</h2>";
			$option_credit = "<tr><td>" . _('Credit') . "</td><td>$credit</td></tr>";
		}
		$tpl = array(
		    'name' => 'user_config',
		    'var' => array(
			'Application options' => _('Application options'),
			'Username' => _('Username'),
			'Effective SMS sender ID' => _('Effective SMS sender ID'),
			'SMS sender ID' => _('SMS sender ID'),
			'SMS footer' => _('SMS footer'),
			'Webservices username' => _('Webservices username'),
			'Webservices token' => _('Webservices token'),
			'New webservices token' => _('New webservices token'),
			'Enable webservices' => _('Enable webservices'),
			'Webservices IP range' => _('Webservices IP range'),
			'Active language' => _('Active language'),
			'Timezone' => _('Timezone'),
			'Forward message to inbox' => _('Forward message to inbox'),
			'Forward message to email' => _('Forward message to email'),
			'Forward message to mobile' => _('Forward message to mobile'),
			'Local number length' => _('Local number length'),
			'Prefix or country code' => _('Prefix or country code'),
			'Auto remove plus sign' => _('Auto remove plus sign'),
			'Always add plus sign' => _('Always add plus sign'),
			'Always choose to send as unicode' => _('Always choose to send as unicode'),
			'Save' => _('Save'),
			'ERROR' => $error_content,
			'FORM_TITLE' => $form_title,
			'BUTTON_DELETE' => $button_delete,
			'BUTTON_BACK' => $button_back,
			'URL_UNAME' => $url_uname,
			'HINT_MAX_CHARS' => _hint(_('Max. 16 numeric or 11 alphanumeric characters')),
			'HINT_MAX_ALPHANUMERIC' => _hint(_('Max. 30 alphanumeric characters')),
			'HINT_COMMA_SEPARATED' => _hint(_('Comma separated')),
			'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
			'HINT_LOCAL_LENGTH' => _hint(_('Min length to detect missing country code')),
			'HINT_REPLACE_ZERO' => _hint(_('Replace prefix 0 or padding local numbers')),
			'option_credit' => $option_credit,
			'option_new_token' => $option_new_token,
			'option_enable_webservices' => $option_enable_webservices,
			'option_language_module' => $option_language_module,
			'option_fwd_to_inbox' => $option_fwd_to_inbox,
			'option_fwd_to_email' => $option_fwd_to_email,
			'option_fwd_to_mobile' => $option_fwd_to_mobile,
			'option_plus_sign_remove' => $option_plus_sign_remove,
			'option_plus_sign_add' => $option_plus_sign_add,
			'option_send_as_unicode' => $option_send_as_unicode,
			'c_username' => $c_username,
			'effective_sender_id' => sendsms_get_sender($c_username),
			'sender' => $sender,
			'footer' => $footer,
			'token' => $token,
			'webservices_ip' => $webservices_ip,
			'datetime_timezone' => $datetime_timezone,
			'local_length' => $local_length,
			'replace_zero' => $replace_zero,
		    )
		);
		_p(tpl_apply($tpl));
		break;
	case "user_config_save":
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'footer', 'datetime_timezone', 'language_module',
			'fwd_to_inbox', 'fwd_to_email', 'fwd_to_mobile', 'local_length',
			'replace_zero', 'plus_sign_remove', 'plus_sign_add', 'send_as_unicode',
			'new_token', 'enable_webservices', 'webservices_ip', 'sender'
		);
		if ($uname && auth_isadmin()) {
			$fields[] = 'credit';
		}
		for ($i=0;$i<count($fields);$i++) {
			$up[$fields[$i]] = trim($_POST['up_'.$fields[$i]]);
		}
		$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
		if ($up['username'] = $c_username) {
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
			$_SESSION['error_string'] = _('Username is empty');
		}
		header("Location: "._u('index.php?app=main&inc=user_config&op=user_config'.$url_uname));
		exit();
		break;
}
