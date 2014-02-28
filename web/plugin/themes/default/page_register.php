<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_register',
	'var' => array(
		'HTTP_PATH_BASE' => $http_path['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Name' => _('Name'),
		'Username' => _('Username'),
		'Mobile' => _('Mobile'),
		'Email' => _('Email'),
		'Register an account' => _('Register an account'),
	'Login' => _('Login'),
	'Submit' => _('Submit'),
	'Forgot password' => _('Forgot password')
    ),
	'if' => array(
		'enable_forgot' => $core_config['main']['cfg_enable_forgot'],
        'enable_logo' => $core_config['cfg_enable_logo']
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
