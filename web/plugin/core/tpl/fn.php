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
 * @param  string $fn       Template filename
 * @param  array  $tpl      Template data
 * @param  array  $injected Injected variable names
 * @return string           Manipulated content
 */
function _tpl_apply($fn, $tpl, $injected=array()) {
	foreach ($injected as $global_var) {
		global ${$global_var};
	}

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

		if (isset($tpl['inject'])) {
			extract($tpl['inject']);
		}
	}
	
	$content = preg_replace("/<if\..*?>(.*?)<\/if\..*?>/s", '', $content);
	$content = preg_replace("/<loop\..*?>(.*?)<\/loop\..*?>/s", '', $content);
	
	$pattern = "\{\{(.*?)\}\}";
	preg_match_all("/".$pattern."/", $content, $matches, PREG_SET_ORDER);
	foreach ($matches as $block) {
		$chunk = $block[0];
		$codes = '<?php '.trim($block[1]).' ?>';
		$content = str_replace($chunk, $codes, $content);
	}

	// attempt to create cache file for this template in storage directory
	$cache_file = _PID_.'_'.md5($fn.mktime()).'.compiled';
	$cache = _APPS_PATH_STORAGE_.'/plugin/core/tpl/'.$cache_file;
	$fd = @fopen($cache, 'w+');
	@fwrite($fd, $content);
	@fclose($fd);

	// when failed, try to create in /tmp
	if (! file_exists($cache)) {
		$cache = '/tmp/'.$cache_file;
		$fd = @fopen($cache, 'w+');
		@fwrite($fd, $content);
		@fclose($fd);
		_log('WARNING: using /tmp to store template cache file. tpl:'.$tpl['name'], 3, '_tpl_apply');
	}

	// if template cache file created then include it, else use eval()
	if (file_exists($cache)) {
		ob_start();
		include $cache;
		$content = ob_get_contents();
		ob_end_clean();
		@unlink($cache);
	} else {
		ob_start();
		eval('?>'.$content.'<?php ');
		$content = ob_get_contents();
		ob_end_clean();
		_log('WARNING: cannot create template cache file. tpl:'.$tpl['name'], 3, '_tpl_apply');
	}

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
 * @param  array  $tpl      Template array
 * @param  array  $injected Injected variable names
 * @return string           Manipulated content
 */
function tpl_apply($tpl, $injected=array()) {
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
			$content = _tpl_apply($fn, $tpl, $injected);
			return $content;
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
	}
	
	return $content;
}
