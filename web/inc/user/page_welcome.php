<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$content = "
	<script src='" . $core_config['http_path']['themes'] . "/common/jscss/jquery.easytabs.js' type='text/javascript'></script>
	<script type='text/javascript'>
		$(document).ready( function() {
		$('#tab-container').easytabs();
	});
	</script>

	<h2>"._('Welcome to playSMS')."</h2>
	<div id='tab-container' class='tab-container'>
		<ul class='tabs'>
			<li class='tab'><a href='#tabs-about'>" . _ ( 'About playSMS' ) . "</a></li>
			<li class='tab'><a href='#tabs-changelog'>" . _ ( 'Changelog' ) . "</a></li>
			<li class='tab'><a href='#tabs-faq'>" . _ ( 'F.A.Q' ) . "</a></li>
			<li class='tab'><a href='#tabs-license'>" . _ ( 'License' ) . "</a></li>
			<li class='tab'><a href='#tabs-webservices'>" . _ ( 'Webservices' ) . "</a></li>
		</ul>
		<div id='tabs-about'>
			" . core_read_docs($apps_path['base'], 'README') . "
		</div>
		<div id='tabs-changelog'>
			" . core_read_docs($apps_path['base'], 'CHANGELOG') . "
		</div>
		<div id='tabs-faq'>
			" . core_read_docs($apps_path['base'], 'FAQ') . "
		</div>
		<div id='tabs-license'>
			" . core_read_docs($apps_path['base'], 'LICENSE') . "
		</div>
		<div id='tabs-webservices'>
			" . core_read_docs($apps_path['base'], 'WEBSERVICES') . "
		</div>			
	</div>";

echo $content;

?>