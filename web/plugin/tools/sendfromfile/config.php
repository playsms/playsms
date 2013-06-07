<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab_tools = $core_config['menutab']['tools'];
$menu_config[$menutab_tools][] = array("index.php?app=menu&inc=tools_sendfromfile&op=list", _('Send from file'));

$sendfromfile_row_limit = 20000;
?>