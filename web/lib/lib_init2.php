<?php
defined('_SECURE_') or die('Forbidden');

// this file loaded after plugins

$menutab_administration = $core_config['menutab']['administration'];
if (isadmin()) {
	// administrator menus
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_inbox&op=all_inbox", _('All inbox'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_incoming&op=all_incoming", _('All incoming SMS'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=all_outgoing&op=all_outgoing", _('All outgoing SMS'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=user_mgmnt&op=user_list", _('Manage user'));
	$arr_menu[$menutab_administration][] = array("index.php?app=menu&inc=main_config&op=main_config", _('Main configuration'));
	//ksort($arr_menu[$menutab_administration]);
}

// load menus into core_config
$core_config['menu'] = $arr_menu;

// load plugin's config into core_config
$core_config['plugins'] = $plugin;

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();

?>