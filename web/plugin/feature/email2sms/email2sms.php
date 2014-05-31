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

if (!auth_isvalid()) {
	auth_block();
}

switch (_OP_) {
	case "email2sms":
		
		$items = registry_search($user_config['uid'], 'features', 'email2sms');
		
		// option enable
		$option_enable = _options(array(
			_('yes') => 1,
			_('no') => 0,
		) , $items['features']['email2sms']['enable']);
		
		// option protocol
		$option_protocol = _options(array(
			'IMAP' => 'imap',
			'POP3' => 'pop3',
		) , $items['features']['email2sms']['protocol']);
		
		// option ssl
		$option_ssl = _options(array(
			_('yes') => 1,
			_('no') => 0,
		) , $items['features']['email2sms']['ssl']);
		
		// option cert
		$option_novalidate_cert = _options(array(
			_('yes') => 1,
			_('no') => 0,
		) , $items['features']['email2sms']['novalidate_cert']);
		
		$tpl = array(
			'name' => 'email2sms',
			'vars' => array(
				'ERROR' => _err_display() ,
				'FORM_TITLE' => _('Manage email to SMS') ,
				'ACTION_URL' => _u('index.php?app=main&inc=feature_email2sms&op=email2sms_save') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_PASSWORD' => _hint('Fill the password field to change password') ,
				'PIN for email to SMS' => _mandatory('PIN for email to SMS') ,
				'Enable email to SMS' => _('Enable email to SMS') ,
				'Email protocol' => _('Email protocol') ,
				'Use SSL' => _('Use SSL') ,
				'No validate cert option' => _('No validate cert option') ,
				'Mail server address' => _('Mail server address') ,
				'Mail server port' => _('Mail server port') ,
				'Mailbox username' => _('Mailbox username') ,
				'Mailbox password' => _('Mailbox password') ,
				'PORT_DEFAULT' => '443',
				'PORT_DEFAULT_SSL' => '993',
			) ,
			'injects' => array(
				'select_users',
				'option_enable',
				'option_protocol',
				'option_ssl',
				'option_novalidate_cert',
				'items',
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "email2sms_save":
		$continue = FALSE;
		
		$pin = core_sanitize_alphanumeric(substr($_REQUEST['pin'], 0, 40));
		if ($pin) {
			$continue = TRUE;
		} else {
			$_SESSION['error_string'][] = _('PIN is empty');
			$_SESSION['error_string'][] = _('Fail to save email to SMS configuration');
		}
		
		if ($continue) {
			$items = array(
				'pin' => $pin,
				'enable' => $_REQUEST['enable'],
				'protocol' => $_REQUEST['protocol'],
				'ssl' => $_REQUEST['ssl'],
				'novalidate_cert' => $_REQUEST['novalidate_cert'],
				'port' => $_REQUEST['port'],
				'server' => $_REQUEST['server'],
				'username' => $_REQUEST['username'],
				'hash' => md5($_REQUEST['username'] . $_REQUEST['server'] . $_REQUEST['port']) ,
			);
			if ($_REQUEST['password']) {
				$items['password'] = $_REQUEST['password'];
			}
			registry_update($user_config['uid'], 'features', 'email2sms', $items);
			
			if ($_REQUEST['enable']) {
				$enabled = 'enabled';
				$_SESSION['error_string'] = _('Email to SMS configuration has been saved and enabled');
			} else {
				$enabled = 'disabled';
				$_SESSION['error_string'] = _('Email to SMS configuration has been saved but disabled');
			}
			_log($enabled . ' uid:' . $user_config['uid'] . ' u:' . $_REQUEST['username'] . ' server:' . $_REQUEST['server'], 2, 'email2sms');
		}
		
		header("Location: " . _u('index.php?app=main&inc=feature_email2sms&op=email2sms'));
		exit();
		break;
}
