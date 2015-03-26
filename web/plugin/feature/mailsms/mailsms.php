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
	case "mailsms" :
		
		$items_global = registry_search(0, 'features', 'mailsms');
		
		// option enable fetch
		$option_enable_fetch = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $items_global['features']['mailsms']['enable_fetch']);
		
		// option check email sender
		$option_check_sender = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $items_global['features']['mailsms']['check_sender']);
		
		// option protocol
		$option_protocol = _options(array(
			'IMAP' => 'imap',
			'POP3' => 'pop3' 
		), $items_global['features']['mailsms']['protocol']);
		
		// option ssl
		$option_ssl = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $items_global['features']['mailsms']['ssl']);
		
		// option cert
		$option_novalidate_cert = _options(array(
			_('yes') => 1,
			_('no') => 0 
		), $items_global['features']['mailsms']['novalidate_cert']);
		
		$tpl = array(
			'name' => 'mailsms',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'FORM_TITLE' => _('Manage email to SMS'),
				'ACTION_URL' => _u('index.php?app=main&inc=feature_mailsms&op=mailsms_save'),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_PASSWORD' => _hint(_('Fill the password field to change password')),
				'Email to SMS address' => _('Email to SMS address'),
				'Enable fetch new emails' => _('Enable fetch new emails'),
				'Check email sender' => _('Check email sender'),
				'Email protocol' => _('Email protocol'),
				'Use SSL' => _('Use SSL'),
				'No validate cert option' => _('No validate cert option'),
				'Mail server address' => _('Mail server address'),
				'Mail server port' => _('Mail server port'),
				'Mailbox username' => _('Mailbox username'),
				'Mailbox password' => _('Mailbox password'),
				'PORT_DEFAULT' => '443',
				'PORT_DEFAULT_SSL' => '993' 
			),
			'injects' => array(
				'option_enable_fetch',
				'option_check_sender',
				'option_protocol',
				'option_ssl',
				'option_novalidate_cert',
				'items_global' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "mailsms_save" :
		$items_global = array(
			'email' => $_REQUEST['email'],
			'enable_fetch' => $_REQUEST['enable_fetch'],
			'check_sender' => $_REQUEST['check_sender'],
			'protocol' => $_REQUEST['protocol'],
			'ssl' => $_REQUEST['ssl'],
			'novalidate_cert' => $_REQUEST['novalidate_cert'],
			'port' => $_REQUEST['port'],
			'server' => $_REQUEST['server'],
			'username' => $_REQUEST['username'],
			'hash' => md5($_REQUEST['username'] . $_REQUEST['server'] . $_REQUEST['port']) 
		);
		if ($_REQUEST['password']) {
			$items_global['password'] = $_REQUEST['password'];
		}
		registry_update(0, 'features', 'mailsms', $items_global);
		
		if ($_REQUEST['enable_fetch']) {
			$enabled = 'enabled';
			$_SESSION['dialog']['info'][] = _('Email to SMS configuration has been saved and service enabled');
		} else {
			$enabled = 'disabled';
			$_SESSION['dialog']['info'][] = _('Email to SMS configuration has been saved and service disabled');
		}
		_log($enabled . ' server:' . $_REQUEST['server'], 2, 'mailsms');
		
		header("Location: " . _u('index.php?app=main&inc=feature_mailsms&op=mailsms'));
		exit();
		break;
}
