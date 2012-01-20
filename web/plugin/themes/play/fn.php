<?php

function theme_play_build_menu() {
  global $arr_menu, $username;
  $content = '<ul class="nav">';
  $i = 0;
	foreach ($arr_menu as $cat => $value) {
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
	  $content .= '<li><a href="index.php?app=page&op=auth_logout">Logout</a></li>';
	  $content .= "</ul>";
	  $content .= "</li>";
	  $content .= '</ul>';
  }
  return $content;
}
?>
