<?php

function _tpl_apply($fn, $tpl) {
	$content = trim(file_get_contents($fn));
	if ($content && is_array($tpl)) {
		foreach ($tpl as $key => $val) {
			if (is_array($val)) {
				unset($loop_content);
				preg_match("/<loop\ $key>(.*?)<\/loop>/s", $content, $l);
				$loop = $l[1];
				foreach ($val as $v) {
					$loop_replaced = $loop;
					foreach ($v as $x => $y) {
						$loop_replaced = str_replace('{'.$key.'.'.$x.'}', $y, $loop_replaced);
					}
					$loop_content .= $loop_replaced;
				}
				$content = preg_replace("/<loop\ $key>(.*?)<\/loop>/s", $loop_content, $content);
			} else {
				$content = str_replace('{'.$key.'}', $val, $content);
			}
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
		$content = _tpl_apply($fn, $tpl);
		return $content;
	}

	// check from active template
	$themes = themes_get();
	$fn = _APPS_PATH_THEMES_.'/'.$themes.'/templates/'.$tpl_name.'.tpl';
	if (file_exists($fn)) {
		$content = _tpl_apply($fn, $tpl);
		return $content;
	}

	// check from common place on themes
	$fn = _APPS_PATH_TPL_.'/'.$tpl_name .'.tpl';
	if (file_exists($fn)) {
		$content = _tpl_apply($fn, $tpl);
	}

	return $content;
}

?>