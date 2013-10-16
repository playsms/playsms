<?php
defined('_SECURE_') or die('Forbidden');

empty($tpl);
$tpl = array(
	'HTTP_PATH_BASE' => $http_path['base'],
	'WEB_TITLE' => $web_title,
	'ERROR' => $error_content,
	'Name' => _('Name'),
	'Username' => _('Username'),
	'Mobile' => _('Mobile'),
	'Email' => _('Email'),
	'Register an account' => _('Register an account'),
	'Forgot password' => _('Forgot password'),
	'if' => array(
		'enable_forgot' => TRUE
	)
);

$content = tpl_apply('page_register', $tpl);

echo themes_apply($content);

?>