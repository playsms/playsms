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
include $core_config['apps_path']['libs'] . "/fn_phonebook.php";
include $core_config['apps_path']['libs'] . "/fn_rate.php";
include $core_config['apps_path']['libs'] . "/fn_billing.php";
include $core_config['apps_path']['libs'] . "/fn_dlr.php";
include $core_config['apps_path']['libs'] . "/fn_webservices.php";
include $core_config['apps_path']['libs'] . "/fn_keyword.php";

// plugins category
$core_config['plugins']['category'] = isset($core_config['plugins']['category']) && is_array($core_config['plugins']['category'])
	? $core_config['plugins']['category'] : [
		'feature',
		'gateway',
		'themes',
		'language'
	];

// plugins list
$core_config['plugins']['list'] = [];

// load commons
$dir = $core_config['apps_path']['plug'] . '/';
foreach ( $core_config['plugins']['category'] as $category ) {
	$dir = $core_config['apps_path']['plug'] . '/' . $category . '/';
	$common_conf = $dir . '/common/config.php';
	if (is_file($common_conf)) {
		include $common_conf;
		$common_fn = $dir . '/common/fn.php';
		if (is_file($common_fn)) {
			include $common_fn;
		}
	}
}

// load list of plugins
foreach ( $core_config['plugins']['category'] as $category ) {
	$dir = $core_config['apps_path']['plug'] . '/' . $category . '/';
	if (is_dir($dir)) {
		$core_config['plugins']['list'][$category] = [];
		$plugins = [];
		$fd = opendir($dir);
		while (false !== ($plugin = readdir($fd))) {
			// plugin's dir prefixed with a dot or an underscore, or named 'common', will not be loaded
			if (preg_match('/^[^\._][a-zA-Z0-9\_]+/', $plugin) && strlen($plugin) <= 64 && $plugin != 'common') {
				$plugins[] = $plugin;
			}
		}
		closedir();
		sort($plugins);
		foreach ( $plugins as $plugin ) {
			if (is_dir($dir . $plugin)) {
				$core_config['plugins']['list'][$category][] = $plugin;
			}
		}
	}
}

// load configs and functions
$dir = $core_config['apps_path']['plug'] . '/';
foreach ( $core_config['plugins']['category'] as $category ) {
	foreach ( $core_config['plugins']['list'][$category] as $plugin ) {
		$c_conf = $dir . $category . '/' . $plugin . '/config.php';
		if (is_file($c_conf)) {
			include $c_conf;
			if ($category == 'feature' || $category == 'gateway') {
				$c_fn = $dir . $category . '/' . $plugin . '/fn.php';
				if (is_file($c_fn)) {
					include $c_fn;
				}
			}
		}
	}
}

// load active themes libs
$dir = $core_config['apps_path']['plug'] . '/';
$category = 'themes';
$plugin = core_themes_get();
$plugin_dir = $dir . $category . '/' . $plugin;
$c_fn = $plugin_dir . '/fn.php';
if (is_file($c_fn)) {
	include $c_fn;
}

// themes main overrides
$mains = $themes_config[core_themes_get()]['main'];
if (is_array($mains)) {
	foreach ( $mains as $main_key => $main_val ) {
		if ($main_key && $main_val) {
			$core_config['main'][$main_key] = $main_val;
		}
	}
}

// themes icons overrides
$icons = $themes_config[core_themes_get()]['icon'];
if (is_array($icons)) {
	foreach ( $icons as $icon_action => $icon_url ) {
		if ($icon_action && $icon_url) {
			$icon_config[$icon_action] = $icon_url;
		}
	}
}

// themes menus overrides
$menus = $themes_config[core_themes_get()]['menu'];
if (is_array($menus)) {
	foreach ( $menus as $menu_menutab => $menu_item ) {
		unset($menu_config[$menu_menutab]);
	}
	foreach ( $menus as $menu_menutab => $menu_item ) {
		if ($menu_menutab && $menu_item) {
			$menu_config[$menu_menutab] = $menu_item;
		}
	}
}

// fixme anton - debug
//print_r($icon_config); die();
//print_r($menu_config); die();
//print_r($plugin_config); die();
//print_r($themes_config); die();
//print_r($user_config); die();
//print_r($core_config); die();
//print_r($GLOBALS); die();
