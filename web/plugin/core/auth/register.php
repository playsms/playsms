<?php
defined('_SECURE_') or die('Forbidden');

if (_OP_ == 'register') {
	
	$ok = FALSE;
	
	if (!auth_isvalid()) {
		$data = array();
		$data['name'] = $_REQUEST['name'];
		$data['username'] = $_REQUEST['username'];
		$data['mobile'] = $_REQUEST['mobile'];
		$data['email'] = $_REQUEST['email'];

		// force non-admin, status=3 is normal user
		$data['status'] = 3;
		
		// empty this and playSMS will generate random password
		$data['password'] = '';
		
		$ret = user_add($data);
		$ok = ($ret['status'] ? TRUE : FALSE);
		$_SESSION['error_string'] = $ret['error_string'];
	}
	
	if ($ok) {
		header("Location: " . _u($core_config['http_path']['base']));
	} else {
		header("Location: " . _u('index.php?app=main&inc=core_auth&route=register'));
	}
	exit();
} else {
	
	// error string
	if ($_SESSION['error_string']) {
		$error_content = '<div class="error_string">' . $_SESSION['error_string'] . '</div>';
	}
	
	unset($tpl);
	$tpl = array(
		'name' => 'auth_register',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $web_title,
			'ERROR' => $error_content,
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=register&op=register') ,
			'URL_FORGOT' => _u('index.php?app=main&inc=core_auth&route=forgot') ,
			'URL_LOGIN' => _u('index.php?app=main&inc=core_auth&route=login') ,
			'Name' => _('Name') ,
			'Username' => _('Username') ,
			'Mobile' => _('Mobile') ,
			'Email' => _('Email') ,
			'Register an account' => _('Register an account') ,
			'Login' => _('Login') ,
			'Submit' => _('Submit') ,
			'Recover password' => _('Recover password') ,
			'logo_url' => $core_config['main']['logo_url']
		) ,
		'ifs' => array(
			'enable_forgot' => $core_config['main']['enable_forgot'],
			'enable_logo' => $core_config['main']['enable_logo'],
			'logo_replace_title' => ($core_config['main']['logo_replace_title'] ? FALSE : TRUE) ,
		)
	);
	
	_p(tpl_apply($tpl));
}
