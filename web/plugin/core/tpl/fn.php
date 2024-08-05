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
 * Actual template apply
 *
 * @param string $fn template filename
 * @param array $tpl template data
 * @param array $injected injected variable names
 * @return string manipulated content
 */
function _tpl_apply($fn, $tpl, $injected = [])
{
	$t = new \Playsms\Tpl();

	$t->setConfig([
		'echo' => '_p',
		'dir_cache' => _APPS_PATH_STORAGE_ . '/plugin/core/tpl'
	]);

	$t->setTemplate($fn);

	if (isset($tpl['vars']) && $tpl['vars']) {
		$t->setVars($tpl['vars']);
	}

	if (isset($tpl['ifs']) && $tpl['ifs']) {
		$t->setIfs($tpl['ifs']);
	}

	if (isset($tpl['loops']) && $tpl['loops']) {
		$t->setLoops($tpl['loops']);
	}

	$t->setInjects($injected);
	$t->compile();

	return $t->getCompiled();
}

/**
 * Apply template
 *
 * @param array $tpl template array
 * @param array $injected injected variable names
 * @return string manipulated content
 */
function tpl_apply($tpl, $injected = [])
{
	$content = '';

	if (!(is_array($tpl) && isset($tpl['name']) && $tpl['name'] && $tpl_name = core_sanitize_filename($tpl['name']))) {

		return $content;
	}

	// inject anti-CSRF hidden field
	$tpl['vars']['CSRF_FORM'] = _CSRF_FORM_;

	// inject global variables
	if (is_array($tpl['injects']) && isset($tpl['injects']) && $tpl['injects'] && !$injected) {
		$injected = $tpl['injects'];
	}

	// 1. check from active plugin based on current inc= query string
	$c_inc = explode('_', _INC_);
	$plugin_category = $c_inc[0];
	$plugin_name = str_replace($plugin_category . '_', '', _INC_);
	$fn = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/templates/' . $tpl_name . '.html';
	if (file_exists($fn)) {
		$content = _tpl_apply($fn, $tpl, $injected);

		return $content;
	}

	// 2. search all possible location on active plugin based on template name
	$c_plugin_name = explode('_', $tpl_name);
	$plugin_name = $c_plugin_name[0];
	$c_plugin_category = [
		'core',
		'feature',
		'gateway'
	];
	foreach ( $c_plugin_category as $plugin_category ) {
		$fn = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/templates/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl, $injected);

			return $content;
		}
	}

	// 3. check from active themes
	$themes = core_themes_get();
	$fn = _APPS_PATH_THEMES_ . '/' . $themes . '/templates/' . $tpl_name . '.html';
	if (file_exists($fn)) {
		$content = _tpl_apply($fn, $tpl, $injected);

		return $content;
	}

	// 4. check from common place on themes
	$fn = _APPS_PATH_TPL_ . '/' . $tpl_name . '.html';
	if (file_exists($fn)) {
		$content = _tpl_apply($fn, $tpl, $injected);

		return $content;
	}

	return $content;
}
