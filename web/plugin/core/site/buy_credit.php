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

$fn = _APPS_PATH_THEMES_.'/'.core_themes_get().'/welcome.php';

if (file_exists($fn)) {
	include $fn;
} else {

	$buy_credit_page_title = ( $core_config['main']['buy_credit_page_title'] ? $core_config['main']['buy_credit_page_title'] : _('Buy credit') );
	$buy_credit_page_content = ( $core_config['main']['buy_credit_page_content'] ? $core_config['main']['buy_credit_page_content'] : _('Go to manage site menu to edit this page') );

	list($buy_credit_page_title, $buy_credit_page_content) = core_display_html(array(
		$buy_credit_page_title,
		$buy_credit_page_content,
	));

	$tpl = array(
		'name' => 'site_buy_credit',
		'vars' => array(
			'BUY_CREDIT_PAGE_TITLE' => $buy_credit_page_title,
			'BUY_CREDIT_PAGE_CONTENT' => $buy_credit_page_content,
		),
		'injects' => array('user_config'),
	);
	$tpl['vars'][$doc . '_ACTIVE'] = 'class=active';
	_p(tpl_apply($tpl));
}
