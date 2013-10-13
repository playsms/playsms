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
	$themes = themes_get();
	$fn = APPS_PATH_THEMES.'/'.$themes.'/templates/'.q_sanitize($tpl_name).'.tpl';
	if (file_exists($fn)) {
		$content = tpl_apply_core($fn, $tpl);
		return $content;
	}
	$fn = APPS_PATH_TPL.'/'.$tpl_name .'.tpl';
	if (file_exists($fn)) {
		$content = tpl_apply_core($fn, $tpl);
	}
	return $content;
}

?>