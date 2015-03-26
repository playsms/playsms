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
 * @param string $fn
 *        	Template filename
 * @param array $tpl
 *        	Template data
 * @param array $injected
 *        	Injected variable names
 * @return string Manipulated content
 */
function _tpl_apply($fn, $tpl, $injected = array()) {
	$t = new \Playsms\Tpl();
	
	$t->setConfig(array(
		'echo' => '_p',
		'dir_cache' => _APPS_PATH_STORAGE_ . '/plugin/core/tpl' 
	));
	
	$t->setTemplate($fn);
	$t->setVars($tpl['vars'])->setIfs($tpl['ifs'])->setLoops($tpl['loops']);
	$t->setInjects($injected);
	$t->compile();
	
	return $t->getCompiled();
}

/**
 * Sanitize template name
 *
 * @param string $name
 *        	Template name
 * @return string Sanitized template name
 */
function _tpl_name_sanitize($name) {
	$name = str_replace('..', '', $name);
	$name = str_replace('|', '', $name);
	$name = str_replace('"', '', $name);
	$name = str_replace("'", '', $name);
	$name = str_replace("\\", '/', $name);
	
	return $name;
}

/**
 * Apply template
 * 
 * @param array $tpl
 *        	Template array
 * @param array $injected
 *        	Injected variable names
 * @return string Manipulated content
 */
function tpl_apply($tpl, $injected = array()) {
	$content = '';
	$continue = FALSE;
	
	if (is_array($tpl)) {
		if ($tpl_name = _tpl_name_sanitize($tpl['name'])) {
			$continue = TRUE;
		}
	}
	
	if ($continue) {
		
		// inject anti-CSRF hidden field
		$tpl['vars']['CSRF_FORM'] = _CSRF_FORM_;
		
		// inject global variables
		if (is_array($tpl['injects']) && !$injected) {
			$injected = $tpl['injects'];
		}
		
		// check from active template
		$themes = core_themes_get();
		$fn = _APPS_PATH_THEMES_ . '/' . $themes . '/templates/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl, $injected);
			return $content;
		}
		
		// check from common place on themes
		$fn = _APPS_PATH_TPL_ . '/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl, $injected);
		}
		// check from active plugin
		$c_inc = explode('_', _INC_);
		$plugin_category = $c_inc[0];
		$plugin_name = str_replace($plugin_category . '_', '', _INC_);
		$fn = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/templates/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl, $injected);
			return $content;
		}
	}
	
	return $content;
}
