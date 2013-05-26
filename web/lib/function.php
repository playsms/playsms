<?php
defined('_SECURE_') or die('Forbidden');

// main functions
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_billing.php";
include $apps_path['libs']."/fn_gateway.php";
include $apps_path['libs']."/fn_recvsms.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_themes.php";
include $apps_path['libs']."/fn_webservices.php";
include $apps_path['libs']."/fn_csv.php";
include $apps_path['libs']."/fn_download.php";

// init global variables
include $apps_path['libs']."/lib_init1.php";

// load plugin's config and libraries
for ($i=0;$i<count($plugins_category);$i++) {
	if ($pc = $plugins_category[$i]) {
		// get plugins
		$dir = $apps_path['plug']."/".$pc."/";
		unset($core_config[$pc.'list']);
		unset($tmp_core_config[$pc.'list']);
		$fd = opendir($dir);
		$j = 0;
		$pc_names = '';
		while(false !== (${$pc} = readdir($fd)))
		{
			// plugin's dir prefixed with dot or underscore will not be loaded
			if (substr(${$pc},0,1) != "." && substr(${$pc},0,1) != "_" ) {
				$pc_names[$j] = ${$pc};
				$j++;
			}
		}
		closedir();
		sort($pc_names);
		for ($k=0;$k<count($pc_names);$k++) {
			if (is_dir($dir.$pc_names[$k])) {
				$tmp_core_config[$pc.'list'][] = $pc_names[$k];
			}
		}
		// load each plugin's config and libaries
		for ($c=0;$c<count($tmp_core_config[$pc.'list']);$c++)
		{
			$c_fn1 = $dir.$tmp_core_config[$pc.'list'][$c]."/config.php";
			if (file_exists($c_fn1))
			{
				if (function_exists('bindtextdomain')) {
					bindtextdomain('messages', $dir.$tmp_core_config[$pc.'list'][$c].'/language/');
					bind_textdomain_codeset('messages', 'UTF-8');
					textdomain('messages');
				}
				include $c_fn1;
				$c_fn2 = $dir.$tmp_core_config[$pc.'list'][$c]."/fn.php";
				if (file_exists($c_fn2))
				{
					include $c_fn2;
					$core_config[$pc.'list'][$c] = $tmp_core_config[$pc.'list'][$c];
				}
			}
		}
	}
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// init global variables after plugins
include $apps_path['libs']."/lib_init2.php";

?>