<?php

function theme_play_build_menu() {
  global $menu_config, $username;
  $content = '<ul class="nav">';
  $i = 0;
	foreach ($menu_config as $cat => $value) {
		$i++;
		$content .= "<li class=\"dropdown\">";
		$content .= '<a href="#" class="dropdown-toggle">'.$cat.'</a>';
		$content .= '<ul class="dropdown-menu">';
		foreach ($value as $sub_key => $menu) {
			$content .= '<li><a href="'.$menu[0].'">'.$menu[1].'</a></li>';
		}
		$content .= "</ul>";
		$content .= "</li>";
	}
	$content .= '</ul>';
	if (valid()) { 
	  $content .= '<ul class="nav secondary-nav">';
	  $content .= "<li class=\"dropdown pull-right\">";
	  $content .= '<a href="#" class="dropdown-toggle">'.$username.'</a>';
	  $content .= '<ul class="dropdown-menu">';
	  $content .= '<li><a href="index.php?app=menu&inc=user_pref&op=user_pref">'._('Preferences').'</a></li>';
	  $content .= '<li><a href="index.php?app=page&op=auth_logout">Logout</a></li>';
	  $content .= "</ul>";
	  $content .= "</li>";
	  $content .= '</ul>';
  }
  return $content;
}

function play_hook_themes_buildmenu($menu_config) {
	global $core_config;
	$content_tree = "";
	$tree_index = 1;
	$open = 0;
	foreach($menu_config as $key=>$value) {
		if($tree_index==1){$open = 1;}else{$open = 0;};
		$content_tree .= "\t\t d.add($tree_index,0,\"$key\",'','','','','',$open);\n";
		$tree_index++;
	}
	$tree_index_top = 1;
	foreach($menu_config as $key=>$value) {
		foreach($value as $sub_key1=>$sub_value1) {
			$content_tree .= "\t\t d.add($tree_index,$tree_index_top,\"".$sub_value1[1]."\", '".$sub_value1[0]."', '', '');\n";
			$tree_index++;
		}
		$tree_index_top++;
	}
	$tree_index_top++;
	$content = "
        <script type=\"text/javascript\">
        <!--
        d = new dTree('d');
        d.add(0,-1,'"._('Home')."', '".$core_config['http_path']['base']."', '', '_top');
        $content_tree
        d.add($tree_index_top,0,'"._('Logout')."', 'index.php?app=page&op=auth_logout', '', '_top');
        document.write(d);
        //-->
        </script>  
    ";
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