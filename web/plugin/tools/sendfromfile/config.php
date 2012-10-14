<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_tools = $core_config['menutab']['tools'];
$arr_menu[$menutab_tools][] = array("index.php?app=menu&inc=tools_sendfromfile&op=list", _('Send from file'));
?>