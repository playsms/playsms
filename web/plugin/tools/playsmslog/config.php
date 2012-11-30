<?php
defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$arr_menu[$menutab][] = array("index.php?app=menu&inc=tools_playsmslog&op=playsmslog_list", _('View log'));
}
?>