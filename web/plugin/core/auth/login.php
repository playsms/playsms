<?php
defined('_SECURE_') or die('Forbidden');

if (_OP_ == 'login') {
	
	$username_or_email = trim($_REQUEST['username']);
	$password = trim($_REQUEST['password']);
	
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
			auth_session_setup($uid);
			if (auth_isvalid()) {
				logger_print("u:" . $_SESSION['username'] . " uid:" . $uid . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "login");
			} else {
				logger_print("unable to setup session u:" . $_SESSION['username'] . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "login");
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
	
	unset($tpl);
	$tpl = array(
		'name' => 'auth_login',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=login&op=login') ,
			'URL_REGISTER' => _u('index.php?app=main&inc=core_auth&route=register') ,
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot') ,
			'DIALOG_DISPLAY' => _dialog(),
			'Username or email' => _('Username or email') ,
			'Password' => _('Password') ,
			'Login' => _('Login') ,
			'Register an account' => _('Register an account') ,
			'Recover password' => _('Recover password') ,
			'logo_url' => $core_config['main']['logo_url']
		) ,
		'ifs' => array(
			'enable_register' => $core_config['main']['enable_register'],
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title,
		)
	);
	
	_p(tpl_apply($tpl));
}
