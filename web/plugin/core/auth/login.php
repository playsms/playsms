<?php
defined('_SECURE_') or die('Forbidden');

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

if (_OP_ == 'login') {

	if ($_REQUEST['captcha'] && $_SESSION['tmp']['captcha'] && (strtolower($_REQUEST['captcha']) == strtolower($_SESSION['tmp']['captcha']))) {
		unset($_SESSION['tmp']['captcha']);
		$auth_captcha_login = TRUE;
	} else {
		$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');
		$auth_captcha_login = FALSE;
	}

	$username_or_email = trim($_REQUEST['username']);
	$password = trim($_REQUEST['password']);
	
	if ($auth_captcha_login && $username_or_email && $password) {
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
				_log("u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "login");
			} else {
				_log("unable to setup session u:" . $_SESSION['username'] . " status:" . $_SESSION['status'] . " sid:" . session_id() . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "login");
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
	
	// captcha
	$phraseBuilder = new PhraseBuilder($auth_captcha_length, $auth_captcha_seed);
	$captcha = new CaptchaBuilder(null, $phraseBuilder);
	$captcha->buildAgainstOCR($auth_captcha_width, $auth_captcha_height);
	$_SESSION['tmp']['captcha'] = $captcha->getPhrase();

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
