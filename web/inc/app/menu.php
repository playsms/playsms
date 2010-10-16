<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

if (!($console = $_REQUEST['console'])) {
    bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');
    include $apps_path['themes'].'/'.$themes_module.'/header.php';
}

bindtextdomain('messages', $apps_path['plug'].'/language/');

// core menus for admin users
$c_fn = $apps_path['incs']."/admin/".$inc.".php";
if (file_exists($c_fn)) {
    include $c_fn;
}

// core menus for non-admin or regular users
$c_fn = $apps_path['incs']."/user/".$inc.".php";
if (file_exists($c_fn)) {
    include $c_fn;
}

// core menus for visitors (not user)
$c_fn = $apps_path['incs']."/common/".$inc.".php";
if (file_exists($c_fn)) {
    include $c_fn;
}

// plugins
for ($i=0;$i<count($plugins_category);$i++) {
    if ($pc = $plugins_category[$i]) {
	for ($c=0;$c<count($core_config[$pc.'list']);$c++) {
	    if ($inc == $pc.'_'.$core_config[$pc.'list'][$c]) {
		$pn = $core_config[$pc.'list'][$c];
		$c_fn = $apps_path['plug'].'/'.$pc.'/'.$pn.'/'.$pn.'.php';
		if (file_exists($c_fn)) {
		    bindtextdomain('messages', $apps_path['plug'].'/'.$pc.'/'.$pn.'/language/');
		    include_once $c_fn;
		    break;
		}
	    }
	}
    }
}

if (!($console = $_REQUEST['console'])) {
    bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');
    include $apps_path['themes'].'/'.$themes_module.'/footer.php';
}

?>