<?php
defined('_SECURE_') or die('Forbidden');

// error string
if ($_SESSION['error_string']) {
	$error_content = '<div class="error_string">'.$_SESSION['error_string'].'</div>';
}

unset($tpl);
$tpl = array(
	'name' => 'page_block',
	'var' => array(
		'ERROR' => $error_content,
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'Home' => _('Home')
	)
);

$content = tpl_apply($tpl);

_p(themes_apply($content));
