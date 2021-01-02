<?php
defined('_SECURE_') or die('Forbidden');

function paper_hook_themes_submenu($content = '') {
	global $user_config;
	
	$separator = "&nbsp;&nbsp;&nbsp;";
	
	$logged_in = $user_config['username'];
	$tooltips_logged_in = _('Logged in as') . ' ' . $logged_in;
	
	$credit = core_display_credit(rate_getusercredit($user_config['username']));
	$tooltips_credit = _('Your credit');
	
	$ret = '<div>';
	$ret .= '<span class="playsms-icon fas fa-user" alt="' . $tooltips_logged_in . '" title="' . $tooltips_logged_in . '"></span>' . $logged_in;
	$ret .= $separator . '<span class="playsms-icon fas fa-credit-card" alt="' . $tooltips_credit . '" title="' . $tooltips_credit . '"></span><div id="submenu-credit-show">' . $credit . '</div>';
	
	if (auth_login_as_check()) {
		$ret .= $separator . _a('index.php?app=main&inc=core_auth&route=logout', _('return'));
	}
	
	$ret .= $content;
	$ret .= '</div>';
	
	return $ret;
}

function paper_hook_themes_menu_tree($menu_config) {
	global $core_config, $user_config, $icon_config;
	
	$main_menu = "";
	foreach ($menu_config as $menu_title => $array_menu) {
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = (int) ($sub_menu[2] ? $sub_menu[2] : 10) + 100;
			
			// devider or valid entry
			if (($sub_menu_url == '#') && ($sub_menu_title == '-')) {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li class=\"nav-item divider\"></li>";
			} else if ($sub_menu_url == '#') {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li class=\"nav-item\">" . $sub_menu_title . "</li>";
			} else if ($sub_menu_url && $sub_menu_title) {
				if (acl_checkurl($sub_menu_url)) {
					$m[$sub_menu_index . '.' . $sub_menu_title] = "<li><a class=\"nav-link\" href='" . _u($sub_menu_url) . "'>" . $sub_menu_title . "</a></li>";
				}
			}
		}
		
		if (count($m)) {
			$main_menu .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $menu_title . " <b class='caret'></b></a>";
			$main_menu .= "<ul class='nav dropdown-menu'>";
			
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
		</nav>";
	
	return $content;
}
