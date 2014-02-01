<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php

$phonebook_row_limit = 20000;

$menutab_tools = $core_config['menutab']['my_account'];
$menu_config[$menutab_tools][] = array("index.php?app=menu&inc=tools_phonebook&op=phonebook_list", _('Phonebook'));

$phonebook_flag_sender[0] = "<span class='playsms-icon glyphicon glyphicon-eye-close' alt='"._('Me only')."' title='"._('Me only')."'></span>";
$phonebook_flag_sender[1] = "<span class='playsms-icon glyphicon glyphicon-eye-open' alt='"._('Members')."' title='"._('Members')."'></span>";
$phonebook_flag_sender[2] = "<span class='playsms-icon glyphicon glyphicon-globe' alt='"._('Anyone')."' title='"._('Anyone')."'></span>";

