<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

if (!auth_isadmin()) { auth_block (); };

$content .= "
	<h2>" . _ ( 'Manage plugin' ) . "</h2>
	<ul class='nav nav-tabs nav-justified'>
		<li class=active><a href='#tabs-feature' data-toggle=tab>" . _ ( 'Features' ) . "</a></li>
		<li><a href='#tabs-gateway' data-toggle=tab>" . _ ( 'Gateways' ) . "</a></li>
		<li><a href='#tabs-theme' data-toggle=tab>" . _ ( 'Themes' ) . "</a></li>
		<li><a href='#tabs-tools' data-toggle=tab>" . _ ( 'Tools' ) . "</a></li>
		<li><a href='#tabs-language' data-toggle=tab>" . _ ( 'Languages' ) . "</a></li>
	</ul>
	<div class=tab-content>
		<div id='tabs-feature' class='tab-pane fade in active'>
			" . pluginmanager_display ( 'feature' ) . "
		</div>
		<div id='tabs-gateway' class='tab-pane fade'>
			" . pluginmanager_display ( 'gateway' ) . "
		</div>
		<div id='tabs-theme' class='tab-pane fade'>
			" . pluginmanager_display ( 'themes' ) . "
		</div>
		<div id='tabs-tools' class='tab-pane fade'>
			" . pluginmanager_display ( 'tools' ) . "
		</div>
		<div id='tabs-language' class='tab-pane fade'>
			" . pluginmanager_display ( 'language' ) . "
		</div>
	</div>";
echo $content;

?>