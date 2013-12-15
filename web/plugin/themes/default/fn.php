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
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_get_menu_tree(),
			'NAME' => $core_config['user']['name'],
			'USERNAME' => $core_config['user']['username'],
			'GRAVATAR' => $core_config['user']['opt']['gravatar'],
			'Logout' => _('Logout')
		),
		'if' => array(
			'valid' => auth_isvalid()
		)
	);
	$content = tpl_apply($tpl);
	return $content;
}

function default_hook_themes_buildmenu($menu_config) {
	global $core_config;
	$main_menu = "";
	foreach ($menu_config as $menu_title => $array_menu) {
		if ($menu_title == $core_config['menutab']['my_account']) {
			$main_menu .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $core_config['user']['name'] . " (".$core_config['user']['username'].") <b class='caret'></b></a>";
			$main_menu .= "<ul class='dropdown-menu'>";
		} else {
			$main_menu .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $menu_title . " <b class='caret'></b></a>";
			$main_menu .= "<ul class='dropdown-menu'>";
		}
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = ( $sub_menu[2] ? $sub_menu[2] : 3 );
			$m[$sub_menu_index.'.'.$sub_menu_title] = "<li><a href='" . $sub_menu_url . "'>" . $sub_menu_title . "</a></li>";
		}
		ksort($m);
		foreach ($m as $mm) {
			$main_menu .= $mm;
		}
		unset($m);
		if ($menu_title == $core_config['menutab']['my_account']) {
			$main_menu .= "<li><hr /></li>";
			$main_menu .= "<li><a href='index.php?app=menu&inc=user_config&op=user_config'>" . _('User configuration') . "</a></li>";
			$main_menu .= "<li><a href='index.php?app=menu&inc=user_pref&op=user_pref'>" . _('Preferences') . "</a></li>";
		}
		$main_menu .= "</ul>";
		$main_menu .= "</li>";
	}
	
	$content = "
		<nav class='navbar navbar-inverse navbar-fixed-top' role='navigation'>
			<div class='navbar-inner'>
				<div class='container'>
					<div class='navbar-header'>
						<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
							<span class='icon-bar'></span>
							<span class='icon-bar'></span>
							<span class='icon-bar'></span>
						</button>
						<a href='" . $core_config['main']['cfg_main_website_url'] . "' class='brand navbar-brand'>" . $core_config['main']['cfg_main_website_name'] . "</a>
					</div>
					<div class='navbar-collapse collapse'>
						<ul class='nav navbar-nav'>
							<li class='active'><a href='" . _HTTP_PATH_BASE_ . "'>" . _('Home') . "</a></li>
							".$main_menu."
						</ul>
						<ul id=nav-login class='nav navbar-nav navbar-right'>
							<li><a href='index.php?app=page&op=auth_logout'>" . _('Logout') . "</a></li>
						</ul>
					</div>
				</div>
			</div>
		</nav>
	";
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
		$nav_pages = "<div class=playsms-nav-bar>";
		$nav_pages .= "<a href='".$url."&page=1&nav=1'> << </a>";
		$nav_pages .= ($start==$nav) ? " < " : "<a href='".$url."&page=".((($nav-2)*$max_nav)+1)."&nav=".($nav-1)."'> < </a>";
		$nav_pages .= ($start==$nav) ? "" : " ... ";
		for($i=$nav_start;$i<=$nav_end;$i++) {
			if($i>$num){ break; };
			if ($i == $page) {
				$nav_pages .= "<u>$i</u> ";
			} else {
				$nav_pages .= "<a href='".$url."&page=".$i."&nav=".$nav."'>".$i."</a> ";
			}
		}
		$nav_pages .= ($end==$nav) ? "" : "..";
		$nav_pages .= ($end==$nav) ? " > " : "<a href='".$url."&page=".(($nav*$max_nav)+1)."&nav=".($nav+1)."'> > </a>";
		$nav_pages .= "<a href='".$url."&page=".$num."&nav=".$end."'> >> </a>";
		$nav_pages .= "</div>";
	}
	return $nav_pages;
}
