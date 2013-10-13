<?php

function tpl_apply_core($fn, $tpl) {
	$content = trim(file_get_contents($fn));
	if ($content && is_array($tpl)) {
		foreach ($tpl as $key => $val) {
			$content = str_replace('{'.$key.'}', $val, $content);
		}
	}
	return $content;
}

function tpl_apply($tpl_name, $tpl) {
	$tpl_name = q_sanitize($tpl_name);

	// check from active plugin
	$inc = explode('_', _INC_);
	$plugin_category = $inc[0];
	$plugin_name = str_replace($plugin_category.'_', '', _INC_);
	$fn = _APPS_PATH_PLUG_.'/'.$plugin_category.'/'.$plugin_name.'/templates/'.$tpl_name.'.tpl';
	if (file_exists($fn)) {
		$content = tpl_apply_core($fn, $tpl);
		return $content;
	}

	// check from active template
	$themes = themes_get();
	$fn = _APPS_PATH_THEMES_.'/'.$themes.'/templates/'.$tpl_name.'.tpl';
	if (file_exists($fn)) {
		$content = tpl_apply_core($fn, $tpl);
		return $content;
	}

	// check from common place on themes
	$fn = _APPS_PATH_TPL_.'/'.$tpl_name .'.tpl';
	if (file_exists($fn)) {
		$content = tpl_apply_core($fn, $tpl);
	}

	return $content;
}

?>