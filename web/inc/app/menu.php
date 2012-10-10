<?php
defined('_SECURE_') or die('Forbidden');

if (!($console = $_REQUEST['console'])) {
	if (function_exists('bindtextdomain')) {
		bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $apps_path['themes'].'/'.$themes_module.'/header.php';
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

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
					if (function_exists('bindtextdomain')) {
						bindtextdomain('messages', $apps_path['plug'].'/'.$pc.'/'.$pn.'/language/');
						bind_textdomain_codeset('messages', 'UTF-8');
						textdomain('messages');
					}
					include_once $c_fn;
					break;
				}
			}
		}
	}
}

if (!($console = $_REQUEST['console'])) {
	if (function_exists('bindtextdomain')) {
		bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $apps_path['themes'].'/'.$themes_module.'/footer.php';
}

?>