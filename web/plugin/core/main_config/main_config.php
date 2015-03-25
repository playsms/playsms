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

switch (_OP_) {
	case "main_config":
		
		// get original main_config
		$data = registry_search(1, 'core', 'main_config');
		$main_config = $data['core']['main_config'];
		
		// enable register yes-no option
		$option_enable_register = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['enable_register']);
		
		// enable forgot yes-no option
		$option_enable_forgot = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['enable_forgot']);
		
		// disable login as subuser yes-no option
		$option_disable_login_as = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['disable_login_as']);
		
		// enhance privacy for subusers
		$option_enhance_privacy_subuser = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['enhance_privacy_subuser']);
		
		// enable logo yes-no option
		$option_enable_logo = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['enable_logo']);
		
		// enable logo to replace main website title yes-no option
		$option_logo_replace_title = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['logo_replace_title']);
		
		// option default account status on user registration
		$option_default_user_status = _options(array(
			_('User') => 3,
			_('Subuser') => 4 
		), $main_config['default_user_status']);
		
		// option default parent upon registration
		$option_default_parent = themes_select_account_level_single(3, 'edit_default_parent', $main_config['default_parent']);
		
		// get access control list
		$c_option_default_acl = array_flip(acl_getall());
		$option_default_acl = _select('edit_default_acl', $c_option_default_acl, $main_config['default_acl']);
		
		// get gateway options
		$main_gateway = $main_config['gateway_module'];
		unset($smsc_list);
		$list = gateway_getall_smsc();
		foreach ($list as $smsc) {
			$smsc_list[] = $smsc['name'];
		}
		$option_gateway_module = _options($smsc_list, $main_gateway);
		
		// get themes options
		$main_themes = $main_config['themes_module'];
		$option_themes_module = _options($core_config['themeslist'], $main_themes);
		
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
			$option_language_module = _options($lang_list, $main_config['language_module']);
		}
		
		// select plus_sign_remove
		$option_plus_sign_remove = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['plus_sign_remove']);
		
		// select plus_sign_add
		$option_plus_sign_add = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['plus_sign_add']);
		
		// select enable_credit_unicode
		$option_enable_credit_unicode = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['enable_credit_unicode']);
		
		// select brute_force_detection
		$option_brute_force_detection = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $main_config['brute_force_detection']);
		
		// display
		

		$tpl = array(
			'name' => 'main_config',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'ACTION_URL' => _u('index.php?app=main&inc=core_main_config&op=main_config_save'),
				'Main configuration' => _('Main configuration'),
				'Default settings' => _('Default settings'),
				'Default site configuration' => _('Default site configuration'),
				'Information page' => _('Information page'),
				'Buy credit page' => _('Buy credit page'),
				'Page title' => _('Page title'),
				'Page content' => _('Page content'),
				'Website URL' => _('Website URL'),
				'Website title' => _('Website title'),
				'Website email' => _('Website email'),
				'Forwarded email footer' => _('Forwarded email footer'),
				'Main website name' => _('Main website name'),
				'Main website URL' => _('Main website URL'),
				'Default sender ID' => _('Default sender ID'),
				'Default timezone' => _('Default timezone'),
				'Maximum username length' => _('Maximum username length'),
				'Default SMS rate' => _('Default SMS rate'),
				'Maximum SMS count' => _('Maximum SMS count'),
				'Always remove plus sign' => _('Always remove plus sign'),
				'Always add plus sign' => _('Always add plus sign'),
				'Enable credit unicode SMS as normal SMS' => _('Enable credit unicode SMS as normal SMS'),
				'Enable login brute force detection' => _('Enable login brute force detection'),
				'Keyword separator' => _('Keyword separator'),
				'Lowest credit limit to trigger notification' => _('Lowest credit limit to trigger notification'),
				'Number of sent SMS per hour limit' => _('Number of sent SMS per hour limit'),
				'Enable public registration' => _('Enable public registration'),
				'Enable forgot password' => _('Enable forgot password'),
				'Disable login as subuser' => _('Disable login as subuser'),
				'Enhance privacy for subusers' => _('Enhance privacy for subusers'),
				'Enable logo' => _('Enable logo'),
				'Logo URL' => _('Logo URL'),
				'Replace website title with logo' => _('Replace website title with logo'),
				'Default SMSC' => _('Default SMSC'),
				'Default prefix or country code' => _('Default prefix or country code'),
				'Active themes' => _('Active themes'),
				'Default language' => _('Default language'),
				'Default account status upon registration' => _('Default account status upon registration'),
				'Default parent upon registration' => _('Default parent upon registration'),
				'Default ACL upon registration' => _('Default ACL upon registration'),
				'Default credit upon registration' => _('Default credit upon registration'),
				'Layout footer' => _('Layout footer'),
				'Save' => _('Save'),
				'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
				'lang' => substr($user_config['language_module'], 0, 2),
				'HINT_SENDER_ID' => _hint(_('Empty default sender ID to allow users setting their own sender ID')),
				'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
				'HINT_ENABLE_LOGO' => _hint(_('Logo by default will be displayed at login, register and forgot password page')),
				'HINT_WEBSITE_URL' => _hint(_('Access to unknown domain mapped to this server IP address will be redirected to this website URL. This website URL should be the primary address for the service.')),
				'HINT_CUSTOM_SENDER_ID' => _hint(_('Allow users to select sender ID while on Send SMS page')),
				'HINT_CUSTOM_FOOTER' => _hint(_('Allow users to select SMS footer while on Send SMS page')),
				'HINT_SMS_LIMIT_PER_HOUR' => _hint(_('Fill with zero to disable limit')),
				'HINT_DEFAULT_PARENT' => _hint(_('Default parent selected upon registration when the default account status on registration setting set to Subuser')),
				'HINT_DEFAULT_ACL' => _hint(_('ACL DEFAULT will not restrict access to menus')),
				'HINT_USERNAME_LENGTH' => _hint(_('Maximum username length must be a number between 4 to 100')),
				'HINT_REPLACE_ZERO' => _hint(_('Default prefix or country code to replace prefix 0 on destination number')),
				'HINT_CREDIT_LOWEST_LIMIT' => _hint(_('Set credit value bigger than 0 to set credit lowest limit that will trigger notification')),
				'HINT_KEYWORD_SEPARATOR' => _hint(_('Define a single character as keyword separator replacing default keyword separator a space')),
				'web_title' => $main_config['web_title'],
				'email_service' => $main_config['email_service'],
				'email_footer' => $main_config['email_footer'],
				'main_website_name' => $main_config['main_website_name'],
				'main_website_url' => $main_config['main_website_url'],
				'gateway_number' => $main_config['gateway_number'],
				'gateway_timezone' => $main_config['gateway_timezone'],
				'username_length' => ((($main_config['username_length'] >= 3) && (($main_config['username_length'] <= 100))) ? $main_config['username_length'] : 30),
				'default_rate' => $main_config['default_rate'],
				'sms_max_count' => $main_config['sms_max_count'],
				'credit_lowest_limit' => (float) $main_config['credit_lowest_limit'],
				'sms_limit_per_hour' => (int) $main_config['sms_limit_per_hour'],
				'default_replace_zero' => $main_config['default_replace_zero'],
				'default_credit' => (float) $main_config['default_credit'],
				'keyword_separator' => substr($main_config['keyword_separator'], 0, 1),
				'logo_url' => $main_config['logo_url'],
				'layout_footer' => $main_config['layout_footer'],
				'information_title' => $main_config['information_title'],
				'information_content' => $main_config['information_content'],
				'option_default_user_status' => $option_default_user_status,
				'option_default_parent' => $option_default_parent,
				'option_default_acl' => $option_default_acl,
				'option_enable_logo' => $option_enable_logo,
				'option_logo_replace_title' => $option_logo_replace_title,
				'option_enable_register' => $option_enable_register,
				'option_enable_forgot' => $option_enable_forgot,
				'option_disable_login_as' => $option_disable_login_as,
				'option_enhance_privacy_subuser' => $option_enhance_privacy_subuser,
				'option_gateway_module' => $option_gateway_module,
				'option_themes_module' => $option_themes_module,
				'option_language_module' => $option_language_module,
				'option_plus_sign_remove' => $option_plus_sign_remove,
				'option_plus_sign_add' => $option_plus_sign_add,
				'option_enable_credit_unicode' => $option_enable_credit_unicode,
				'option_brute_force_detection' => $option_brute_force_detection 
			),
			'injects' => array(
				'core_config' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "main_config_save":
		
		// logo
		

		$enable_logo = $_POST['edit_enable_logo'];
		$logo_url = trim($_POST['edit_logo_url']);
		$logo_replace_title = $_POST['edit_logo_replace_title'];
		
		if (!$logo_url) {
			$themes_logo = _APPS_PATH_THEMES_ . '/' . core_themes_get() . '/images/logo.png';
			$themes_logo_url = _HTTP_PATH_THEMES_ . '/' . core_themes_get() . '/images/logo.png';
			
			$default_logo = _APPS_PATH_THEMES_ . '/common/images/logo.png';
			$default_logo_url = _HTTP_PATH_THEMES_ . '/common/images/logo.png';
			
			$logo_url = (file_exists($themes_logo) ? $themes_logo_url : $default_logo_url);
			
			// force to disable logo when neither themes_logo or default_logo exists
			if (!file_exists($default_logo)) {
				$logo_url = '';
				$enable_logo = 0;
			}
		}
		
		// disable logo_replace_title when logo disabled
		if (!$enable_logo) {
			$logo_replace_title = 0;
		}
		
		// allow default account status 3 and 4 only
		$edit_default_user_status = (int) $_POST['edit_default_user_status'];
		if (!(($edit_default_user_status == 3) || ($edit_default_user_status == 4))) {
			$edit_default_user_status == 4;
		}
		
		// save
		

		foreach ($_POST as $key => $val) {
			if (substr($key, 0, 5) == 'edit_') {
				$post[$key] = str_replace('"', '\'', $val);
			}
		}
		
		$items = array(
			'web_title' => $post['edit_web_title'],
			'email_service' => $post['edit_email_service'],
			'email_footer' => $post['edit_email_footer'],
			'main_website_name' => $post['edit_main_website_name'],
			'main_website_url' => $post['edit_main_website_url'],
			'gateway_number' => core_sanitize_sender($post['edit_gateway_number']),
			'gateway_timezone' => $post['edit_gateway_timezone'],
			'username_length' => ((((int) $post['edit_username_length'] >= 3) && ((int) $post['edit_username_length'] <= 100)) ? (int) $post['edit_username_length'] : 30),
			'default_rate' => (float) $post['edit_default_rate'],
			'gateway_module' => ($post['edit_gateway_module'] ? $post['edit_gateway_module'] : 'dev'),
			'themes_module' => ($post['edit_themes_module'] ? $post['edit_themes_module'] : 'default'),
			'language_module' => ($post['edit_language_module'] ? $post['edit_language_module'] : 'en_US'),
			'sms_max_count' => (int) ($post['edit_sms_max_count'] > 1 ? $post['edit_sms_max_count'] : 1),
			'plus_sign_remove' => (int) $post['edit_plus_sign_remove'],
			'plus_sign_add' => (int) $post['edit_plus_sign_add'],
			'enable_credit_unicode' => (int) $post['edit_enable_credit_unicode'],
			'brute_force_detection' => (int) $post['edit_brute_force_detection'],
			'keyword_separator' => substr($post['edit_keyword_separator'], 0, 1),
			'credit_lowest_limit' => (float) $post['edit_credit_lowest_limit'],
			'sms_limit_per_hour' => (int) $post['edit_sms_limit_per_hour'],
			'default_replace_zero' => $post['edit_default_replace_zero'],
			'default_credit' => (float) $post['edit_default_credit'],
			'default_user_status' => $edit_default_user_status,
			'default_parent' => (int) $post['edit_default_parent'],
			'default_acl' => (int) $post['edit_default_acl'],
			'enable_register' => (int) $post['edit_enable_register'],
			'enable_forgot' => (int) $post['edit_enable_forgot'],
			'disable_login_as' => (int) $post['edit_disable_login_as'],
			'enhance_privacy_subuser' => (int) $post['edit_enhance_privacy_subuser'],
			'enable_logo' => (int) $enable_logo,
			'logo_url' => $logo_url,
			'logo_replace_title' => (int) $logo_replace_title,
			'layout_footer' => ($post['edit_layout_footer'] ? $post['edit_layout_footer'] : _('Application footer here. Go to main configuration or manage site to edit this footer.')),
			'information_title' => ($post['edit_information_title'] ? $post['edit_information_title'] : _('Information')),
			'information_content' => ($post['edit_information_content'] ? $post['edit_information_content'] : _('Go to main configuration or manage site to edit this page')) 
		);
		
		$result = registry_update(1, 'core', 'main_config', $items);
		
		_log('main configuration saved. uid:' . $user_config['uid'], 3, 'main_config');
		
		$_SESSION['dialog']['info'][] = _('Main configuration changes has been saved');
		header("Location: " . _u('index.php?app=main&inc=core_main_config&op=main_config'));
		exit();
		break;
}
