<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

$content .= "
	<h2 class=page-header-title>" . _('Manage plugin') . "</h2>
	<div class='playsms-actions-box'>
		<ul class='nav nav-tabs nav-justified' id='playsms-tab-pluginmanager'>
			<li class='nav-item'><a class='nav-link' href='#tabs-core' data-toggle=tab>" . _('Core') . "</a></li>
			<li class='nav-item'><a class='nav-link' href='#tabs-feature' data-toggle=tab>" . _('Features') . "</a></li>
			<li class='nav-item'><a class='nav-link' href='#tabs-gateway' data-toggle=tab>" . _('Gateways') . "</a></li>
			<li class='nav-item'><a class='nav-link' href='#tabs-theme' data-toggle=tab>" . _('Themes') . "</a></li>
			<li class='nav-item'><a class='nav-link' href='#tabs-language' data-toggle=tab>" . _('Languages') . "</a></li>
		</ul>
	</div>
	<div class=tab-content>
		<div id='tabs-core' class='tab-pane fade'>
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
	</div>";

_p($content);
