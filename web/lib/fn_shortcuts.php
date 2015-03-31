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

// lib/fn_core.php


/**
 * Shortcut to core_print() for printing output to display
 *
 * @return string
 */
function _p($content) {
	return core_print($content);
}

// lib/fn_themes.php
function _a($url, $title = '', $css_class = '', $css_id = '') {
	return themes_link($url, $title, $css_class, $css_id);
}

function _u($url) {
	return themes_url($url);
}

function _back($url) {
	return themes_button_back($url);
}

function _button($url, $title, $css_class = '', $css_id = '') {
	return themes_button($url, $title, $css_class, $css_id);
}

function _hint($text) {
	return themes_hint($text);
}

function _mandatory($text) {
	return themes_mandatory($text);
}

/**
 * Generate options for select HTML tag
 * Shortcut to themes_select_options()
 *
 * @param array $options
 *        Select options
 * @param string $selected
 *        Selected option
 * @return string Options for select HTML tag
 */
function _options($options = array(), $selected = '') {
	return themes_select_options($options, $selected);
}

/**
 * Generate select HTML tag
 * Shortcut to themes_select()
 *
 * @param string $name
 *        Tag name
 * @param array $options
 *        Select options
 * @param string $selected
 *        Selected option
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string Select HTML tag
 */
function _select($name, $options = array(), $selected = '', $tag_params = array(), $css_id = '', $css_class = '') {
	return themes_select($name, $options, $selected, $tag_params, $css_id, $css_class);
}

/**
 * Generate select HTML tag for yes-no or enabled-disabled type of options
 * Shortcut to themes_select_yesno()
 *
 * @param string $name
 *        Tag name
 * @param boolean $selected
 *        TRUE if yes/enabled
 * @param string $yes
 *        'Yes' or 'Enabled' option
 * @param string $no
 *        'No' or 'Disabled' option
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string Select HTML tag
 */
function _yesno($name, $selected = '', $yes = '', $no = '', $tag_params = array(), $css_id = '', $css_class = '') {
	return themes_select_yesno($name, $selected, $yes, $no, $tag_params, $css_id, $css_class);
}

/**
 * Display error string from function parameter or session
 * Shortcut to themes_dialog()
 *
 * @param array $content
 *        Array of contents of dialog, format: $content['dialog'][<Type_of_dialog>]
 *        Type of dialog: default, info, primary, success, warning, danger
 * @param string $title
 *        Dialog title
 * @return string HTML string of error strings
 */
function _dialog($content = array(), $title = '') {
	return themes_dialog($content, $title);
}

/**
 * Display error string from function parameter or session
 * Shortcut to themes_dialog()
 * Compatibilty with previous playSMS versions, will be removed on 1.0
 *
 * @param array $content
 *        Array of contents of dialog, format: $content['dialog'][<Type_of_dialog>]
 *        Type of dialog: default, info, primary, success, warning, danger
 * @param string $title
 *        Dialog title
 * @return string HTML string of error strings
 */
function _err_display($content = array(), $title = '') {
	return _dialog($content, $title);
}

/**
 * Generate HTML input tag
 * Shortcut to themes_input()
 *
 * @param string $type
 *        Input type
 * @param string $name
 *        Input name
 * @param string $value
 *        Input default value
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string HTML input tag
 */
function _input($type = 'text', $name = '', $value = '', $tag_params = array(), $css_id = '', $css_class = '') {
	return themes_input($type, $name, $value, $tag_params, $css_id, $css_class);
}

// lib/fn_logger.php
function _log($log, $level, $label) {
	return logger_print($log, $level, $label);
}

/**
 * Popup compose message form
 * Shortcut to themes_popup_sendsms()
 *
 * @param string $to
 *        Default destination
 * @param string $message
 *        Default or previous message
 * @param string $return_url
 *        If empty this would be $_SERVER['REQUEST_URI']
 * @param string $button_icon
 *        If empty this would be a reply icon
 * @return string Javascript PopupSendsms()
 */
function _sendsms($to = "", $message = "", $return_url = "", $button_icon = "") {
	return themes_popup_sendsms($to, $message, $return_url, $button_icon);
}

/**
 * Get last submitted $_POST data
 * Shortcut to core_last_post_get()
 *
 * @param string $key        
 * @return mixed
 */
function _lastpost($key = '') {
	return core_last_post_get($key);
}
