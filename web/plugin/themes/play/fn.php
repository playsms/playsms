<?php

function play_hook_themes_apply($content) {
	global $core_config, $web_title, $themes_default_charset, $theme_play_foot1, $theme_play_head1, $theme_play_head2, $theme_image;
	unset($tpl);
	$tpl = array(
		'name' => 'themes_layout',
		'var' => array(
			'CONTENT' => $content,
			'WEB_TITLE' => $web_title,
			'THEMES_DEFAULT_CHARSET' => $themes_default_charset,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_get_menu_tree(),
			'THEMES_BUILD_MENU' => theme_play_build_menu(),
			'THEMES_PLAY_FOOT1' => $theme_play_foot1,
			'THEMES_PLAY_HEAD1' => $theme_play_head1,
			'THEMES_PLAY_HEAD2' => $theme_play_head2,
			'NAME' => $core_config['user']['name'],
			'USERNAME' => $core_config['user']['username'],
			'GRAVATAR' => $core_config['user']['opt']['gravatar'],
			'Home' => _('Home'),
			'Logged in' => _('Logged in'),
			'Logout' => _('Logout')
		),
		'if' => array(
			'theme_image' => ( !empty($theme_image) ? TRUE : FALSE ),
			'valid' => valid()
		)
	);
	$content = tpl_apply($tpl);
	return $content;
}

function theme_play_build_menu() {
  global $menu_config, $username, $name;
  $content = '<ul class="nav">';
  $i = 0;
	foreach ($menu_config as $cat => $value) {
		$i++;
		$content .= "<li class=\"dropdown\">";
		$content .= '<a href="#" class="dropdown-toggle">'.$cat.'</a>';
		$content .= '<ul class="dropdown-menu">';
		foreach ($value as $sub_key => $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = ( $sub_menu[2] ? $sub_menu[2] : 3 );
			$m[$sub_menu_index.'.'.$sub_menu_title] = "<li><a href='" . $sub_menu_url . "'>" . $sub_menu_title . "</a></li>";
		}
		ksort($m);
		foreach ($m as $mm) {
			$content .= $mm;
		}
		unset($m);
		$content .= "</ul>";
		$content .= "</li>";
	}
	$content .= '</ul>';
	if (valid()) { 
	  $content .= '<ul class="nav secondary-nav">';
	  $content .= "<li class=\"dropdown pull-right\">";
	  $content .= '<a href="#" class="dropdown-toggle">'.$name.' ('.$username.')</a>';
	  $content .= '<ul class="dropdown-menu">';
		$content .= '<li><a href="index.php?app=menu&inc=user_config&op=user_config">' . _('User configuration') . '</a></li>';
		$content .= '<li><a href="index.php?app=menu&inc=user_pref&op=user_pref">'._('Preferences').'</a></li>';
	  $content .= '<li><a href="index.php?app=page&op=auth_logout">Logout</a></li>';
	  $content .= "</ul>";
	  $content .= "</li>";
	  $content .= '</ul>';
  }
  return $content;
}

function play_hook_themes_navbar($num, $nav, $max_nav, $url, $page) {
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