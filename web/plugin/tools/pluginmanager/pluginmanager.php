<?php

defined('_SECURE_') or die('Forbidden');

if (!isadmin()) {
	forcenoaccess();
};

$content = "
	<link href='" . $http_path['plug'] . "/tools/pluginmanager/jscss/style.css' rel='stylesheet' />
	<script src='" . $http_path['plug'] . "/tools/pluginmanager/jscss/jquery.js' type='text/javascript'></script>
	<script src='" . $http_path['plug'] . "/tools/pluginmanager/jscss/jquery.hashchange.js' type='text/javascript'></script>
	<script src='" . $http_path['plug'] . "/tools/pluginmanager/jscss/jquery.easytabs.js' type='text/javascript'></script>
	<script type='text/javascript'>
		$(document).ready( function() {
		$('#tab-container').easytabs();
	});
	</script>";

$content .= "
	<h2>"._('Manage plugin')."</h2>
	<div id='tab-container' class='tab-container'>
		<ul class='etabs'>
			<li class='tab'><a href='#tabs-feature'>" . _('Features') . "</a></li>
			<li class='tab'><a href='#tabs-gateway'>" . _('Gateways') . "</a></li>
			<li class='tab'><a href='#tabs-theme'>" . _('Themes') . "</a></li>
			<li class='tab'><a href='#tabs-tool'>" . _('Tools') . "</a></li>
			<li class='tab'><a href='#tabs-lang'>" . _('Languages') . "</a></li>
		</ul>
	<div id='tabs-feature'>
		".pluginmanager_display('feature')."<br />
	</div>
	<div id='tabs-gateway'>
		".pluginmanager_display('gateway')."<br />
	</div>
	<div id='tabs-theme'>
		".pluginmanager_display('themes')."<br />
	</div>
	<div id='tabs-tool'>
		".pluginmanager_display('tools')."<br />
	</div>
	<div id='tabs-lang'>
		".pluginmanager_display('language')."<br />
	</div>";

echo $content;

?>