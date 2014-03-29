<?php
defined('_SECURE_') or die('Forbidden');

// error string
if ($_SESSION['error_string']) {
	$error_content = '<div class="error_string">'.$_SESSION['error_string'].'</div>';
}

unset($tpl);
$tpl = array(
	'name' => 'page_forgot',
	'var' => array(
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'WEB_TITLE' => $web_title,
		'ERROR' => $error_content,
		'Username' => _('Username'),
		'Email' => _('Email'),
		'Recover password' => _('Recover password'),
		'Login' => _('Login'),
		'Submit' => _('Submit'),
		'Register an account' => _('Register an account'),
		'logo_url' => $core_config['main']['logo_url']
	),
	'if' => array(
		'enable_register' => $core_config['main']['enable_register'],
		'enable_logo' => $core_config['main']['enable_logo']
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
