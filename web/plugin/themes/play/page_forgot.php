<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_forgot',
	'var' => array(
		'HTTP_PATH_BASE' => $http_path['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Username' => _('Username'),
		'Email' => _('Email'),
		'Login' => _('Login'),
		'Forgot password' => _('Forgot password'),
		'Recover password' => _('Recover password'),
		'Register an account' => _('Register an account')
	),
	'if' => array(
		'enable_register' => $core_config['main']['cfg_enable_register']
	)
);

$content = tpl_apply($tpl);

echo themes_apply($content);

?>