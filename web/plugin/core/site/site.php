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

// admin and normal users allowed to use this plugin
if (!(($user_config['status'] == 2) || ($user_config['status'] == 3))) {
	auth_block();
}

switch (_OP_) {
	case "site_config":
		$site_config = site_config_get();
		
		// enable register yes-no option
		$selected = ($site_config['enable_register'] ? _('yes') : _('no'));
		$options['enable_register'] = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $site_config['enable_register']);
		
		// enable forgot yes-no option
		$selected = ($site_config['enable_forgot'] ? _('yes') : _('no'));
		$options['enable_forgot'] = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $site_config['enable_forgot']);
		
		// enable logo yes-no option
		$options['enable_logo'] = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $site_config['enable_logo']);
		
		// enable logo to replace main website title yes-no option
		$options['logo_replace_title'] = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $site_config['logo_replace_title']);
		
		// get themes options
		$options['themes_module'] = _options($core_config['themeslist'], $site_config['themes_module']);
		
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
			$options['language_module'] = _options($lang_list, $site_config['language_module']);
		}
		
		$tpl = array(
			'name' => 'site',
			'vars' => array(
				'ACTION_URL' => _u('index.php?app=main&inc=core_site&op=site_config_save'),
				'HINT_ENABLE_LOGO' => _hint(_('Logo by default will be displayed at login, register and forgot password page')),
				'HINT_DOMAIN' => _hint('Put your domain name here and then set your domain DNS A record to this server IP address'),
				'DIALOG_DISPLAY' => _dialog(),
				'Manage site' => _('Manage site'),
				'Site configuration' => _('Site configuration'),
				'Configuration' => _('Configuration'),
				'Information page' => _('Information page'),
				'Buy credit page' => _('Buy credit page'),
				'Page title' => _('Page title'),
				'Page content' => _('Page content'),
				'Domain' => _('Domain'),
				'Website title' => _('Website title'),
				'Email service' => _('Email service'),
				'Email footer' => _('Email footer'),
				'Main website name' => _('Main website name'),
				'Main website URL' => _('Main website URL'),
				'Enable logo' => _('Enable logo'),
				'Logo URL' => _('Logo URL'),
				'Replace website title with logo' => _('Replace website title with logo'),
				'Enable public registration' => _('Enable public registration'),
				'Enable forgot password' => _('Enable forgot password'),
				'Active themes' => _('Active themes'),
				'Default language' => _('Default language'),
				'Default credit upon registration' => _('Default credit upon registration'),
				'Layout footer' => _('Layout footer'),
				'Save' => _('Save') 
			),
			'injects' => array(
				'core_config',
				'site_config',
				'options' 
			) 
		);
		
		_p(tpl_apply($tpl));
		break;
	
	case "site_config_save":
		foreach ($_POST['up'] as $key => $val) {
			$up[$key] = $val;
		}
		
		$site = site_config_getbydomain($up['domain']);
		if ($up['domain'] && $site[0]['uid'] && $site[0]['uid'] != $user_config['uid']) {
			$_SESSION['dialog']['info'][] = _('The domain is already configured by other user') . ' (' . _('domain') . ':' . $up['domain'] . ')';
		} else {
			site_config_set($up);
			$_SESSION['dialog']['info'][] = _('Site configuration has been saved');
		}
		
		_log('site configuration saved. uid:' . $user_config['uid'] . ' domain:' . $up['domain'], 3, 'site');
		
		header('Location:' . _u('index.php?app=main&inc=core_site&op=site_config'));
		exit();
		break;
}
