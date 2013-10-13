<?php

function tpl_apply($fn, $tpl) {
	$content = trim(file_get_contents($fn));
	if ($content && is_array($tpl)) {
		foreach ($tpl as $key => $val) {
			$content = str_replace('{'.$key.'}', $val, $content);
		}
	}
	return $content;
}

function tpl_apply_common($tpl_name, $tpl) {
	$fn = APPS_PATH_TPL . '/' . $tpl_name . '.tpl';
	$content = tpl_apply($fn, $tpl);
	return $content;
}

?>