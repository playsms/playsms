<?php 
error_reporting(0);

// inspired by http://www.bulksms.com/int/docs/eapi/status_reports/http_push/

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/orange/");
}



?>