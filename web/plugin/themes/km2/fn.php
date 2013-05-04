<?php
defined('_SECURE_') or die('Forbidden');

/*

// custom functions for this theme
function themes_km2_set_title($title) {
}

function themes_km2_get_title() {
}

*/

function km2_hook_themes_buildmenu($menu_config) {
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
        d.add(0,-1,'<b>"._('Home')."</b>', '".$core_config['http_path']['base']."', '', '_top');
        $content_tree
        d.add($tree_index_top,0,'"._('Logout')."', 'index.php?app=page&op=auth_logout', '', '_top');
        document.write(d);
        //-->
        </script>  
    ";
        return $content;
}

function km2_hook_themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$nav_pages = "";
	if($num) {
		$nav_start = ((($nav-1) * $max_nav)+1);
		$nav_end = (($nav) * $max_nav);
		$start = 1;
		$end = ceil($num/$max_nav);
		$nav_pages = "
            <table cellpadding=1 cellspacing=1>
            <tr>
        ";
		$nav_pages .= "<td> <a href=$url&page=1&nav=1><img align=absmiddle src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_start.gif border=0 /></a></td>";
		$nav_pages .= "<td>";
		$nav_pages .= ($start==$nav) ? "<img align=absmiddle src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_prev.gif border=0 /> &nbsp;" : "<a href=$url&page=".((($nav-2)*$max_nav)+1)."&nav=".($nav-1)."><img src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_prev.gif border=0 /></a>";
		$nav_pages .= "</td>";
		$nav_pages .= ($start==$nav) ? "<td>" : "<td> ... ";
		for($i=$nav_start;$i<=$nav_end;$i++) {
			if($i>$num){ break; };
			if ($i == $page) {
				$nav_pages .= "[$i] ";
			} else {
				$nav_pages .= "<a href=$url&page=$i&nav=$nav>$i</a> ";
			}
		}
		$nav_pages .= ($end==$nav) ? "</td>" : " ... </td>";
		$nav_pages .= "<td>";
		$nav_pages .= ($end==$nav) ? "<img align=absmiddle src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_next.gif border=0 />&nbsp;" : "<a href=$url&page=".(($nav*$max_nav)+1)."&nav=".($nav+1)."> <img align=absmiddle src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_next.gif border=0 /></a>";
		$nav_pages .= "</td>";
		$nav_pages .= "<td><a href=$url&page=$num&nav=$end> <img align=absmiddle src=".$core_config['http_path']['themes']."/".$core_config['module']['themes']."/images/icon_end.gif border=0 /> </a></td>";
		$nav_pages .= "
            </tr>
            </table>	
        ";
	}
	return $nav_pages;
}

?>