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

	/*
	$menu_config = Array
	(
		[My Account] => Array	<--- $menu_title
		(
			[0] => Array	<--- $array_menu
			(
				[0] => index.php?app=menu&inc=send_sms&op=sendsmstopv	<--- $sub_menu_url
				[1] => Send SMS						<--- $sub_menu_title
			) */

	// Note: login and then view source, see LEFT NAVIGATION MENU block in the source

	/*
	<nav>
		<div class="menu-item">
			<h4><a href="#">Portfolio</a></h4>
			<ul>
				<li><a href="#">Web</a></li>
				<li><a href="#">Print</a></li>
				<li><a href="#">Other</a></li>
			</ul>
		</div>
	</nav> */

	$content = "\n\n<!-- BEGIN NAVIGATION MENU -->\n\n";
	$content .= "<div id=\"menu\">\n";
	$i = 0;
	foreach ($menu_config as $menu_title => $array_menu) {
		$i++;
		$content .= "<div id=\"menu-box-" . $i . "\" class=\"menu-item\">\n";
		$content .= "<p><a href=#>" . $menu_title . "</a></p>\n";
		$content .= "<ul id=\"menu-item-" . $i . "\">\n";
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$content .= "<li><a href=\"".$sub_menu_url."\">".$sub_menu_title."</a></li>\n";
		}
		$content .= "</ul>\n";
		$content .= "</div>\n";
	}
	$content .= "<div class=\"menu-item\"><p><a href=\"index.php?app=page&op=auth_logout\">"._('Logout')."</a></p></div>";
	$content .= "</div>\n";
	$content .= "\n\n<!-- END NAVIGATION MENU -->\n\n";

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