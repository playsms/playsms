<?php
defined('_SECURE_') or die('Forbidden');

function playsmslog_view($nline=1000) {
	global $core_config;
	$content = @shell_exec('tail -n '.$nline.' '.$core_config['apps_path']['logs'].'/playsms.log');
	return $content;
}

?>
