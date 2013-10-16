<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_login',
	'var' => array(
		'HTTP_PATH_BASE' => $http_path['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Username' => _('Username'),
		'Password' => _('Password'),
		'Login' => _('Login'),
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