<?php
defined('_SECURE_') or die('Forbidden');

empty($tpl);
$tpl = array(
	'ERROR' => $error_content,
	'HTTP_PATH_BASE' => $core_config['http_path']['base'],
	'Home' => _('Home'),
);

$content = tpl_apply('page_noaccess', $tpl);

echo themes_apply($content);

?>