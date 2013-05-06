<?php

function gateway_get() {
	global $core_config;
	$ret = $core_config['module']['gateway'];
	return $ret;
}

?>