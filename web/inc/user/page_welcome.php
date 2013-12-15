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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$fn = _APPS_PATH_THEMES_.'/'.core_themes_get().'/page_welcome.php';

if (file_exists($fn)) {
	include $fn;
} else {
	unset($tpl);
	$tpl = array(
		'name' => 'page_welcome',
		'var' => array(
			'Welcome to playSMS' => _('Welcome to playSMS'),
			'version' => _('version'),
			'VERSION' => $core_config['version']
		)
	);
	$tpl['var'][$doc . '_ACTIVE'] = 'class=active';
	echo tpl_apply($tpl);
}
