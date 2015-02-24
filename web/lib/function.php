<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

// main functions
include $core_config['apps_path']['libs']."/fn_phonebook.php";
include $core_config['apps_path']['libs']."/fn_rate.php";
include $core_config['apps_path']['libs']."/fn_billing.php";
include $core_config['apps_path']['libs']."/fn_dlr.php";
include $core_config['apps_path']['libs']."/fn_webservices.php";
include $core_config['apps_path']['libs']."/fn_keyword.php";

// load common configurations
$c_fn1 = $core_config['apps_path']['plug'].'/themes/common/config.php';
if (file_exists($c_fn1)) {
	include $c_fn1;
	$c_fn2 = $core_config['apps_path']['plug'].'/themes/common/fn.php';
	if (file_exists($c_fn2)) {
		include $c_fn2;
	}
}

// load list of plugins
for ($i=0;$i<count($core_config['plugins_category']);$i++) {
	if ($pc = $core_config['plugins_category'][$i]) {
		// get plugins
		$dir = $core_config['apps_path']['plug'].'/'.$pc.'/';
		unset($core_config[$pc.'list']);
		unset($tmp_core_config[$pc.'list']);
		$fd = opendir($dir);
		$pc_names = array();
		while(false !== ($pl_name = readdir($fd))) {
			// plugin's dir prefixed with dot or underscore will not be loaded
			if (substr($pl_name, 0, 1) != "." && substr($pl_name, 0, 1) != "_" ) {
				// exeptions for themes/common
				if (! (($pc == 'themes') && ($pl_name == 'common'))) {
					$pc_names[] = $pl_name;
				}
			}
		}
		closedir();
		sort($pc_names);
		for ($j=0;$j<count($pc_names);$j++) {
			if (is_dir($dir.$pc_names[$j])) {
				$core_config[$pc.'list'][] = $pc_names[$j];
			}
		}
	}
}

// load each plugin's config
$dir = $core_config['apps_path']['plug'].'/';
$pcs = array('themes', 'language', 'gateway', 'feature');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/config.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load each plugin's libs
$dir = $core_config['apps_path']['plug'].'/';
$pcs = array('feature', 'gateway');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/fn.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load active themes libs
$dir = $core_config['apps_path']['plug'].'/';
$pc = 'themes';
$pl = core_themes_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/fn.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
}

// themes main overrides
$mains = $themes_config[core_themes_get()]['main'];
if (is_array($mains)) {
	foreach ($mains as $main_key => $main_val) {
		if ($main_key && $main_val) {
			$core_config['main'][$main_key] = $main_val;
		}
	}
}

// themes icons overrides
$icons = $themes_config[core_themes_get()]['icon'];
if (is_array($icons)) {
	foreach ($icons as $icon_action => $icon_url) {
		if ($icon_action && $icon_url) {
			$icon_config[$icon_action] = $icon_url;
		}
	}
}

// themes menus overrides
$menus = $themes_config[core_themes_get()]['menu'];
if (is_array($menus)) {
	foreach ($menus as $menu_menutab => $menu_item) {
		unset($menu_config[$menu_menutab]);
	}
	foreach ($menus as $menu_menutab => $menu_item) {
		if ($menu_menutab && $menu_item) {
			$menu_config[$menu_menutab] = $menu_item;
		}
	}
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $core_config['apps_path']['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// fixme anton - debug
//print_r($icon_config); die();
//print_r($menu_config); die();
//print_r($plugin_config); die();
//print_r($themes_config); die();
//print_r($user_config); die();
//print_r($core_config); die();
//print_r($GLOBALS); die();
