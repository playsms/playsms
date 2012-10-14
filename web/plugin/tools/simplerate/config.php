<?php
defined('_SECURE_') or die('Forbidden');

if (isadmin()) {
	$menutab_tools = $core_config['menutab']['tools'];
	$arr_menu[$menutab_tools][] = array("index.php?app=menu&inc=tools_simplerate&op=simplerate_list", _('Manage SMS rate'));
}
?>