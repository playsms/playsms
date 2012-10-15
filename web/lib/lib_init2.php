<?php
defined('_SECURE_') or die('Forbidden');

// this file loaded after plugins

// load menus into core_config
$core_config['menu'] = $arr_menu;

// load plugin's config into core_config
$core_config['plugins'] = $plugin;

// fixme anton - uncomment this if you want to know what are available in $core_config
print_r($core_config); die();

?>