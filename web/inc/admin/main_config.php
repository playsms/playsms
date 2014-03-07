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
if(!auth_isadmin()){auth_block();};

switch (_OP_) {
	case "main_config":
		// enable register yes-no option
		if ($enable_register) { $selected1 = "selected"; } else { $selected2 = "selected"; };
		$option_enable_register = "<option value=\"1\" $selected1>"._('yes')."</option>";
		$option_enable_register .= "<option value=\"0\" $selected2>"._('no')."</option>";
		$selected1 = ""; $selected2 = "";
		// enable forgot yes-no option
		if ($enable_forgot) { $selected1 = "selected"; } else { $selected2 = "selected"; };
		$option_enable_forgot = "<option value=\"1\" $selected1>"._('yes')."</option>";
		$option_enable_forgot .= "<option value=\"0\" $selected2>"._('no')."</option>";
		$selected1 = ""; $selected2 = "";
		// allow edit sender yes-no option
		if ($allow_custom_sender) {
			$selected1 = "selected";
		} else {
			$selected2 = "selected";
		};
		$option_allow_custom_sender = "<option value=\"1\" $selected1>" . _('yes') . "</option>";
		$option_allow_custom_sender .= "<option value=\"0\" $selected2>" . _('no') . "</option>";
		$selected1 = "";
		$selected2 = "";
		// allow edit footer yes-no option
		if ($allow_custom_footer) {
			$selected1 = "selected";
		} else {
			$selected2 = "selected";
		};
		$option_allow_custom_footer = "<option value=\"1\" $selected1>" . _('yes') . "</option>";
		$option_allow_custom_footer .= "<option value=\"0\" $selected2>" . _('no') . "</option>";
		$selected1 = "";
		$selected2 = "";
		// get gateway options
		for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
			$gateway = $core_config['gatewaylist'][$i];
			$gw = core_gateway_get();
			if ($gateway == $gw) $selected = "selected";
			$option_gateway_module .= "<option value=\"$gateway\" $selected>$gateway</option>";
			$selected = "";
		}
		// get themes options
		for ($i=0;$i<count($core_config['themeslist']);$i++) {
			$themes = $core_config['themeslist'][$i];
			if ($themes == $themes_module) $selected = "selected";
			$option_themes_module .= "<option value=\"$themes\" $selected>$themes</option>";
			$selected = "";
		}
		// get language options
		$lang_list = '';
		for ($i=0;$i<count($core_config['languagelist']);$i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $plugin_config[$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == $language_module) $selected = "selected";
				$option_language_module .= "<option value=\"".$val."\" $selected>".$key."</option>";
				$selected = "";
			}
		}

		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$tpl = array(
		    'name' => 'main_config',
		    'var' => array(
			'ERROR' => $error_content,
			'Main configuration' => _('Main configuration'),
			'Website title' => _('Website title'),
			'Website email' => _('Website email'),
			'Forwarded email footer' => _('Forwarded email footer'),
			'Main website name' => _('Main website name'),
			'Main website URL' => _('Main website URL'),
			'Default sender ID' => _('Default sender ID'),
			'Default timezone' => _('Default timezone'),
			'Default SMS rate' => _('Default SMS rate'),
			'Maximum SMS count' => _('Maximum SMS count'),
			'Default credit for user' => _('Default credit for user'),
			'Enable public registration' => _('Enable public registration'),
			'Enable forgot password' => _('Enable forgot password'),
			'Allow custom sender ID' => _('Allow custom sender ID'),
			'Allow custom SMS footer' => _('Allow custom SMS footer'),
			'Active gateway module' => _('Active gateway module'),
			'Active themes' => _('Active themes'),
			'Default language' => _('Default language'),
			'Save' => _('Save'),
			'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
			'web_title' => $web_title,
			'email_service' => $email_service,
			'email_footer' => $email_footer,
			'main_website_name' => $main_website_name,
			'main_website_url' => $main_website_url,
			'gateway_number' => $gateway_number,
			'gateway_timezone' => $gateway_timezone,
			'default_rate' => $default_rate,
			'sms_max_count' => $sms_max_count,
			'default_credit' => $default_credit,
			'option_enable_register' => $option_enable_register,
			'option_enable_forgot' => $option_enable_forgot,
			'option_allow_custom_sender' => $option_allow_custom_sender,
			'option_allow_custom_footer' => $option_allow_custom_footer,
			'option_gateway_module' => $option_gateway_module,
			'option_themes_module' => $option_themes_module,
			'option_language_module' => $option_language_module
		    )
		);
		_p(tpl_apply($tpl));
		break;
	case "main_config_save":
		$items = array(
			'web_title' => $_POST['edit_web_title'],
			'email_service' => $_POST['edit_email_service'],
			'email_footer' => $_POST['edit_email_footer'],
			'main_website_name' => $_POST['edit_main_website_name'],
			'main_website_url' => $_POST['edit_main_website_url'],
			'gateway_number' => core_sanitize_sender($_POST['edit_gateway_number']),
			'gateway_timezone' => $_POST['edit_gateway_timezone'],
			'default_rate' => (float) $_POST['edit_default_rate'],
			'gateway_module' => ( $_POST['edit_gateway_module'] ? $_POST['edit_gateway_module'] : 'dev' ),
			'themes_module' => ( $_POST['edit_themes_module'] ? $_POST['edit_themes_module'] : 'default' ),
			'language_module' => ( $_POST['edit_language_module'] ? $_POST['edit_language_module'] : 'en_US' ),
			'sms_max_count' => (int) ( $_POST['edit_sms_max_count'] > 1 ? $_POST['edit_sms_max_count'] : 1 ),
			'default_credit' => (float) $_POST['edit_default_credit'],
			'enable_register' => (int) $_POST['edit_enable_register'],
			'enable_forgot' => (int) $_POST['edit_enable_forgot'],
			'allow_custom_sender' => $_POST['edit_allow_custom_sender'],
			'allow_custom_footer' => $_POST['edit_allow_custom_footer']
		);
		$result = registry_update(1, 'core', 'main_config', $items);
		$_SESSION['error_string'] = _('Main configuration changes has been saved');
		header("Location: index.php?app=main&inc=main_config&op=main_config");
		exit();
		break;
}
