<?php
defined('_SECURE_') or die('Forbidden');

// main functions
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_billing.php";
include $apps_path['libs']."/fn_lang.php";
include $apps_path['libs']."/fn_gateway.php";
include $apps_path['libs']."/fn_dlr.php";
include $apps_path['libs']."/fn_recvsms.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_themes.php";
include $apps_path['libs']."/fn_tpl.php";
include $apps_path['libs']."/fn_webservices.php";
include $apps_path['libs']."/fn_csv.php";
include $apps_path['libs']."/fn_download.php";

// init global variables
include $apps_path['libs']."/lib_init1.php";

// load plugin's config and libraries
for ($i=0;$i<count($plugins_category);$i++) {
	if ($pc = $plugins_category[$i]) {
		// get plugins
		$dir = $apps_path['plug'].'/'.$pc.'/';
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

// load common items for themes
$c_fn1 = $apps_path['plug'].'/themes/common/config.php';
if (file_exists($c_fn1)) {
	include $c_fn1;
	$c_fn2 = $apps_path['plug'].'/themes/common/fn.php';
	if (file_exists($c_fn2)) {
		include $c_fn2;
	}
}

// load active themes
$dir = $apps_path['plug'].'/';
$pc = 'themes';
$pl = themes_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/config.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
	$c_fn2 = $pl_dir.'/fn.php';
	if (file_exists($c_fn2)) {
		include $c_fn2;
	}
}

// load each plugin's config and libaries
$dir = $apps_path['plug'].'/';
$pcs = array('language', 'gateway', 'feature', 'tools');
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
			$c_fn2 = $pl_dir.'/fn.php';
			if (file_exists($c_fn2)) {
				include $c_fn2;
			}
		}
	}
}

//print_r($plugin); die();
//print_r($core_config); die();

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// init global variables after plugins
include $apps_path['libs']."/lib_init2.php";

?>