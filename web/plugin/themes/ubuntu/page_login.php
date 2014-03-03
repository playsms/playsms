<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_login',
	'var' => array(
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Username' => _('Username'),
		'Password' => _('Password'),
		'Login' => _('Login'),
		'Register an account' => _('Register an account'),
		'Forgot password' => _('Forgot password')
	),
	'if' => array(
		'enable_register' => $core_config['main']['cfg_enable_register'],
		'enable_forgot' => $core_config['main']['cfg_enable_forgot']
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
