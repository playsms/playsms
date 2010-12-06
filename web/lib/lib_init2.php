<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

// this file loaded after plugins

// load menus into core_config
$core_config['menu'] = $arr_menu;

// fixme anton - uncomment this if you want to know what are available in $core_config
//print_r($core_config); die();

?>