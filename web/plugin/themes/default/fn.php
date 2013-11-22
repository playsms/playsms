<?php
defined('_SECURE_') or die('Forbidden');

function default_hook_themes_apply($content) {
	global $core_config, $web_title;
	unset($tpl);
	$tpl = array(
		'name' => 'themes_layout',
		'var' => array(
			'CONTENT' => $content,
			'WEB_TITLE' => $web_title,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'THEMES_MODULE' => themes_get(),
			'THEMES_MENU_TREE' => themes_get_menu_tree(),
			'NAME' => $core_config['user']['name'],
			'USERNAME' => $core_config['user']['username'],
			'GRAVATAR' => $core_config['user']['opt']['gravatar'],
			'Logout' => _('Logout')
		),
		'if' => array(
			'valid' => valid()
		)
	);
	$content = tpl_apply($tpl);
	return $content;
}

function default_hook_themes_buildmenu($menu_config) {
	global $core_config;
	$content .= "<div id='container'>\n";
	$content .= "<nav class='navbar navbar-default navbar-fixed-top' role='navigation'>";
	$content .= "<ul class='nav navbar-nav'>\n";

	$content .= "<li class='active'><a href='" . _HTTP_PATH_BASE_ . "'>" . _('Home') . "</a></li>\n";
	foreach ($menu_config as $menu_title => $array_menu) {
		$content .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $menu_title . " <b class='caret'></b></a>\n";
		$content .= "<ul class='dropdown-menu'>\n";
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$content .= "<li><a href='" . $sub_menu_url . "'>" . $sub_menu_title . "</a></li>\n";
		}
		$content .= "</ul>\n";
		$content .= "</li>\n";
	}
	$content .= "<li class='dropdown pull-right'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $core_config['user']['name'] . " (" . $core_config['user']['username'] . ") <b class='caret'></b></a>\n";
	$content .= "<ul class='dropdown-menu'>\n";
	$content .= "<li><a href='index.php?app=menu&inc=user_config&op=user_config'>" . _('User configuration') . "</a></li>\n";
	$content .= "<li><a href='index.php?app=menu&inc=user_pref&op=user_pref'>" . _('Preferences') . "</a></li>\n";
	$content .= "<li><a href='index.php?app=page&op=auth_logout'>" . _('Logout') . "</a></li>\n";
	$content .= "</ul>\n";
	$content .= "</li>\n";

	$content .= "</ul>\n";
	$content .= "</nav>\n";
	$content .= "</div>\n";

	return $content;
}

function default_hook_themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$nav_pages = "";
	if ($num) {
		$nav_start = ((($nav-1) * $max_nav)+1);
		$nav_end = (($nav) * $max_nav);
		$start = 1;
		$end = ceil($num/$max_nav);
		$nav_pages = "<div id='navbar'>";
		$nav_pages .= "<a href='".$url."&page=1&nav=1'> << </a>";
		$nav_pages .= ($start==$nav) ? " &nbsp; < &nbsp; " : "<a href='".$url."&page=".((($nav-2)*$max_nav)+1)."&nav=".($nav-1)."'> &nbsp; < &nbsp; </a>";
		$nav_pages .= ($start==$nav) ? "" : " ... ";
		for($i=$nav_start;$i<=$nav_end;$i++) {
			if($i>$num){ break; };
			if ($i == $page) {
				$nav_pages .= "<u>$i</u> ";
			} else {
				$nav_pages .= "<a href='".$url."&page=".$i."&nav=".$nav."'>".$i."</a> ";
			}
		}
		$nav_pages .= ($end==$nav) ? "" : " ... ";
		$nav_pages .= ($end==$nav) ? " &nbsp; > &nbsp; " : "<a href='".$url."&page=".(($nav*$max_nav)+1)."&nav=".($nav+1)."'> &nbsp; > &nbsp; </a>";
		$nav_pages .= "<a href='".$url."&page=".$num."&nav=".$end."'> >> </a>";
		$nav_pages .= "</div>";
	}
	return $nav_pages;
}

?>