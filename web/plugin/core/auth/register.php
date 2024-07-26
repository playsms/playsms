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
use Gregwar\Captcha\PhraseBuilder;

if (auth_isvalid()) {
	header("Location: " . _u($core_config['http_path']['base']));
	exit();
}

if (_OP_ == 'register') {

	// verify captcha
	if ($auth_captcha_form_register) {
		$session_captcha_phrase = $_SESSION['tmp']['captcha']['phrase'];
		$session_captcha_time = (int) $_SESSION['tmp']['captcha']['time'];
		unset($_SESSION['tmp']['captcha']);

		if ($_REQUEST['captcha'] && $session_captcha_phrase && (strtolower($_REQUEST['captcha']) == strtolower($session_captcha_phrase))) {

			// captcha timeout 15 minutes
			if (time() > ($session_captcha_time + (15 * 60))) {
				_log("fail to verify captcha due to timeout u:" . $username_or_email . " ip:" . _REMOTE_ADDR_, 2, "auth register");

				$_SESSION['dialog']['danger'][] = _('Captcha was expired, please try again');

				header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
				exit();
			}

		} else {
			_log("fail to verify captcha ip:" . _REMOTE_ADDR_, 2, "register");

			$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');

			header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
			exit();
		}
	}

	$data = [
		'name' => trim($_REQUEST['name']),
		'username' => trim($_REQUEST['username']),
		'email' => trim($_REQUEST['email']),
		'mobile' => trim($_REQUEST['mobile']),
	];

	if ($core_config['main']['enable_register']) {
		if (!($data['name'] && $data['username'] && $data['email'])) {
			_log("incomplete registration data name:" . $data['name'] . " u:" . $data['username'] . " email:" . $data['email'] . " mobile:" . $data['mobile'] . " ip:" . _REMOTE_ADDR_, 2, "auth register");

			$_SESSION['dialog']['danger'][] = _('Incomplete registration data');

			header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
			exit();
		}
	} else {
		_log("attempted to register an account while disabled name:" . $data['name'] . " u:" . $data['username'] . " email:" . $data['email'] . " mobile:" . $data['mobile'] . " ip:" . _REMOTE_ADDR_, 2, "auth register");

		$_SESSION['dialog']['danger'][] = _('Register an account is disabled');

		header("Location: " . _u($core_config['http_path']['base']));
		exit();
	}

	// force non-admin, status=3 is user and status=4 is subuser
	$data['status'] = $core_config['main']['default_user_status'] == 3 ? $core_config['main']['default_user_status'] : 4;

	// set parent for subuser
	$parent_uid = (int) $site_config['uid'] ? (int) $site_config['uid'] : 0;
	if ($parent_uid) {
		// regardless of default user status if register form site config then user status become subuser and parent is site config owner
		$data['parent_uid'] = $parent_uid;
		$data['status'] = 4;
	} else {
		$data['parent_uid'] = $data['status'] == 4 ? $core_config['main']['default_parent'] : 0;
	}

	$ret = user_add($data, false, false);
	$ok = $ret['status'] ? true : false;
	if ($ok) {

		// injected variable
		$reg_data = $ret['data'];

		// send email
		$tpl = [
			'name' => 'auth_register_email',
			'vars' => [
				'Name' => _('Name'),
				'Username' => _('Username'),
				'Password' => _('Password'),
				'Mobile' => _('Mobile'),
				'Credit' => _('Credit'),
				'Email' => _('Email')
			],
			'injects' => [
				'core_config',
				'reg_data'
			]
		];
		$email_body = tpl_apply($tpl);
		$email_subject = _('New account registration');

		$mail_data = array(
			'mail_from_name' => $core_config['main']['web_title'],
			'mail_from' => $core_config['main']['email_service'],
			'mail_to' => $ret['data']['email'],
			'mail_subject' => $email_subject,
			'mail_body' => $email_body
		);
		if (sendmail($mail_data)) {
			$_SESSION['dialog']['info'][] = _('Account has been added and password has been emailed');
		} else {
			$_SESSION['dialog']['info'][] = _('Account has been added but failed to send email');
		}
	} else {
		$_SESSION['dialog']['danger'][] = $ret['error_string'];
	}

	header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
	exit();
} else {

	$enable_logo = false;
	$show_web_title = true;

	if ($core_config['main']['enable_logo'] && $core_config['main']['logo_url']) {
		$enable_logo = true;
		if ($core_config['main']['logo_replace_title']) {
			$show_web_title = false;
		}
	}

	$lastpost = [
		'name' => _lastpost('name'),
		'username' => _lastpost('username'),
		'mobile' => _lastpost('mobile'),
		'email' => _lastpost('email')
	];

	// prepare captcha phrase and set the time
	$captcha_image = '';
	if ($auth_captcha_form_register) {
		$phraseBuilder = new PhraseBuilder($auth_captcha_length, $auth_captcha_seed);
		$captcha = new CaptchaBuilder(null, $phraseBuilder);
		$captcha->build($auth_captcha_width, $auth_captcha_height);
		$_SESSION['tmp']['captcha'] = [
			'phrase' => $captcha->getPhrase(),
			'time' => time(),
		];
		$captcha_image = $captcha->inline();
	}

	$tpl = array(
		'name' => 'auth_register',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'DIALOG_DISPLAY' => _dialog(),
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=register&op=register'),
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot'),
			'URL_LOGIN' => _u('index.php?app=main&inc=core_auth&route=login'),
			'CAPTCHA_IMAGE' => $captcha_image,
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
			'enable_captcha' => $auth_captcha_form_register,
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title
		),
		'injects' => array(
			'lastpost'
		)
	);

	_p(tpl_apply($tpl));
}