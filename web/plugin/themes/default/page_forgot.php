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
		'Recover password' => _('Recover password'),
		'Login' => _('Login'),
	'Submit' => _('Submit'),
	'Register an account' => _('Register an account')
    ),
	'if' => array(
		'enable_register' => $core_config['main']['cfg_enable_register'],
        'enable_logo' => $core_config['cfg_enable_logo']
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
