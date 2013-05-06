<?php

function gateway_get() {
	global $core_config;
	$ret = $core_config['main']['cfg_gateway_module'];
	return $ret;
}

?>