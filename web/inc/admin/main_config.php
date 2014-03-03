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

switch ($op) {
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
		$edit_web_title = $_POST['edit_web_title'];
		$edit_email_service = $_POST['edit_email_service'];
		$edit_email_footer = $_POST['edit_email_footer'];
		$edit_main_website_name = $_POST['edit_main_website_name'];
		$edit_main_website_url = $_POST['edit_main_website_url'];
		$edit_gateway_number = core_sanitize_sender($_POST['edit_gateway_number']);
		$edit_gateway_timezone = $_POST['edit_gateway_timezone'];
		$edit_default_rate = $_POST['edit_default_rate'];
		$edit_gateway_module = $_POST['edit_gateway_module'];
		$edit_themes_module = $_POST['edit_themes_module'];
		$edit_language_module = $_POST['edit_language_module'];
		$edit_sms_max_count = ( $_POST['edit_sms_max_count'] > 1 ? $_POST['edit_sms_max_count'] : 1 );
		$edit_default_credit = $_POST['edit_default_credit'];
		$edit_enable_register = $_POST['edit_enable_register'];
		$edit_enable_forgot = $_POST['edit_enable_forgot'];
		$edit_allow_custom_sender = $_POST['edit_allow_custom_sender'];
		$edit_allow_custom_footer = $_POST['edit_allow_custom_footer'];
		$db_query = "
			UPDATE "._DB_PREF_."_tblConfig_main 
			SET c_timestamp='".mktime()."',
				cfg_web_title='$edit_web_title',
				cfg_email_service='$edit_email_service',
				cfg_email_footer='$edit_email_footer',
				cfg_main_website_name='$edit_main_website_name',
				cfg_main_website_url='$edit_main_website_url',
				cfg_gateway_number='$edit_gateway_number',
				cfg_default_rate='$edit_default_rate',
				cfg_datetime_timezone='$edit_gateway_timezone',
				cfg_gateway_module='$edit_gateway_module',
				cfg_themes_module='$edit_themes_module',
				cfg_language_module='$edit_language_module',
				cfg_sms_max_count='$edit_sms_max_count',
				cfg_default_credit='$edit_default_credit',
				cfg_enable_register='$edit_enable_register',
				cfg_enable_forgot='$edit_enable_forgot',
				cfg_allow_custom_sender='$edit_allow_custom_sender',
				cfg_allow_custom_footer='$edit_allow_custom_footer'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Main configuration changes has been saved');
		header("Location: index.php?app=menu&inc=main_config&op=main_config");
		exit();
		break;
}
