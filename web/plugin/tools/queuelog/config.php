<?php
defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab = $core_config['menutab']['administration'];
} else {
	$menutab = $core_config['menutab']['tools'];
}
$arr_menu[$menutab][] = array("index.php?app=menu&inc=tools_queuelog&op=queuelog_list", _('View SMS queue'));
?>