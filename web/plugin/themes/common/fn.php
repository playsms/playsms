<?php
defined('_SECURE_') or die('Forbidden');

function common_hook_themes_apply($content) {
	global $core_config, $user_config;
	
	$themes_lang = strtolower(substr($user_config['language_module'], 0, 2));
	
	if ($themes_layout = trim($_REQUEST['_themes_layout_'])) {
		$themes_layout = 'themes_layout_' . $themes_layout;
	} else {
		$themes_layout = 'themes_layout';
	}
	
	$tpl = array(
		'name' => $themes_layout,
		'vars' => array(
			'CONTENT' => $content,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_get_menu_tree(),
			'THEMES_SUBMENU' => themes_submenu(),
			'THEMES_LANG' => ($themes_lang ? $themes_lang : 'en'),
			'CREDIT_SHOW_URL' => _u('index.php?app=ws&op=credit'),
			'NAME' => $user_config['name'],
			'USERNAME' => $user_config['username'],
			'GRAVATAR' => $user_config['opt']['gravatar'],
			'LAYOUT_FOOTER' => $core_config['main']['layout_footer'],
			'Logout' => _('Logout') 
		),
		'ifs' => array(
			'valid' => auth_isvalid() 
		) 
	);
	$content = tpl_apply($tpl, array(
		'core_config',
		'user_config' 
	));
	
	return $content;
}

function common_hook_themes_submenu($content = '') {
	global $user_config;
	
	$separator = "&nbsp;&nbsp;&nbsp;";
	
	$logged_in = $user_config['username'];
	$tooltips_logged_in = _('Logged in as') . ' ' . $logged_in;
	
	$credit = $user_config['credit'];
	$tooltips_credit = _('Your credit');
	
	$ret = '<div>';
	$ret .= '<span class="playsms-icon glyphicon glyphicon-user" alt="' . $tooltips_logged_in . '" title="' . $tooltips_logged_in . '"></span>' . $logged_in;
	$ret .= $separator . '<span class="playsms-icon glyphicon glyphicon-credit-card" alt="' . $tooltips_credit . '" title="' . $tooltips_credit . '"></span><div id="submenu-credit-show">' . $credit . '</div>';
	
	if (auth_login_as_check()) {
		$ret .= $separator . _a('index.php?app=main&inc=core_auth&route=logout', _('return'));
	}
	
	$ret .= $content;
	$ret .= '</div>';
	
	return $ret;
}

function common_hook_themes_buildmenu($menu_config) {
	global $core_config, $user_config, $icon_config;
	
	$main_menu = "";
	foreach ($menu_config as $menu_title => $array_menu) {
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = (int) ($sub_menu[2] ? $sub_menu[2] : 10) + 100;
			
			// devider or valid entry
			if (($sub_menu_url == '#') && ($sub_menu_title == '-')) {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li class=\"divider\"></li>";
			} else if ($sub_menu_url == '#') {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li>" . $sub_menu_title . "</li>";
			} else if ($sub_menu_url && $sub_menu_title) {
				if (acl_checkurl($sub_menu_url)) {
					$m[$sub_menu_index . '.' . $sub_menu_title] = "<li><a href='" . _u($sub_menu_url) . "'>" . $sub_menu_title . "</a></li>";
				}
			}
		}
		
		if (count($m)) {
			$main_menu .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $menu_title . " <b class='caret'></b></a>";
			$main_menu .= "<ul class='dropdown-menu'>";
			
			ksort($m);
			foreach ($m as $mm) {
				$main_menu .= $mm;
			}
			unset($m);
			
			$main_menu .= "</ul>";
			$main_menu .= "</li>";
		}
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
						<a href='" . _u($core_config['main']['main_website_url']) . "' class='brand navbar-brand'>" . $core_config['main']['main_website_name'] . "</a>
					</div>
					<div class='navbar-collapse collapse'>
						<ul class='nav navbar-nav'>
							<li class='active'><a href='" . _u(_HTTP_PATH_BASE_) . "'>" . _('Home') . "</a></li>
							" . $main_menu . "
						</ul>
						<ul class='nav navbar-nav navbar-right'>
							<li><a href='" . _u('index.php?app=main&inc=core_auth&route=logout') . "'>" . $icon_config['logout'] . "</a></li>
						</ul>
					</div>
				</div>
			</div>
		</nav>
	";
	
	return $content;
}

function common_hook_themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$nav_pages = "";
	if ($num) {
		$nav_start = ((($nav - 1) * $max_nav) + 1);
		$nav_end = (($nav) * $max_nav);
		$start = 1;
		$end = ceil($num / $max_nav);
		$nav_pages = "<div class=playsms-nav-bar>";
		$nav_pages .= "<a href='" . _u($url . '&page=1&nav=1') . "'> << </a>";
		$nav_pages .= ($start == $nav) ? " < " : "<a href='" . _u($url . '&page=' . ((($nav - 2) * $max_nav) + 1) . '&nav=' . ($nav - 1)) . "'> < </a>";
		$nav_pages .= ($start == $nav) ? "" : " ... ";
		for ($i = $nav_start; $i <= $nav_end; $i++) {
			if ($i > $num) {
				break;
			}
			;
			if ($i == $page) {
				$nav_pages .= "<u>$i</u> ";
			} else {
				$nav_pages .= "<a href='" . _u($url . '&page=' . $i . '&nav=' . $nav) . "'>" . $i . "</a> ";
			}
		}
		$nav_pages .= ($end == $nav) ? "" : "..";
		$nav_pages .= ($end == $nav) ? " > " : "<a href='" . _u($url . '&page=' . (($nav * $max_nav) + 1) . '&nav=' . ($nav + 1)) . "'> > </a>";
		$nav_pages .= "<a href='" . _u($url . '&page=' . $num . '&nav=' . $end) . "'> >> </a>";
		$nav_pages .= "</div>";
	}
	return $nav_pages;
}
