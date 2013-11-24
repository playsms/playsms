<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

if (!isadmin()) { forcenoaccess (); };

$content .= "
	<h2>" . _ ( 'Manage plugin' ) . "</h2>
	<ul class='nav nav-pills nav-justified'>
		<li><a href='#tabs-feature'>" . _ ( 'Features' ) . "</a></li>
		<li><a href='#tabs-gateway'>" . _ ( 'Gateways' ) . "</a></li>
		<li><a href='#tabs-theme'>" . _ ( 'Themes' ) . "</a></li>
		<li><a href='#tabs-tools'>" . _ ( 'Tools' ) . "</a></li>
		<li><a href='#tabs-language'>" . _ ( 'Languages' ) . "</a></li>
	</ul>
	<div id='tabs-feature'>
		" . pluginmanager_display ( 'feature' ) . "
	</div>
	<div id='tabs-gateway'>
		" . pluginmanager_display ( 'gateway' ) . "
	</div>
	<div id='tabs-theme'>
		" . pluginmanager_display ( 'themes' ) . "
	</div>
	<div id='tabs-tools'>
		" . pluginmanager_display ( 'tools' ) . "
	</div>
	<div id='tabs-language'>
		" . pluginmanager_display ( 'language' ) . "
	</div>
	";
echo $content;

?>