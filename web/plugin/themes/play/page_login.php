<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_login',
	'var' => array(
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Login' => _('Login'),
		'Username' => _('Username'),
		'Password' => _('Password'),
		'Submit' => _('Submit'),
		'Cancel' => _('Cancel'),
		'Register an account' => _('Register an account'),
		'Forgot password' => _('Forgot password')
	),
	'if' => array(
		'enable_register' => TRUE,
		'enable_forgot' => TRUE
	)
);

$content = tpl_apply($tpl);

echo themes_apply($content);

?>