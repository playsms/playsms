<?php
defined('_SECURE_') or die('Forbidden');

use Gregwar\Captcha\CaptchaBuilder;

if (_OP_ == 'forgot') {
	
	$username = trim($_REQUEST['username']);
	$email = trim($_REQUEST['email']);
	
	$ok = FALSE;
	
	if (!auth_isvalid()) {
		if ($_REQUEST['captcha'] == $_SESSION['tmp']['captcha']) {
			if ($core_config['main']['enable_forgot']) {
				if ($username && $email) {
					$db_query = "SELECT password FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username='$username' AND email='$email'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						if ($password = $db_row['password']) {
							$tmp_password = core_get_random_string();
							$tmp_password_coded = md5($tmp_password);
							if (registry_update(1, 'auth', 'tmp_password', array(
								$username => $tmp_password_coded 
							))) {
								$subject = _('Password recovery');
								$body = $core_config['main']['web_title'] . "\n\n";
								$body .= _('You or someone else have requested a password recovery') . "\n\n";
								$body .= _('This temporary password will be removed once you have logged in successfully') . "\n\n";
								$body .= _('Username') . "\t: " . $username . "\n";
								$body .= _('Password') . "\t: " . $tmp_password . "\n\n--\n";
								$body .= $core_config['main']['email_footer'] . "\n\n";
								$data = array(
									'mail_from_name' => $core_config['main']['web_title'],
									'mail_from' => $core_config['main']['email_service'],
									'mail_to' => $email,
									'mail_subject' => $subject,
									'mail_body' => $body 
								);
								if (sendmail($data)) {
									$error_string = _('Password has been emailed') . " (" . _('Username') . ": " . $username . ")";
									$_SESSION['dialog']['info'][] = $error_string;
									$ok = TRUE;
								} else {
									$error_string = _('Fail to send email');
									$_SESSION['dialog']['danger'][] = $error_string;
								}
							} else {
								$error_string = _('Fail to save temporary password');
								$_SESSION['dialog']['danger'][] = $error_string;
							}
							
							logger_print("u:" . $username . " email:" . $email . " ip:" . $_SERVER['REMOTE_ADDR'] . " error_string:[" . $error_string . "]", 2, "forgot");
						} else {
							$_SESSION['dialog']['danger'][] = _('Fail to recover password');
						}
					} else {
						$_SESSION['dialog']['danger'][] = _('Fail to recover password');
					}
				} else {
					$_SESSION['dialog']['danger'][] = _('Fail to recover password');
				}
			} else {
				$_SESSION['dialog']['danger'][] = _('Recover password disabled');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('Please type the displayed captcha phrase correctly');
		}
	}
	
	if ($ok) {
		header("Location: " . _u($core_config['http_path']['base']));
	} else {
		header("Location: " . _u('index.php?app=main&inc=core_auth&route=forgot'));
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
		'name' => 'auth_forgot',
		'vars' => array(
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'WEB_TITLE' => $core_config['main']['web_title'],
			'DIALOG_DISPLAY' => _dialog(),
			'URL_ACTION' => _u('index.php?app=main&inc=core_auth&route=forgot&op=forgot'),
			'URL_REGISTER' => _u('index.php?app=main&inc=core_auth&route=register'),
			'URL_LOGIN' => _u('index.php?app=main&inc=core_auth&route=login'),
			'CAPTCHA_IMAGE' => $captcha->inline(),
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
			'enable_register' => $core_config['main']['enable_register'],
			'enable_logo' => $enable_logo,
			'show_web_title' => $show_web_title 
		) 
	);
	
	_p(tpl_apply($tpl));
}
