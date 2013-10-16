<?php

function _tpl_set_string($content, $key, $val) {
	$content = str_replace('{'.$key.'}', $val, $content);
	return $content;
}

function _tpl_set_array($content, $key, $val) {
	preg_match("/<loop\.".$key.">(.*?)<\/loop\.".$key.">/s", $content, $l);
	$loop = $l[1];
	foreach ($val as $v) {
		$loop_replaced = $loop;
		foreach ($v as $x => $y) {
			$loop_replaced = str_replace('{'.$key.'.'.$x.'}', $y, $loop_replaced);
		}
		$loop_content .= $loop_replaced;
	}
	$content = preg_replace("/<loop\.".$key.">(.*?)<\/loop\.".$key.">/s", $loop_content, $content);
	$content = str_replace("<loop.".$key.">", '', $content);
	$content = str_replace("</loop.".$key.">", '', $content);
	return $content;
}

function _tpl_set_bool($content, $key, $val) {
	if ($key && !$val) {
		$content = preg_replace("/<if\.".$key.">(.*?)<\/if\.".$key.">/s", '', $content);
	}
	$content = str_replace("<if.".$key.">", '', $content);
	$content = str_replace("</if.".$key.">", '', $content);
	return $content;
}

function _tpl_apply($fn, $tpl) {
	$content = trim(file_get_contents($fn));
	if ($content && is_array($tpl)) {
		if (isset($tpl['if'])) {
			foreach ($tpl['if'] as $key => $val) {
				$content = _tpl_set_bool($content, $key, $val);
			}
			$content = preg_replace("/<if\..*?>(.*?)<\/if\..*?>/s", '', $content);
			unset($tpl['if']);
		}
		if (isset($tpl['loop'])) {
			foreach ($tpl['loop'] as $key => $val) {
				$content = _tpl_set_array($content, $key, $val);
			}
			$content = preg_replace("/<loop\..*?>(.*?)<\/loop\..*?>/s", '', $content);
			unset($tpl['loop']);
		}
		foreach ($tpl as $key => $val) {
			$content = _tpl_set_string($content, $key, $val);
		}
	}
	$content = preg_replace("/{(.*?)}/s", '', $content);
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