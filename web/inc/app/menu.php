<?php
defined('_SECURE_') or die('Forbidden');

ob_start();

$continue = TRUE;

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// core menus for admin users
if ($continue) {
	$c_fn = $apps_path['incs']."/admin/".$inc.".php";
	if (file_exists($c_fn)) {
		include $c_fn;
		$continue = FALSE;
	}
}

if ($continue) {
	// core menus for non-admin or regular users
	$c_fn = $apps_path['incs']."/user/".$inc.".php";
	if (file_exists($c_fn)) {
		include $c_fn;
		$continue = FALSE;
	}
}
if ($continue) {
	// core menus for visitors (not user)
	$c_fn = $apps_path['incs']."/common/".$inc.".php";
	if (file_exists($c_fn)) {
		include $c_fn;
		$continue = FALSE;
	}
}

// plugins
if ($continue) {
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
}

$content = ob_get_clean();

empty($tpl);
$tpl['INDEX_CONTENT'] = $content;
$tpl['WEB_TITLE'] = $web_title;
$tpl['THEMES_DEFAULT_CHARSET'] = $themes_default_charset;
$tpl['HTTP_PATH_BASE'] = $core_config['http_path']['base'];
$tpl['HTTP_PATH_THEMES'] = $core_config['http_path']['themes'];
$tpl['THEMES_MODULE'] = themes_get();
$tpl['THEMES_MENU_TREE'] = themes_get_menu_tree();
$tpl['NAME'] = $core_config['user']['name'];
$tpl['USERNAME'] = $core_config['user']['username'];
$tpl['GRAVATAR'] = $core_config['user']['opt']['gravatar'];
$tpl['Logout'] = _('Logout');
$tpl['if']['valid'] = valid();
echo tpl_apply('index', $tpl);

?>