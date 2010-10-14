<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function themes_get_menu_tree($menus='') {
    global $arr_menu;
    if ($menus) {
        $arr_menu = $menus;
    }
    $menu_tree = themes_buildmenu($arr_menu);
    return $menu_tree;
}

function themes_buildmenu($arr_menu) {
    global $http_path;
    $content_tree = "";
    $tree_index = 1;
    $open = 0;
    foreach($arr_menu as $key=>$value) {
        if($tree_index==1){$open = 1;}else{$open = 0;};
        $content_tree .= "\t\t d.add($tree_index,0,'$key','','','','','',$open);\n";
        $tree_index++;
    }
    $tree_index_top = 1;
    foreach($arr_menu as $key=>$value) {
        foreach($value as $sub_key1=>$sub_value1) {
            $content_tree .= "\t\t d.add($tree_index,$tree_index_top,'".$sub_value1[1]."', '".$sub_value1[0]."', '', '');\n";
            $tree_index++;
        }
        $tree_index_top++;
    }
    $tree_index_top++;
    $content = "
        <script type=\"text/javascript\">
        <!--
        d = new dTree('d');
        d.add(0,-1,'<b>"._('Home')."</b>', '".$http_path['base']."', '', '_top');
        $content_tree		
        d.add($tree_index_top,0,'"._('Logout')."', 'index.php?app=menu&inc=logout', '', '_top');
        document.write(d);
        //-->
        </script>  
    ";
    return $content;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
    global $http_path, $themes_module;
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
        $nav_pages .= "<td> <a href=$url&page=1&nav=1><img align=absmiddle src=".$http_path['themes']."/".$themes_module."/images/icon_start.gif border=0 /></a></td>";
        $nav_pages .= "<td>";        
        $nav_pages .= ($start==$nav) ? "<img align=absmiddle src=".$http_path['themes']."/".$themes_module."/images/icon_prev.gif border=0 /> &nbsp;" : "<a href=$url&page=".((($nav-2)*$max_nav)+1)."&nav=".($nav-1)."><img src=".$http_path['themes']."/".$themes_module."/images/icon_prev.gif border=0 /></a>";
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
        $nav_pages .= ($end==$nav) ? "<img align=absmiddle src=".$http_path['themes']."/".$themes_module."/images/icon_next.gif border=0 />&nbsp;" : "<a href=$url&page=".(($nav*$max_nav)+1)."&nav=".($nav+1)."> <img align=absmiddle src=".$http_path['themes']."/".$themes_module."/images/icon_next.gif border=0 /></a>";
        $nav_pages .= "</td>";        
        $nav_pages .= "<td><a href=$url&page=$num&nav=$end> <img align=absmiddle src=".$http_path['themes']."/".$themes_module."/images/icon_end.gif border=0 /> </a></td>";
        $nav_pages .= "
            </tr>
            </table>	
        ";
    }
    return $nav_pages;
}

?>