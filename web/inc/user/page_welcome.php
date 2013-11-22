<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$fn = _APPS_PATH_THEMES_.'/'.themes_get().'/page_welcome.php';

$doc = strtoupper(trim($_REQUEST['doc']));
$doc = ( $doc ? $doc : 'README' );

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
			'WELCOME_CONTENT' => core_read_docs($apps_path['base'], $doc)
	    )
	);
	$tpl['var'][$doc . '_ACTIVE'] = 'class=active';
	echo tpl_apply($tpl);
}
?>