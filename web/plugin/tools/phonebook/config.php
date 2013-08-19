<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php

$phonebook_row_limit = 20000;

$menutab_tools = $core_config['menutab']['my_account'];
$menu_config[$menutab_tools][] = array("index.php?app=menu&inc=tools_phonebook&op=phonebook_list", _('Phonebook'));

?>