<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$fn = _APPS_PATH_THEMES_.'/'.core_themes_get().'/page_welcome.php';

if (file_exists($fn)) {
	include $fn;
} else {
	unset($tpl);
	$tpl = array(
		'name' => 'page_welcome',
		'var' => array(
			'Welcome to playSMS' => _('Welcome to playSMS'),
			'version' => _('version'),
			'VERSION' => $core_config['version']
		)
	);
	$tpl['var'][$doc . '_ACTIVE'] = 'class=active';
	echo tpl_apply($tpl);
}
?>