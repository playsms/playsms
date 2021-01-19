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

if (_OP_ == 'login') {

	$username_or_email = trim($_REQUEST['username']);
	$password = trim($_REQUEST['password']);
	
	// verify captcha
	if ($auth_captcha_form_login) {
		$session_captcha_phrase = strtolower($_SESSION['tmp']['captcha']['phrase']);
		$session_captcha_time = (int) $_SESSION['tmp']['captcha']['time'];
		unset($_SESSION['tmp']['captcha']);
	
		if ($_REQUEST['captcha'] && $session_captcha_phrase && (strtolower($_REQUEST['captcha']) == $session_captcha_phrase)) {
		
			// captcha timeout 15 minutes
			if (time() > ($session_captcha_time + (15 * 60))) {
				_log("fail to verify captcha due to timeout u:" . $username_or_email . " ip:" . _REMOTE_ADDR_, 2, "auth login");

				$_SESSION['dialog']['danger'][] = _('Captcha was expired, please try again');

				header("Location: " . _u($core_config['http_path']['base']));
				exit();
			}
			
		} else {
			_log("fail to verify captcha u:" . $username_or_email . " ip:" . _REMOTE_ADDR_, 2, "auth login");

			$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');

			header("Location: " . _u($core_config['http_path']['base']));
			exit();
		}
	}

	if ($username_or_email && $password) {
		$username = '';
		$validated = FALSE;
		
		if (preg_match('/^(.+)@(.+)\.(.+)$/', $username_or_email)) {
			if (auth_validate_email($username_or_email, $password)) {
				$username = user_email2username($username_or_email);
				$validated = TRUE;
			}
		} else {
			if (auth_validate_login($username_or_email, $password)) {
				$username = $username_or_email;
				$validated = TRUE;
			}
		}
		
		if ($validated) {
			$uid = user_username2uid($username);
			
			// setup new session after successful login
			auth_session_setup($uid);
			
			if (auth_isvalid()) {
				_log("u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . _REMOTE_ADDR_, 2, "auth login");
			} else {
				_log("unable to setup session u:" . $_SESSION['username'] . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . _REMOTE_ADDR_, 2, "auth login");
				$_SESSION['dialog']['danger'][] = _('Unable to login');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid username or password');
		}
	}
	
	header("Location: " . _u($core_config['http_path']['base']));
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

	$lastpost = array(
		'username' => _lastpost('username')
	);
	
	// prepare captcha phrase and set the time
	if ($auth_captcha_form_login) {
		$phraseBuilder = new PhraseBuilder($auth_captcha_length, $auth_captcha_seed);
		$captcha = new CaptchaBuilder(null, $phraseBuilder);
		$captcha->build($auth_captcha_width, $auth_captcha_height);
		$_SESSION['tmp']['captcha'] = [
			'phrase' => $captcha->getPhrase(),
			'time' => time(),
		];
	}

	unset($tpl);
	$tpl = array(
		'name' => 'auth_login',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=login&op=login') ,
			'URL_REGISTER' => _u('index.php?app=main&inc=core_auth&route=register') ,
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot') ,
			'CAPTCHA_IMAGE' => $captcha->inline(),
			'HINT_CAPTCHA' => _hint(_('Read and type the captcha phrase on verify captcha field. If you cannot read them please contact administrator.')),
			'DIALOG_DISPLAY' => _dialog(),
			'Username or email' => _('Username or email') ,
			'Password' => _('Password') ,
			'Login' => _('Login') ,
			'Register an account' => _('Register an account') ,
			'Recover password' => _('Recover password') ,
			'Verify captcha' => _('Verify captcha'),
			'logo_url' => $core_config['main']['logo_url']
		) ,
		'ifs' => array(
			'enable_captcha' => $auth_captcha_form_login,
			'enable_register' => $core_config['main']['enable_register'],
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title,
		),
		'injects' => array(
			'lastpost'
		)
	);
	
	_p(tpl_apply($tpl));
}
