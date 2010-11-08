<?php

/*

// custom functions for this theme
function themes_work2_set_title($title) {
}

function themes_work2_get_title() {
}

*/

function themes_work2_get_menu_dropdown($menus='') {
    global $arr_menu;
    if ($menus) {
        $arr_menu = $menus;
    }
    $menu_tree = themes_work2_buildmenu($arr_menu);
    return $menu_tree;
}

function themes_work2_buildmenu($arr_menu) {
    global $http_path;
    $i = 0;
    $content = "<table><tr><td>";
    $content .= "<div id='dropdown_attach_menu_parent_".$i."' class='dropdown_attach'><a href='".$http_path['base']."'>Home</a></div>";
    $content .= "<div id='dropdown_attach_menu_child_".$i."'>";
    $content .= "<a class='dropdown_attach' href='index.php?app=page&op=auth_logout'>Logout</a>";
    $content .= "</div>";
    $content .= "<script type='text/javascript'>at_attach('dropdown_attach_menu_parent_".$i."', 'dropdown_attach_menu_child_".$i."', 'hover', 'y', 'pointer');</script>";
    $content .= "</td>";
    foreach ($arr_menu as $cat => $value) {
        $i++;
        $content .= "<td>";
	$content .= "<div id='dropdown_attach_menu_parent_".$i."' class='dropdown_attach'><a href='#'>".$cat."</a></div>";
	$content .= "<div id='dropdown_attach_menu_child_".$i."'>";
    	foreach ($value as $sub_key => $menu) {
	    $content .= "<a class='dropdown_attach' href='".$menu[0]."'>".$menu[1]."</a>";
    	}
    	$content .= "</div>";
    	$content .= "<script type='text/javascript'>at_attach('dropdown_attach_menu_parent_".$i."', 'dropdown_attach_menu_child_".$i."', 'hover', 'y', 'pointer');</script>";
    	$content .= "</td>";
    }
    $content .= "</tr></table>";
    return $content;
}

?>