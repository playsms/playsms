<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$fn = _APPS_PATH_THEMES_.'/'.themes_get().'/page_welcome.php';

if (file_exists($fn)) {
	include $fn;
} else {
	unset($tpl);
	$tpl = array(
		'name' => 'page_welcome',
		'var' => array(
			'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
			'Welcome to playSMS' => _('Welcome to playSMS'),
			'About playSMS' => _('About playSMS'),
			'Changelog' => _('Changelog'),
			'F.A.Q' => _('F.A.Q'),
			'License' => _('License'),
			'Webservices' => _('Webservices'),
			'READ_README' => core_read_docs($apps_path['base'], 'README'),
			'READ_CHANGELOG' => core_read_docs($apps_path['base'], 'CHANGELOG'),
			'READ_FAQ' => core_read_docs($apps_path['base'], 'FAQ'),
			'READ_LICENSE' => core_read_docs($apps_path['base'], 'LICENSE'),
			'READ_WEBSERVICES' => core_read_docs($apps_path['base'], 'WEBSERVICES'),	
		)
	);
	echo tpl_apply($tpl);
}
?>