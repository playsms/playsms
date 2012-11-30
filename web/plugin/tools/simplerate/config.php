<?php
defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab = $core_config['menutab']['administration'];
	$arr_menu[$menutab][] = array("index.php?app=menu&inc=tools_simplerate&op=simplerate_list", _('Manage SMS rate'));
}
?>