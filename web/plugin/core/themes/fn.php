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

/**
 * Apply theme to content
 * 
 * @param string $content whole content
 * @return string
 */
function themes_apply($content = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_apply',
			[
				$content
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_apply',
			[
				$content
			]
		);
	}

	return $ret;
}

/**
 * Get submenus
 * 
 * @param string $content submenu content
 * @return string
 */
function themes_submenu($content = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_submenu',
			[
				$content
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_submenu',
			[
				$content
			]
		);
	}

	return $ret;
}

/**
 * Get menu tree
 * 
 * @param array $menus
 * @return string
 */
function themes_buildmenu($menus = [])
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_buildmenu',
			[
				$menus
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_buildmenu',
			[
				$menus
			]
		);
	}

	return $ret;
}

/**
 * Get navigation bar
 * 
 * @param int $num number of item per page
 * @param int $nav active nav bar
 * @param int $max_nav maximum visible page on a nav bar
 * @param string $url base URL
 * @param int $page active page
 * @return string
 */
function themes_navbar($num, $nav, $max_nav, $url, $page)
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_navbar',
			[
				$num,
				$nav,
				$max_nav,
				$url,
				$page
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_navbar',
			[
				$num,
				$nav,
				$max_nav,
				$url,
				$page
			]
		);
	}

	return $ret;
}

/**
 * Get navigation bar information
 * 
 * @param int $count row counts
 * @param string $url navigation form URL
 * @return array [form, limit, offset, top, nav, page, url)
 */
function themes_nav($count, $url = '')
{
	$ret = [];

	$lines_per_page = 50;
	$max_nav = 10;
	$num = ceil($count / $lines_per_page);
	$nav = _NAV_ ? _NAV_ : 1;
	$page = _PAGE_ ? _PAGE_ : 1;
	$url = trim($url) ? trim($url) : $_SERVER['REQUEST_URI'];

	$ret['form'] = themes_navbar($num, $nav, $max_nav, $url, $page);
	$ret['limit'] = (int) $lines_per_page;
	$ret['offset'] = (int) (($page - 1) * $lines_per_page);
	$ret['top'] = (int) (($count - ($lines_per_page * ($page - 1))) + 1);
	$ret['nav'] = (int) $nav;
	$ret['page'] = (int) $page;
	$ret['url'] = $url;

	$_SESSION['tmp']['themes_nav'] = $ret;

	return $ret;
}

/**
 * Get current navigation bar
 * 
 * @return string
 */
function themes_nav_session()
{
	return $_SESSION['tmp']['themes_nav'];
}

/**
 * Get search form
 * 
 * @param array $search_category search categories
 * @param string $url base URL
 * @param array $keyword_converter pair keywords and their modifier functions
 * @return array
 */
function themes_search($search_category = [], $url = '', $keyword_converter = [])
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_search',
			[
				$search_category,
				$url,
				$keyword_converter,
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_search',
			[
				$search_category,
				$url,
				$keyword_converter,
			]
		);
	}

	return $ret;
}

/**
 * Get active search form
 * 
 * @return array
 */
function themes_search_session()
{
	return $_SESSION['tmp']['themes_search'];
}

/**
 * Build link
 * 
 * @param string $url base URL
 * @param string $title link title
 * @param string $css_class link CSS class name
 * @param string $css_id link CSS ID
 * @return string
 */
function themes_link($url, $title = '', $css_class = '', $css_id = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_link',
			[
				$url,
				$title,
				$css_class,
				$css_id
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_link',
			[
				$url,
				$title,
				$css_class,
				$css_id
			]
		);
	}

	return $ret;
}

/**
 * Modify URL
 * 
 * @param string $url URL
 * @return string
 */
function themes_url($url)
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_url',
			[
				$url
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_url',
			[
				$url
			]
		);
	}

	return $ret;
}

/**
 * Build back button
 * 
 * @param string $url base URL
 * @return string
 */
function themes_button_back($url)
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_button_back',
			[
				$url
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_button_back',
			[
				$url
			]
		);
	}

	return $ret;
}

/**
 * Build button
 * 
 * @param string $url base URL
 * @param string $title button title
 * @param string $css_class button CSS class name
 * @param string $css_id button CSS ID
 * @return string
 */
function themes_button($url, $title, $css_class = '', $css_id = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_button',
			[
				$url,
				$title,
				$css_class,
				$css_id
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_button',
			[
				$url,
				$title,
				$css_class,
				$css_id
			]
		);
	}

	return $ret;
}

/**
 * Build hint label
 * 
 * @param string $text hint text
 * @return string
 */
function themes_hint($text)
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_hint',
			[
				$text
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_hint',
			[
				$text
			]
		);
	}

	return $ret;
}

/**
 * Build mandatory label
 * 
 * @param string $text mandatory text
 * @return string
 */
function themes_mandatory($text)
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_mandatory',
			[
				$text
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_mandatory',
			[
				$text
			]
		);
	}

	return $ret;
}

/**
 * Generate options for select HTML tag
 *
 * @param array $options select options
 * @param string $selected selected option
 * @return string options for select HTML tag
 */
function themes_select_options($options = [], $selected = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_options',
			[
				$options,
				$selected
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_options',
			[
				$options,
				$selected
			]
		);
	}

	return $ret;
}

/**
 * Generate select HTML tag
 *
 * @param string $name select tag name
 * @param array $options select options
 * @param string $selected selected option
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string select HTML tag
 */
function themes_select($name, $options = [], $selected = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select',
			[
				$name,
				$options,
				$selected,
				$tag_params,
				$css_id,
				$css_class,
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select',
			[
				$name,
				$options,
				$selected,
				$tag_params,
				$css_id,
				$css_class,
			]
		);
	}

	return $ret;
}

/**
 * Generate select HTML tag for yes-no or enabled-disabled type of options
 *
 * @param string $name tag name
 * @param bool $selected true if yes/enabled
 * @param string $yes 'Yes' or 'Enabled' option
 * @param string $no 'No' or 'Disabled' option
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string select HTML tag
 */
function themes_select_yesno($name, $selected = false, $yes = '', $no = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_yesno',
			[
				$name,
				$selected,
				$yes,
				$no,
				$tag_params,
				$css_id,
				$css_class,
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_yesno',
			[
				$name,
				$selected,
				$yes,
				$no,
				$tag_params,
				$css_id,
				$css_class,
			]
		);
	}

	return $ret;
}

/**
 * Generate HTML input tag
 *
 * @param string $type input type
 * @param string $name input name
 * @param string $value input default value
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string HTML input tag
 */
function themes_input($type = 'text', $name = '', $value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_input',
			[
				$type,
				$name,
				$value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_input',
			[
				$type,
				$name,
				$value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	return $ret;
}

/**
 * Popup compose message form
 *
 * @param string $to default destination
 * @param string $message default or previous message
 * @param string $return_url if empty this would be $_SERVER['REQUEST_URI']
 * @param string $button_icon if empty this would be a reply icon
 * @return string Javascript PopupSendsms()
 */
function themes_popup_sendsms($to = '', $message = '', $return_url = '', $button_icon = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_popup_sendsms',
			[
				$to,
				$message,
				$return_url,
				$button_icon
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_popup_sendsms',
			[
				$to,
				$message,
				$return_url,
				$button_icon
			]
		);
	}

	return $ret;
}
/**
 * Display error string from function parameter or session
 *
 * @param array $content
 *        Array of contents of dialog, format: $content['dialog'][<Type_of_dialog>]
 *        Type of dialog: default, info, primary, success, warning, danger
 * @param string $title dialog title
 * @return string HTML string of error strings
 */
function themes_dialog($content = [], $title = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_dialog',
			[
				$content,
				$title
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_dialog',
			[
				$content,
				$title
			]
		);
	}

	return $ret;
}

/**
 * Generate select single user
 * 
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function themes_select_users_single($select_field_name, $selected_value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_users_single',
			[
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_users_single',
			[
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	return $ret;
}

/**
 * Generate select multiple users
 * 
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function themes_select_users_multi($select_field_name, $selected_value = [], $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_users_multi',
			[
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_users_multi',
			[
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	return $ret;
}

/**
 * Generate select user with user level
 * 
 * @param int $status user level
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function themes_select_account_level_single($status, $select_field_name, $selected_value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	$status = empty($status) || (int) $status > 3 || (int) $status < 0 ? 2 : (int) $status;

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_account_level_single',
			[
				$status,
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_account_level_single',
			[
				$status,
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	return $ret;
}

/**
 * Generate select multiple users with user level
 * 
 * @param int $status user level
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function themes_select_account_level_multi($status, $select_field_name, $selected_value = [], $tag_params = [], $css_id = '', $css_class = '')
{
	$ret = '';

	$status = empty($status) || (int) $status > 3 || (int) $status < 0 ? 2 : (int) $status;

	if (core_themes_get()) {
		$ret = core_hook(
			core_themes_get(),
			'themes_select_account_level_multi',
			[
				$status,
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	if (!$ret) {
		$ret = core_hook(
			'common',
			'themes_select_account_level_multi',
			[
				$status,
				$select_field_name,
				$selected_value,
				$tag_params,
				$css_id,
				$css_class
			]
		);
	}

	return $ret;
}
