<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php

function playsmslog_view($nline=1000) {
	global $core_config;
	$content = @shell_exec('tail -n '.$nline.' '.$core_config['apps_path']['logs'].'/playsms.log');
	return $content;
}

?>