<?php
defined('_SECURE_') or die('Forbidden');

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_phonebook&op=phonebook_list',
	_('Phonebook'),
	2
);

$phonebook_row_limit = 5000;

$phonebook_flag_sender[0] = $icon_config['user'];
$phonebook_flag_sender[1] = $icon_config['user_group'];
$phonebook_flag_sender[2] = $icon_config['user_all'];
;