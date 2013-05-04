<?php
defined('_SECURE_') or die('Forbidden');

function themes_get_menu_tree($menus='') {
	global $core_config;
	$menu_config = $core_config['menu'];
	if ($menus) {
		$menu_config = $menus;
	}
	$menu_tree = themes_buildmenu($menu_config);
	return $menu_tree;
}

function themes_buildmenu($menu_config) {
	global $core_config;
	$menu = '';
	if ($core_config['module']['themes']) {
		$menu = x_hook($core_config['module']['themes'],'themes_buildmenu',array($menu_config));
	}
	return $menu;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	$search = themes_search_session();
	if ($search['keyword']) {
		$search_url = '&search_keyword='.urlencode($search['keyword']);
	}
	if ($search['category']) {
		$search_url .= '&search_category='.urlencode($search['category']);
	}
	$url = $url.$search_url;
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

function themes_search($search_category=array(), $url='') {
	$ret['keyword'] = $_REQUEST['search_keyword'];
	$ret['url'] = ( trim($url) ? trim($url) : $_SERVER['REQUEST_URI'] );
	$ret['category'] = $_REQUEST['search_category'];
	$option_search_category = "<option value=\"\">"._('Search')."</option>";
	foreach ($search_category as $key => $val) {
		if ( $selected = ( $ret['category'] == $val ? 'selected' : '' ) ) {
			$ret['dba_keywords'] = array($val => '%'.$ret['keyword'].'%' );
		}
		$option_search_category .= "<option value=\"".$val."\" $selected>".ucfirst($key)."</option>";
		$tmp_dba_keywords[$val] = '%'.$ret['keyword'].'%';
	}
	if ((! $ret['category'] ) && $ret['keyword']) {
		$ret['dba_keywords'] = $tmp_dba_keywords;
	}
	$content = "
		<form action='".$ret['url']."' method='POST'>
		<table cellpadding='0' cellspacing='0' border='0'><tbody><tr>
			<td><select name='search_category'>".$option_search_category."</select></td>
			<td>&nbsp;:&nbsp;</td>
			<td><input type='text' name='search_keyword' value='".$ret['keyword']."' size='30' maxlength='30'><td></td>
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

function themes_button_back($url) {
	$content = "<input type=button class=button value=\""._('Back')."\" onClick=\"javascript:window.location.href='".$url."'\">";
	return $content;
}

function _b($url) {
	return themes_button_back($url);
}

function themes_link($url, $title='') {
	$c_title = ( $title ? $title : $url );
	$content = "<a href=\"".$url."\">".$c_title."</a>";
	return $content;
}

function _a($url, $title='') {
	return themes_link($url, $title);
}

function themes_button($url, $title) {
	$content = "<input type=button class=button value=\"".$title."\" onClick=\"javascript:window.location.href='".$url."'\" />";
	return $content;
}

function _button($url, $title) {
	return themes_button($url, $title);
}

?>