<?php
defined('_SECURE_') or die('Forbidden');

function themes_get_menu_tree($menus='') {
	global $core_config;
	$arr_menu = $core_config['menu'];
	if ($menus) {
		$arr_menu = $menus;
	}
	$menu_tree = themes_buildmenu($arr_menu);
	return $menu_tree;
}

function themes_buildmenu($arr_menu) {
	global $core_config;
	$menu = '';
	if ($core_config['module']['themes']) {
		$menu = x_hook($core_config['module']['themes'],'themes_buildmenu',array($arr_menu));
	}
	return $menu;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$nav_pages = '';
	if ($core_config['module']['themes']) {
		$nav_pages = x_hook($core_config['module']['themes'],'themes_navbar',array($num, $nav, $max_nav, $url, $page));
	}
	return $nav_pages;
}

?>