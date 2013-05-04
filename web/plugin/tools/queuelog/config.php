<?php
defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab = $core_config['menutab']['administration'];
} else {
	$menutab = $core_config['menutab']['tools'];
}
$menu_config[$menutab][] = array("index.php?app=menu&inc=tools_queuelog&op=queuelog_list", _('View SMS queue'));
?>