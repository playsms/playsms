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

if (_OP_ == 'forgot') {

	$username = trim($_REQUEST['username']);
	$email = trim($_REQUEST['email']);

	if (auth_isvalid()) {
		header("Location: " . _u($core_config['http_path']['base']));
		exit();
	}

	// verify captcha
	if ($auth_captcha_form_forgot) {
		$session_captcha_phrase = strtolower($_SESSION['tmp']['captcha']['phrase']);
		$session_captcha_time = (int) $_SESSION['tmp']['captcha']['time'];
		unset($_SESSION['tmp']['captcha']);

		if ($_REQUEST['captcha'] && $session_captcha_phrase && (strtolower($_REQUEST['captcha']) == $session_captcha_phrase)) {

			// captcha timeout 15 minutes
			if (time() > ($session_captcha_time + (15 * 60))) {
				_log("fail to verify captcha due to timeout u:" . $username_or_email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

				$_SESSION['dialog']['danger'][] = _('Captcha was expired, please try again');

				header("Location: " . _u('index.php?app=main&inc=core_auth&route=forgot'));
				exit();
			}

		} else {
			_log("fail to verify captcha u:" . $username . " e:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

			$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');

			header("Location: " . _u('index.php?app=main&inc=core_auth&route=forgot'));
			exit();
		}
	}

	if ($core_config['main']['enable_forgot']) {
		if ($username && $email) {
			$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username=? AND email=?";
			$db_result = dba_query($db_query, [$username, $email]);
			$db_row = dba_fetch_array($db_result);
			if ($uid = $db_row['uid']) {
				$tmp_password_plain = core_get_random_string();
				$tmp_password_hashed = password_hash($tmp_password_plain, PASSWORD_BCRYPT);
				if (
					registry_update(
						1,
						'auth',
						'tmp_password',
						[
							$username => $tmp_password_hashed
						]
					)
				) {
					// send email
					$tpl = array(
						'name' => 'auth_forgot_email',
						'vars' => [
							'INFO1' => _('You or someone else have requested a password recovery'),
							'INFO2' => _('This temporary password will be removed once you have logged in successfully'),
							'Username' => _('Username'),
							'Password' => _('Password'),
							'username' => $username,
							'temporary_password' => $tmp_password_plain
						],
						'injects' => array(
							'core_config'
						)
					);
					$email_body = tpl_apply($tpl);
					$email_subject = _('Password recovery');

					$mail_data = array(
						'mail_from_name' => $core_config['main']['web_title'],
						'mail_from' => $core_config['main']['email_service'],
						'mail_to' => $email,
						'mail_subject' => $email_subject,
						'mail_body' => $email_body
					);
					if (sendmail($mail_data)) {
						_log("temporary password has been emailed u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

						$_SESSION['dialog']['info'][] = _('Temporary password has been emailed. You must login immediately.');

						header("Location: " . _u($core_config['http_path']['base']));
						exit();
					} else {
						_log("fail to send email u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

						$_SESSION['dialog']['danger'][] = _('Fail to recover password');
					}
				} else {
					_log("fail to save temporary password u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

					$_SESSION['dialog']['danger'][] = _('Fail to recover password');
				}
			} else {
				_log("username and email pair not found u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

				$_SESSION['dialog']['danger'][] = _('Fail to recover password');
			}
		} else {
			_log("empty username or email u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

			$_SESSION['dialog']['danger'][] = _('Fail to recover password');
		}
	} else {
		_log("attempted to recover password while disabled u:" . $username . " email:" . $email . " ip:" . _REMOTE_ADDR_, 2, "auth forgot");

		$_SESSION['dialog']['danger'][] = _('Recover password disabled');
	}

	header("Location: " . _u('index.php?app=main&inc=core_auth&route=forgot'));
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

	$lastpost = array(
		'username' => _lastpost('username'),
		'email' => _lastpost('email')
	);

	// prepare captcha phrase and set the time
	$captcha_image = '';
	if ($auth_captcha_form_forgot) {
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
		'name' => 'auth_forgot',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'DIALOG_DISPLAY' => _dialog(),
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=forgot&op=forgot'),
			'URL_REGISTER' => _u('index.php?app=main&inc=core_auth&route=register'),
			'URL_LOGIN' => _u('index.php?app=main&inc=core_auth&route=login'),
			'CAPTCHA_IMAGE' => $captcha_image,
			'HINT_CAPTCHA' => _hint(_('Read and type the captcha phrase on verify captcha field. If you cannot read them please contact administrator.')),
			'Username' => _('Username'),
			'Email' => _('Email'),
			'Recover password' => _('Recover password'),
			'Login' => _('Login'),
			'Submit' => _('Submit'),
			'Register an account' => _('Register an account'),
			'Verify captcha' => _('Verify captcha'),
			'logo_url' => $core_config['main']['logo_url']
		),
		'ifs' => array(
			'enable_captcha' => $auth_captcha_form_forgot,
			'enable_register' => $core_config['main']['enable_register'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title
		),
		'injects' => array(
			'lastpost'
		)
	);

	_p(tpl_apply($tpl));
}