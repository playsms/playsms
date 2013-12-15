<?php
defined('_SECURE_') or die('Forbidden');

function themes_apply($content) {
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(),'themes_apply',array($content));
	}
	return $ret;
}

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
		$menu = core_hook($core_config['module']['themes'],'themes_buildmenu',array($menu_config));
	}
	return $menu;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
	$search = themes_search_session();
	if ($search['keyword']) {
		$search_url = '&search_keyword='.urlencode($search['keyword']);
	}
	if ($search['category']) {
		$search_url .= '&search_category='.urlencode($search['category']);
	}
	$url = $url.$search_url;
	$nav_pages = '';
	if ($theme = core_themes_get()) {
		$nav_pages = core_hook($theme,'themes_navbar',array($num, $nav, $max_nav, $url, $page));
	}
	return $nav_pages;
}

function themes_nav($count, $url='') {
	$ret = false;
	$lines_per_page = 20;
	$max_nav = 5;
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
		<form action='".$ret['url']."' method=POST>
		"._CSRF_FORM_."
		<div class=search_box>
			<div class=search_box_select><select name='search_category' class=search_input_category>".$option_search_category."</select></div>
			<div class=search_box_input><input type='text' name='search_keyword' class=search_input_keyword value='".$ret['keyword']."' size=30 maxlength='30' onEnter='document.searchbar.submit();'></div>
		</div>
		</form>";
	$ret['form'] = $content;
	$_SESSION['tmp']['themes_search'] = $ret;
	return $ret;
}

function themes_search_session() {
	return $_SESSION['tmp']['themes_search'];
}

function themes_button_back($url) {

	// fixme anton - "Back" untranslated without this
	global $apps_path;
	if (function_exists('bindtextdomain')) {
		bindtextdomain('messages', $apps_path['plug'].'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}

	$content = themes_button($url, _('Back'), 'button_back');
	return $content;
}

function themes_link($url, $title='', $css_class="", $css_id="") {
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(),'themes_link',array($url, $title, $css_class, $css_id));
	}
	if (! $ret) {
		$c_title = ( $title ? $title : $url );
		$css_class = ( $css_class ? " class=\"".$css_class."\"" : '' );
		$css_id = ( $css_id ? " id=\"".$css_id."\"" : '' );
		$ret = "<a href=\"".$url."\"".$css_class.$css_id.">".$c_title."</a>";
	}
	return $ret;
}

function themes_button($url, $title, $css_class='', $css_id='') {
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(),'themes_button',array($url, $title, $css_class, $css_id));
	}
	if (! $ret) {
		$css_class = ( $css_class ? " ".$css_class : '' );
		$css_id = ( $css_id ? " id=\"".$css_id."\"" : '' );
		$ret = "<a href=# class=\"button".$css_class."\" ".$css_id."value=\"".$title."\" onClick=\"javascript:window.location.href='".$url."'\" />".$title."</a>";
	}
	return $ret;
}

function themes_hint($text) {
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(),'themes_hint',array($text));
	}
	if (! $ret) {
		$ret = "<i class='glyphicon glyphicon-info-sign playsms-tooltip' data-toggle=tooltip title='".$text."' rel=tooltip></i>";
	}
	return $content;
}

function themes_mandatory($text) {
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(),'themes_mandatory',array($text));
	}
	if (! $ret) {
		$ret = $text." <i class='glyphicon glyphicon-exclamation-sign playsms-mandatory' data-toggle=tooltip title='"._('This field is required')."' rel=tooltip></i>";
	}
	return $content;
}

/* define shortcuts */

function _a($url, $title='') {
	return themes_link($url, $title);
}

function _back($url) {
	return themes_button_back($url);
}

function _button($url, $title) {
	return themes_button($url, $title);
}

function _hint($text) {
	return themes_hint($text);
}

function _mandatory($text) {
	return themes_mandatory($text);
}
