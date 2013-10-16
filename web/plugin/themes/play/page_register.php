<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_register',
	'var' => array(
		'HTTP_PATH_BASE' => $http_path['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Register' => _('Register'),
		'Name' => _('Name'),
		'Username' => _('Username'),
		'Mobile' => _('Mobile'),
		'Email' => _('Email'),
		'Login' => _('Login'),
		'Submit' => _('Submit'),
		'Cancel' => _('Cancel'),
		'Register an account' => _('Register an account'),
		'Forgot password' => _('Forgot password')
	),
	'if' => array(
		'enable_forgot' => $core_config['main']['cfg_enable_forgot']
	)
);

$content = tpl_apply($tpl);

echo themes_apply($content);

?>