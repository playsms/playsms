<?php
defined('_SECURE_') or die('Forbidden');

empty($tpl);
$tpl = array(
	'HTTP_PATH_BASE' => $http_path['base'],
	'WEB_TITLE' => $web_title,
	'ERROR' => $error_content,
	'Username' => _('Username'),
	'Email' => _('Email'),
	'Recover password' => _('Recover password'),
	'Register an account' => _('Register an account'),
	'if' => array(
		'enable_register' => TRUE
	)
);

$content = tpl_apply('page_forgot', $tpl);

echo themes_apply($content);

?>