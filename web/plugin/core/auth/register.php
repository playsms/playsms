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

use Gregwar\Captcha\CaptchaBuilder;

if (_OP_ == 'register') {
	
	$ok = FALSE;
	
	if (!auth_isvalid()) {
		if ($_REQUEST['captcha'] == $_SESSION['tmp']['captcha']) {
			$data = array();
			$data['name'] = $_REQUEST['name'];
			$data['username'] = $_REQUEST['username'];
			$data['mobile'] = $_REQUEST['mobile'];
			$data['email'] = $_REQUEST['email'];
			
			// force non-admin, status=3 is user and status=4 is subuser
			$data['status'] = ($core_config['main']['default_user_status'] == 3 ? $core_config['main']['default_user_status'] : 4);
			
			// set parent for subuser
			$parent_uid = ((int) $site_config['uid'] ? (int) $site_config['uid'] : 0);
			if ($parent_uid) {
				// regardless of default user status if register form site config then user status become subuser and parent is site config owner
				$data['parent_uid'] = $parent_uid;
				$data['status'] = 4;
			} else {
				$data['parent_uid'] = ($data['status'] == 4 ? $core_config['main']['default_parent'] : 0);
			}
			
			$ret = user_add($data);
			$ok = ($ret['status'] ? TRUE : FALSE);
			if ($ok) {
				$_SESSION['dialog']['info'][] = $ret['error_string'];
			} else {
				$_SESSION['dialog']['danger'][] = $ret['error_string'];
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');
		}
	}
	
	if ($ok) {
		header("Location: " . _u($core_config['http_path']['base']));
	} else {
		header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
	}
	exit();
} else {
	
	$enable_logo = FALSE;
	$show_web_title = TRUE;
	
	if ($core_config['main']['enable_logo'] && $core_config['main']['logo_url']) {
		$enable_logo = TRUE;
		if ($core_config['main']['logo_replace_title']) {
			$show_web_title = FALSE;
		}
	}
	
	// captcha
	$captcha = new CaptchaBuilder();
	$captcha->build();
	$_SESSION['tmp']['captcha'] = $captcha->getPhrase();
	
	$tpl = array(
		'name' => 'auth_register',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'DIALOG_DISPLAY' => _dialog(),
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=register&op=register'),
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot'),
			'URL_LOGIN' => _u('index.php?app=main&inc=core_auth&route=login'),
			'CAPTCHA_IMAGE' => $captcha->inline(),
			'HINT_CAPTCHA' => _hint(_('Read and type the captcha phrase on verify captcha field. If you cannot read them please contact administrator.')),
			'Name' => _('Name'),
			'Username' => _('Username'),
			'Mobile' => _('Mobile'),
			'Email' => _('Email'),
			'Register an account' => _('Register an account'),
			'Login' => _('Login'),
			'Submit' => _('Submit'),
			'Recover password' => _('Recover password'),
			'Verify captcha' => _('Verify captcha'),
			'logo_url' => $core_config['main']['logo_url'] 
		),
		'ifs' => array(
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title 
		) 
	);
	
	_p(tpl_apply($tpl));
}
