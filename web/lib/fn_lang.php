<?php

function lang_get() {
	global $core_config;
	$ret = $core_config['module']['language'];
	return $ret;
}

?>