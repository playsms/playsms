<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

if (!isadmin()) { forcenoaccess (); };

$content = "
	<link href='" . $core_config['http_path']['themes'] . "/common/jscss/pluginmanager.css' rel='stylesheet' />
	<script src='" . $core_config['http_path']['themes'] . "/common/jscss/jquery.js' type='text/javascript'></script>
	<script src='" . $core_config['http_path']['plug'] . "/tools/pluginmanager/jscss/jquery.hashchange.js' type='text/javascript'></script>
	<script src='" . $core_config['http_path']['plug'] . "/tools/pluginmanager/jscss/jquery.easytabs.js' type='text/javascript'></script>
	<script type='text/javascript'>
		$(document).ready( function() {
		$('#tab-container').easytabs();
	});
	</script>";

$content .= "
	<h2>" . _ ( 'Manage plugin' ) . "</h2>
	<div id='tab-container' class='tab-container'>
		<ul class='tabs'>
			<li class='tab'><a href='#tabs-feature'>" . _ ( 'Features' ) . "</a></li>
			<li class='tab'><a href='#tabs-gateway'>" . _ ( 'Gateways' ) . "</a></li>
			<li class='tab'><a href='#tabs-theme'>" . _ ( 'Themes' ) . "</a></li>
			<li class='tab'><a href='#tabs-tools'>" . _ ( 'Tools' ) . "</a></li>
			<li class='tab'><a href='#tabs-language'>" . _ ( 'Languages' ) . "</a></li>
		</ul>
		<div id='tabs-feature'>
			" . pluginmanager_display ( 'feature' ) . "<br />
		</div>
		<div id='tabs-gateway'>
			" . pluginmanager_display ( 'gateway' ) . "<br />
		</div>
		<div id='tabs-theme'>
			" . pluginmanager_display ( 'themes' ) . "<br />
		</div>
		<div id='tabs-tools'>
			" . pluginmanager_display ( 'tools' ) . "<br />
		</div>
		<div id='tabs-language'>
			" . pluginmanager_display ( 'language' ) . "<br />
		</div>
	</div>";

echo $content;

?>