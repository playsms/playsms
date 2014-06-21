<?php
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
};

$content.= "
	<h2>" . _('Manage plugin') . "</h2>
	<ul class='nav nav-tabs nav-justified' id='playsms-tab'>
		<li class=active><a href='#tabs-core' data-toggle=tab>" . _('Core') . "</a></li>
		<li><a href='#tabs-feature' data-toggle=tab>" . _('Features') . "</a></li>
		<li><a href='#tabs-gateway' data-toggle=tab>" . _('Gateways') . "</a></li>
		<li><a href='#tabs-theme' data-toggle=tab>" . _('Themes') . "</a></li>
		<li><a href='#tabs-language' data-toggle=tab>" . _('Languages') . "</a></li>
	</ul>
	<div class=tab-content>
		<div id='tabs-core' class='tab-pane fade in active'>
			" . pluginmanager_display('core') . "
		</div>
		<div id='tabs-feature' class='tab-pane fade'>
			" . pluginmanager_display('feature') . "
		</div>
		<div id='tabs-gateway' class='tab-pane fade'>
			" . pluginmanager_display('gateway') . "
		</div>
		<div id='tabs-theme' class='tab-pane fade'>
			" . pluginmanager_display('themes') . "
		</div>
		<div id='tabs-language' class='tab-pane fade'>
			" . pluginmanager_display('language') . "
		</div>
	</div>
	<script type=\"text/javascript\" src=\"".$core_config['http_path']['plug']."/themes/common/jscss/jquery.cookie.js\"></script>
	<script type=\"text/javascript\">
	$(document).ready(function() {
		$('a[data-toggle=\"tab\"]').on('shown.bs.tab', function(e){
			//save the latest tab using a cookie:
			$.cookie('pluginmanager_last_tab', $(e.target).attr('href'));
		});
	
		//activate latest tab, if it exists:
		var lastTab = $.cookie('pluginmanager_last_tab');
		if (lastTab) {
			$('ul.nav-tabs').children().removeClass('active');
			$('a[href='+ lastTab +']').parents('li:first').addClass('active');
			$('div.tab-content').children().removeClass('in active');
			$(lastTab).addClass('in active');
		}
	});
	</script>
	";

_p($content);
