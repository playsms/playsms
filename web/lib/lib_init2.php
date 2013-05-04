<?php
defined('_SECURE_') or die('Forbidden');

// this file loaded after plugins

$menutab_administration = $core_config['menutab']['administration'];
if (isadmin()) {
	// administrator menus
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_inbox&op=all_inbox", _('All inbox'));
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_incoming&op=all_incoming", _('All incoming SMS'));
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_outgoing&op=all_outgoing", _('All outgoing SMS'));
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=user_mgmnt&op=user_list", _('Manage user'));
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=main_config&op=main_config", _('Main configuration'));
	//ksort($menu_config[$menutab_administration]);
}

// load menus into core_config
$core_config['menu'] = $menu_config;

// load plugin's config into core_config
$core_config['plugins'] = $plugin;

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();
//print_r($menu_config); die();

?>