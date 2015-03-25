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

switch (_OP_) {
	case "mailsms_user" :
		
		$items_global = registry_search(0, 'features', 'mailsms');
		$items = registry_search($user_config['uid'], 'features', 'mailsms_user');
		
		// option enable
		$option_enable = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $items['features']['mailsms_user']['enable']);
		
		$tpl = array(
			'name' => 'mailsms_user',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'FORM_TITLE' => _('My email to SMS'),
				'ACTION_URL' => _u('index.php?app=main&inc=feature_mailsms&route=mailsms_user&op=mailsms_user_save'),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_PASSWORD' => _hint(_('Fill the password field to change password')),
				'Email to SMS address' => _('Email to SMS address'),
				'PIN for email to SMS' => _mandatory(_('PIN for email to SMS')) 
			),
			'injects' => array(
				'option_enable',
				'items_global',
				'items' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "mailsms_user_save" :
		$continue = FALSE;
		
		$pin = core_sanitize_alphanumeric(substr(trim($_REQUEST['pin']), 0, 40));
		if ($pin) {
			$continue = TRUE;
		} else {
			$_SESSION['dialog']['info'][] = _('PIN is empty');
			$_SESSION['dialog']['info'][] = _('Fail to save email to SMS PIN');
		}
		
		if ($continue) {
			$items = array(
				'pin' => $pin 
			);
			registry_update($user_config['uid'], 'features', 'mailsms_user', $items);
			
			$items_global = registry_search(0, 'features', 'mailsms');
			if ($items_global['features']['mailsms']['enable_fetch']) {
				$enabled = 'enabled';
				$_SESSION['dialog']['info'][] = _('Email to SMS PIN has been saved');
			} else {
				$enabled = 'disabled';
				$_SESSION['dialog']['info'][] = _('Email to SMS PIN has been saved but service is disabled');
			}
			_log($enabled . ' uid:' . $user_config['uid'] . ' u:' . $user_config['username'], 2, 'mailsms_user');
		}
		
		header("Location: " . _u('index.php?app=main&inc=feature_mailsms&route=mailsms_user&op=mailsms_user'));
		exit();
		break;
}
