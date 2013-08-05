<?php
defined('_SECURE_') or die('Forbidden');

function default_hook_themes_buildmenu($menu_config) {
	global $core_config;
	$content_tree = "";
	$tree_index_top = 1;
	$tree_index = 101;
	$open = 0;

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

	// javascript menu tree (left navigation menu)
	// d.add(id, pid, name, url, title, target, icon, iconOpen, open)

	// Note: login and then view source, see LEFT NAVIGATION MENU block in the source

	foreach ($menu_config as $menu_title => $array_menu) {
		$open = ( $tree_index == 1 ? 1 : 0 );
		$content_tree .= "d.add(".$tree_index_top.",0,'".$menu_title."','','".$menu_title."','_top','','','',".$open.");\n";
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$content_tree .= "\td.add(".$tree_index.",".$tree_index_top.",'".$sub_menu_title."','".$sub_menu_url."','".$sub_menu_title."','_top','','','');\n";
			$tree_index++;
		}
		$tree_index_top++;
	}

	$home_url = $core_config['http_path']['base'];
	$logout_url = "index.php?app=page&op=auth_logout";

	$content = "\n\n<!-- BEGIN LEFT NAVIGATION MENU -->\n\n";
	$content .= "<script type=\"text/javascript\">\n";
	$content .= "<!--\n";
	$content .= "d = new dTree('d');\n";
	$content .= "d.add(0,-1,'"._('Home')."','".$home_url."','"._('Home')."','_top','','','');\n";
	$content .= $content_tree;
	// $content .= "d.add(".$tree_index_top.",0,'"._('Logout')."','".$logout_url."','"._('Logout')."','_top','','','');\n";
	$content .= "document.write(d);\n";
	$content .= "//-->\n";
	$content .= "</script>";
	$content .= "\n\n<!-- END LEFT NAVIGATION MENU -->\n\n";

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
				$nav_pages .= "$i ";
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