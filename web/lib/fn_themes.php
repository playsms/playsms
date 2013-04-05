<?php
defined('_SECURE_') or die('Forbidden');

function themes_get_menu_tree($menus='') {
	global $core_config;
	$arr_menu = $core_config['menu'];
	if ($menus) {
		$arr_menu = $menus;
	}
	$menu_tree = themes_buildmenu($arr_menu);
	return $menu_tree;
}

function themes_buildmenu($arr_menu) {
	global $core_config;
	$menu = '';
	if ($core_config['module']['themes']) {
		$menu = x_hook($core_config['module']['themes'],'themes_buildmenu',array($arr_menu));
	}
	return $menu;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$search = themes_search_session();
	$url = $url.'&'.$search['param'].'='.urlencode($search['keyword']);
	$nav_pages = '';
	if ($core_config['module']['themes']) {
		$nav_pages = x_hook($core_config['module']['themes'],'themes_navbar',array($num, $nav, $max_nav, $url, $page));
	}
	return $nav_pages;
}

function themes_nav($count, $url='') {
	$ret = false;
	$lines_per_page = 30;
	$max_nav = 10;
	$num = ceil($count / $lines_per_page);
	$nav = ( $_REQUEST['nav'] ? $_REQUEST['nav'] : 1 );
	$page = ( $_REQUEST['page'] ? $_REQUEST['page'] : 1 );
	$url = ( trim($url) ? trim($url) : $_SERVER['REQUEST_URI'] );
	if ($ret['form'] = themes_navbar($num, $nav, $max_nav, $url, $page)) {
		$ret['limit'] = $lines_per_page;
		$ret['offset'] = ($page - 1) * $lines_per_page;
		$ret['top'] = ($count - ($lines_per_page * ($page - 1))) + 1;
		$ret['nav'] = $nav;
		$ret['page'] = $page;
		$ret['url'] = $url;
	}
	$_SESSION['tmp']['themes_nav'] = $ret;
	return $ret;
}

function themes_nav_session() {
	return $_SESSION['tmp']['themes_nav'];
}

function themes_search() {
	$ret['param'] = 'search_keyword';
	$ret['keyword'] = themes_search_keyword();
	$ret['url'] = ( trim($var['url']) ? trim($var['url']) : $_SERVER['REQUEST_URI'] );
	$content = "
		<form action='".$ret['url']."' method='POST'>
		<table cellpadding='0' cellspacing='0' border='0'><tbody><tr>
			<td>"._('Search')."</td>
			<td>&nbsp;:&nbsp;</td>
			<td><input type='text' name='".$ret['param']."' value='".$ret['keyword']."' size='30' maxlength='30'><td></td>
			<td><input type='submit' value='"._('Go')."' class='button'></td>
		</tr></tbody></table>
		</form>";
	$ret['form'] = $content;
	$_SESSION['tmp']['themes_search'] = $ret;
	return $ret;
}

function themes_search_session() {
	return $_SESSION['tmp']['themes_search'];
}

function themes_search_keyword() {
	return $_REQUEST['search_keyword'];
}

?>