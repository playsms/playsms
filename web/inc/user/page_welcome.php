<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$t['HTTP_PATH_THEMES'] = _HTTP_PATH_THEMES_;

$t['Welcome to playSMS'] = _('Welcome to playSMS');
$t['About playSMS'] = _('About playSMS');
$t['Changelog'] = _('Changelog');
$t['F.A.Q'] = _('F.A.Q');
$t['License'] = _('License');
$t['Webservices'] = _('Webservices');

$t['READ_README'] = core_read_docs($apps_path['base'], 'README');
$t['READ_CHANGELOG'] = core_read_docs($apps_path['base'], 'CHANGELOG');
$t['READ_FAQ'] = core_read_docs($apps_path['base'], 'FAQ');
$t['READ_LICENSE'] = core_read_docs($apps_path['base'], 'LICENSE');
$t['READ_WEBSERVICES'] = core_read_docs($apps_path['base'], 'WEBSERVICES');

echo tpl_apply('page_welcome', $t);

?>