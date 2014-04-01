<?php
defined('_SECURE_') or die('Forbidden');

// error string
if ($_SESSION['error_string']) {
	$error_content = '<div class="error_string">'.$_SESSION['error_string'].'</div>';
}

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
		'Recover password' => _('Recover password'),
		'logo_url' => $core_config['main']['logo_url']
	),
	'if' => array(
		'enable_register' => $core_config['main']['enable_register'],
		'enable_forgot' => $core_config['main']['enable_forgot'],
		'enable_logo' => $core_config['main']['enable_logo'],
		'logo_replace_title' => ( $core_config['main']['logo_replace_title'] ? FALSE : TRUE ),
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
