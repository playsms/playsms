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
			$c_user = user_getdatabyusername($username);
			$_SESSION['sid'] = session_id();
			$_SESSION['username'] = $c_user['username'];
			$_SESSION['uid'] = $c_user['uid'];
			$_SESSION['status'] = $c_user['status'];
			$_SESSION['valid'] = true;
			
			// save session in registry
			if (!$core_config['daemon_process']) {
				user_session_set($c_user['uid']);
			}
			logger_print("u:" . $_SESSION['username'] . " status:" . $_SESSION['status'] . " sid:" . $_SESSION['sid'] . " ip:" . $_SERVER['REMOTE_ADDR'], 2, "login");
		} else {
			$_SESSION['error_string'] = _('Invalid username or password');
		}
	}
	
	header("Location: " . _u($core_config['http_path']['base']));
	exit();
} else {
	
	// error string
	if ($_SESSION['error_string']) {
		$error_content = '<div class="error_string">' . $_SESSION['error_string'] . '</div>';
	}
	
	unset($tpl);
	$tpl = array(
		'name' => 'auth_login',
		'var' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $web_title,
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=login&op=login') ,
			'URL_REGISTER' => _u('index.php?app=main&inc=core_auth&route=register') ,
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot') ,
			'ERROR' => $error_content,
			'Username or email' => _('Username or email') ,
			'Password' => _('Password') ,
			'Login' => _('Login') ,
			'Register an account' => _('Register an account') ,
			'Recover password' => _('Recover password') ,
			'logo_url' => $core_config['main']['logo_url']
		) ,
		'if' => array(
			'enable_register' => $core_config['main']['enable_register'],
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $core_config['main']['enable_logo'],
			'logo_replace_title' => ($core_config['main']['logo_replace_title'] ? FALSE : TRUE) ,
		)
	);
	
	$content = tpl_apply($tpl);
	
	_p(tpl_apply($tpl));
}
