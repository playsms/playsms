<?php

if(!(defined('_SECURE_'))){die('Intruder alert');};

// main functions
include $apps_path['libs']."/fn_logger.php";
include $apps_path['libs']."/fn_auth.php";
include $apps_path['libs']."/fn_user.php";
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_sendmail.php";
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_core.php";
include $apps_path['libs']."/fn_themes.php";

// init global variables
include $apps_path['libs']."/lib_init1.php";

// custom functions before plugins loading
include $apps_path['libs']."/fn_custom1.php";

// load plugin's config and libraries
for ($i=0;$i<count($plugins_category);$i++) {
    if ($pc = $plugins_category[$i]) {
	// get plugins
	$dir = $apps_path['plug']."/".$pc."/";
	unset($core_config[$pc.'list']);
	unset($tmp_core_config[$pc.'list']);
	$fd = opendir($dir);
	while(false !== (${$pc} = readdir($fd)))
	{
	    if (is_dir($dir.${$pc}) && substr(${$pc},0,1) != "." ) {
		$tmp_core_config[$pc.'list'][] = ${$pc};
	    }
	}
	closedir();
	// load each plugin's config and libaries
	for ($c=0;$c<count($tmp_core_config[$pc.'list']);$c++)
	{
	    $c_fn1 = $dir.$tmp_core_config[$pc.'list'][$c]."/config.php";
	    if (file_exists($c_fn1))
	    {
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

// init global variables after plugins
include $apps_path['libs']."/lib_init2.php";

// custom functions after plugins loaded
include $apps_path['libs']."/fn_custom2.php";

?>