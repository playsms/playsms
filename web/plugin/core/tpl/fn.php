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

/**
 * Template string manipulation
 * @param  string $content Content
 * @param  string $key     Template key
 * @param  string $val     Template value
 * @return string          Manipulated content
 */
function _tpl_set_string($content, $key, $val) {
	$content = str_replace('{' . $key . '}', $val, $content);
	return $content;
}

/**
 * Template loop manipulation
 * @param  string $content Content
 * @param  string $key     Template key
 * @param  string $val     Template value
 * @return string          Manipulated content
 */
function _tpl_set_array($content, $key, $val) {
	preg_match("/<loop\." . $key . ">(.*?)<\/loop\." . $key . ">/s", $content, $l);
	
	$loop = $l[1];
	foreach ($val as $v) {
		$loop_replaced = $loop;
		foreach ($v as $x => $y) {
			$loop_replaced = str_replace('{' . $key . '.' . $x . '}', $y, $loop_replaced);
		}
		$loop_content.= $loop_replaced;
	}
	
	$content = preg_replace("/<loop\." . $key . ">(.*?)<\/loop\." . $key . ">/s", $loop_content, $content);
	$content = str_replace("<loop." . $key . ">", '', $content);
	$content = str_replace("</loop." . $key . ">", '', $content);
	
	return $content;
}

/**
 * Template boolean manipulation
 * @param  string $content Content
 * @param  string $key     Template key
 * @param  string $val     Template value
 * @return string          Manipulated content
 */
function _tpl_set_bool($content, $key, $val) {
	if ($key && !$val) {
		$content = preg_replace("/<if\." . $key . ">(.*?)<\/if\." . $key . ">/s", '', $content);
	}
	$content = str_replace("<if." . $key . ">", '', $content);
	$content = str_replace("</if." . $key . ">", '', $content);
	
	return $content;
}

/**
 * Actual template apply
 * @param  string $fn  Template filename
 * @param  array  $tpl Template data
 * @return string      Manipulated content
 */
function _tpl_apply($fn, $tpl) {
	$content = trim(file_get_contents($fn));
	
	if ($content && is_array($tpl)) {
		
		if (isset($tpl['if'])) {
			foreach ($tpl['if'] as $key => $val) {
				$content = _tpl_set_bool($content, $key, $val);
			}
			unset($tpl['if']);
		}
		
		if (isset($tpl['loop'])) {
			foreach ($tpl['loop'] as $key => $val) {
				$content = _tpl_set_array($content, $key, $val);
			}
			unset($tpl['loop']);
		}
		
		if (isset($tpl['var'])) {
			foreach ($tpl['var'] as $key => $val) {
				$content = _tpl_set_string($content, $key, $val);
			}
		}
	}
	
	$content = preg_replace("/<if\..*?>(.*?)<\/if\..*?>/s", '', $content);
	$content = preg_replace("/<loop\..*?>(.*?)<\/loop\..*?>/s", '', $content);
	
	return $content;
}

/**
 * Sanitize template name
 * @param  string $name Template name
 * @return string       Sanitized template name
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
 * @param  array  $tpl Template array
 * @return string      Manipulated content
 */
function tpl_apply($tpl) {
	$content = '';
	$continue = FALSE;
	
	if (is_array($tpl)) {
		if ($tpl_name = _tpl_name_sanitize($tpl['name'])) {
			$continue = TRUE;
		}
	}
	
	if ($continue) {
		
		// inject anti-CSRF hidden field
		$tpl['var']['CSRF_FORM'] = _CSRF_FORM_;
		
		// check from active plugin
		$c_inc = explode('_', _INC_);
		$plugin_category = $c_inc[0];
		$plugin_name = str_replace($plugin_category . '_', '', _INC_);
		$fn = _APPS_PATH_PLUG_ . '/' . $plugin_category . '/' . $plugin_name . '/templates/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl);
			return $content;
		}
		
		// check from active template
		$themes = core_themes_get();
		$fn = _APPS_PATH_THEMES_ . '/' . $themes . '/templates/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl);
			return $content;
		}
		
		// check from common place on themes
		$fn = _APPS_PATH_TPL_ . '/' . $tpl_name . '.html';
		if (file_exists($fn)) {
			$content = _tpl_apply($fn, $tpl);
		}
	}
	
	return $content;
}
