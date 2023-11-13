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

// load common configurations
$c_conf = _APPS_PATH_PLUG_ . '/themes/common/config.php';
$c_libs = _APPS_PATH_PLUG_ . '/themes/common/fn.php';
if (is_file($c_conf) && is_file($c_libs)) {
	include $c_conf;
	include $c_libs;
} else {
	_log('Fail to locate common themes files', 1, 'lib function');
	ob_end_clean();
	die(_('FATAL ERROR') . ' : ' . _('Fail to locate common themes files'));
}

// load list of plugins and their configs and libs
if (isset($core_config['plugins']['category']) && is_array($core_config['plugins']['category'])) {
	$c_categories = $core_config['plugins']['category'];
	foreach ( $c_categories as $c_category ) {
		unset($core_config['plugins']['list'][$c_category]);
		unset($tmp_core_config['plugins']['list'][$c_category]);
		// get plugins
		$c_plugins = [];
		$c_dir = _APPS_PATH_PLUG_ . '/' . $c_category . '/';
		if (is_dir($c_dir)) {
			$fd = opendir($c_dir);
			while (false !== ($c_plugin = readdir($fd))) {
				// plugin's dir prefixed with dot or underscore will not be loaded
				if (substr($c_plugin, 0, 1) != "." && substr($c_plugin, 0, 1) != "_") {
					// exeptions for themes/common
					if (!(($c_category == 'themes') && ($c_plugin == 'common'))) {
						$c_plugin_dir = $c_dir . $c_plugin;
						if (is_dir($c_plugin_dir)) {
							$c_plugins[] = $c_plugin;
						}
					}
				}
			}
			closedir();
			// sort plugins list
			sort($c_plugins);
			foreach ( $c_plugins as $c_plugin ) {
				$c_plugin_dir = $c_dir . $c_plugin;
				if (is_dir($c_plugin_dir)) {
					$c_conf = $c_plugin_dir . '/config.php';
					$c_libs = $c_plugin_dir . '/fn.php';
					// must load plugin's config and libs
					if (is_file($c_conf) && is_file($c_libs)) {
						// load plugin's shipped config file
						include $c_conf;

						// load plugin's shipped libs file
						include $c_libs;

						$c_plugin_custom_dir = _APPS_PATH_CUSTOM_ . '/configs/' . $c_category . '/' . $c_plugin;
						$c_custom_conf = $c_plugin_custom_dir . '/config.php';
						$c_custom_libs = $c_plugin_custom_dir . '/fn.php';
						// also load plugin's custom config file if exists
						if (is_file($c_custom_conf)) {
							include $c_custom_conf;
						}
						// also load plugin's custom libs file if exists
						if (is_file($c_custom_libs)) {
							include $c_custom_libs;

						}

						// save in list of plugins
						$core_config['plugins']['list'][$c_category][] = $c_plugin;
					}
				}
			}
		}
	}
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
