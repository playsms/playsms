<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
// insert to left menu array
$menutab_tools = $core_config['menu']['main_tab']['tools'];
$arr_menu[$menutab_tools][] = array("index.php?app=menu&inc=tools_sendfromfile&op=list", _('Send from file'));
?>