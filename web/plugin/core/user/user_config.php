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

$c_uid = user_username2uid($c_username);

switch (_OP_) {
	case "user_config":
		if ($c_user = dba_search(_DB_PREF_ . '_tblUser', '*', array(
			'flag_deleted' => 0,
			'uid' => $c_uid 
		))) {
			$token = $c_user[0]['token'];
			$webservices_ip = $c_user[0]['webservices_ip'];
			$enable_webservices = $c_user[0]['enable_webservices'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
			$footer = core_sanitize_footer($c_user[0]['footer']);
			$datetime_timezone = core_get_timezone($c_username);
			$fwd_to_inbox = $c_user[0]['fwd_to_inbox'];
			$fwd_to_email = $c_user[0]['fwd_to_email'];
			$fwd_to_mobile = $c_user[0]['fwd_to_mobile'];
			$local_length = $c_user[0]['local_length'];
			$replace_zero = $c_user[0]['replace_zero'];
			$acl_id = (int) $c_user[0]['acl_id'];
			$credit = rate_getusercredit($c_username);
		} else {
			$_SESSION['dialog']['info'][] = _('User does not exist') . ' (' . _('username') . ': ' . $uname . ')';
			header("Location: " . _u('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view));
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
		
		// get language options
		$lang_list = '';
		for ($i = 0; $i < count($core_config['languagelist']); $i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $plugin_config[$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		$option_language_module .= "<option value=\"\">" . _('Default') . "</option>";
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == $user_config['language_module']) $selected = "selected";
				$option_language_module .= "<option value=\"" . $val . "\" $selected>" . $key . "</option>";
				$selected = "";
			}
		}
		
		// get sender ID
		$c_sms_from = sender_id_default_get($user_edited['uid']);
		$option_sender_id = "<option value=\"\">" . _('None') . "</option>";
		foreach (sender_id_getall($user_edited['username']) as $sender_id) {
			$selected = '';
			if (strtoupper($c_sms_from) == strtoupper($sender_id)) {
				$selected = 'selected';
			}
			$option_sender_id .= "<option value=\"" . $sender_id . "\" title=\"" . $sender_id . "\" " . $selected . ">" . $sender_id . "</option>";
		}
		
		// admin or users
		if ($uname && (auth_isadmin() || $is_parent)) {
			$form_title = _('Manage account');
			
			// fixme anton - now disabled since plugin/feature/credit exists
			// $option_credit = "<tr><td>" . _('Credit') . "</td><td><input type=text maxlength=14 name=up_credit value=\"$credit\"></td></tr>";
			

			if ($is_parent) {
				$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete subuser ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_del" . $url_uname . "')\">";
				$button_back = _back('index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list');
			} else {
				$button_delete = "<input type=button class=button value='" . _('Delete') . "' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=main&inc=core_user&route=user_mgmnt&op=user_del" . $url_uname . "&view=" . $view . "')\">";
				$button_back = _back('index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list&view=' . $view);
			}
		} else {
			$form_title = _('User configuration');
			
			// fixme anton - now disabled since plugin/feature/credit exists
			// $option_credit = "<tr><td>" . _('Credit') . "</td><td>$credit</td></tr>";
		}
		
		// get access control list
		$c_option_acl = array_flip(acl_getall());
		$option_acl = _input('text', '', acl_getname($acl_id), array(
			'readonly' 
		));
		if (auth_isadmin()) {
			$option_acl = _select('up_acl_id', $c_option_acl, $acl_id);
		}
		if ($user_edited['status'] == 4) {
			$parent_id = user_getparentbyuid($user_edited['uid']);
			if ($parent_id == $user_config['uid']) {
				$c_option_acl = array_flip(acl_getallbyuid($user_config['uid']));
				$option_acl = _select('up_acl_id', $c_option_acl, $acl_id);
			}
		}
		
		// additional user's config available on registry
		$data = registry_search($c_uid, 'core', 'user_config');
		
		// credit unicodes messages as single message
		$option_enable_credit_unicode = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $data['core']['user_config']['enable_credit_unicode']);
		if (auth_isadmin()) {
			$option_enable_credit_unicode = "<select name='edit_enable_credit_unicode'>" . $option_enable_credit_unicode . "</select>";
		} else {
			$option_enable_credit_unicode = $user_config['opt']['enable_credit_unicode'] ? _('yes') : _('no');
		}
		
		// error string
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		
		$tpl = array(
			'name' => 'user_config',
			'vars' => array(
				'Application options' => _('Application options'),
				'Username' => _('Username'),
				'Access Control List' => _('Access Control List'),
				'Effective SMS sender ID' => _('Effective SMS sender ID'),
				'Default sender ID' => _('Default sender ID'),
				'Default message footer' => _('Default message footer'),
				'Webservices username' => _('Webservices username'),
				'Webservices token' => _('Webservices token'),
				'Renew webservices token' => _('Renew webservices token'),
				'Enable webservices' => _('Enable webservices'),
				'Webservices IP range' => _('Webservices IP range'),
				'Active language' => _('Active language'),
				'Timezone' => _('Timezone'),
				'Credit' => _('Credit'),
				'Enable credit unicode SMS as normal SMS' => _('Enable credit unicode SMS as normal SMS'),
				'Forward message to inbox' => _('Forward message to inbox'),
				'Forward message to email' => _('Forward message to email'),
				'Forward message to mobile' => _('Forward message to mobile'),
				'Local number length' => _('Local number length'),
				'Prefix or country code' => _('Prefix or country code'),
				'Always choose to send as unicode' => _('Always choose to send as unicode'),
				'Save' => _('Save'),
				'DIALOG_DISPLAY' => $error_content,
				'FORM_TITLE' => $form_title,
				'BUTTON_DELETE' => $button_delete,
				'BUTTON_BACK' => $button_back,
				'URL_UNAME' => $url_uname,
				'VIEW' => $view,
				'HINT_MAX_CHARS' => _hint(_('Max. 16 numeric or 11 alphanumeric characters')),
				'HINT_MAX_ALPHANUMERIC' => _hint(_('Max. 30 alphanumeric characters')),
				'HINT_COMMA_SEPARATED' => _hint(_('Comma separated')),
				'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
				'HINT_LOCAL_LENGTH' => _hint(_('Min length to detect missing country code')),
				'HINT_REPLACE_ZERO' => _hint(_('Replace prefix 0 or padding local numbers')),
				'HINT_MANAGE_CREDIT' => _hint(_('Add or reduce credit from manage credit menu')),
				'HINT_ACL' => _hint(_('ACL DEFAULT will not restrict access to menus')),
				'option_new_token' => $option_new_token,
				'option_enable_webservices' => $option_enable_webservices,
				'option_language_module' => $option_language_module,
				'option_fwd_to_inbox' => $option_fwd_to_inbox,
				'option_fwd_to_email' => $option_fwd_to_email,
				'option_fwd_to_mobile' => $option_fwd_to_mobile,
				'option_acl' => $option_acl,
				'option_sender_id' => $option_sender_id,
				'c_username' => $c_username,
				'effective_sender_id' => sendsms_get_sender($c_username),
				'sender' => $sender,
				'footer' => $footer,
				'token' => $token,
				'webservices_ip' => $webservices_ip,
				'datetime_timezone' => $datetime_timezone,
				'local_length' => $local_length,
				'replace_zero' => $replace_zero,
				'credit' => $credit,
				'option_enable_credit_unicode' => $option_enable_credit_unicode 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "user_config_save":
		$fields = array(
			'footer',
			'datetime_timezone',
			'language_module',
			'fwd_to_inbox',
			'fwd_to_email',
			'fwd_to_mobile',
			'local_length',
			'replace_zero',
			'new_token',
			'enable_webservices',
			'webservices_ip',
			'sender',
			'acl_id' 
		);
		
		$up = array();
		foreach ($fields as $field) {
			if (strlen($_POST['up_' . $field])) {
				$up[$field] = trim($_POST['up_' . $field]);
			}
		}
		$ret = user_edit_conf($c_uid, $up);
		
		$items['enable_credit_unicode'] = (int) $_POST['edit_enable_credit_unicode'];
		registry_update($c_uid, 'core', 'user_config', $items);
		
		$_SESSION['dialog']['info'][] = $ret['error_string'];
		
		_log('saving username:' . $c_username . ' error_string:[' . $ret['error_string'] . ']', 2, 'user_config');
		header("Location: " . _u('index.php?app=main&inc=core_user&route=user_config&op=user_config' . $url_uname . '&view=' . $view));
		exit();
		break;
}
