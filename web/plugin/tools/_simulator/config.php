<?php
defined('_SECURE_') or die('Forbidden');

$menutab_tools = $core_config['menutab']['tools'];
$menu_config[$menutab_tools][] = array("index.php?app=menu&inc=tools_simulator&op=simulator_list", "Simulator");
?>