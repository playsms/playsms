<?php
defined('_SECURE_') or die('Forbidden');

unset($tpl);
$tpl = array(
	'name' => 'page_noaccess',
	'var' => array(
		'ERROR' => $error_content,
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'Home' => _('Home')
	)
);

$content = tpl_apply($tpl);

echo themes_apply($content);

?>