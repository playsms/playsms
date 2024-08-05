<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

/**
 * Apply theme to content
 * 
 * @param string $content whole content
 * @return string
 */
function common_hook_themes_apply($content = '')
{
	global $core_config, $user_config;

	$user_config['language_module'] = isset($user_config['language_module']) ? $user_config['language_module'] : '';
	$themes_lang = strtolower(substr($user_config['language_module'], 0, 2));

	$themes_layout = isset($_REQUEST['_themes_layout_']) && trim($_REQUEST['_themes_layout_']) ? trim($_REQUEST['_themes_layout_']) : '';
	$themes_layout = $themes_layout ? 'themes_layout_' . $themes_layout : 'themes_layout';

	$tpl = [
		'name' => $themes_layout,
		'vars' => [
			'CONTENT' => $content,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_buildmenu(),
			'THEMES_SUBMENU' => themes_submenu(),
			'THEMES_LANG' => $themes_lang ? $themes_lang : 'en',
			'CREDIT_SHOW_URL' => _u('index.php?app=ws&op=credit'),
			'NAME' => $user_config['name'],
			'USERNAME' => $user_config['username'],
			'GRAVATAR' => $user_config['opt']['gravatar'],
			'LAYOUT_FOOTER' => $core_config['main']['layout_footer'],
			'Logout' => _('Logout')
		],
		'ifs' => [
			'valid' => auth_isvalid()
		],
	];
	$content = tpl_apply(
		$tpl,
		[
			'core_config',
			'user_config'
		]
	);

	return $content;
}

/**
 * Get submenus
 * 
 * @param string $content submenu content
 * @return string
 */
function common_hook_themes_submenu($content = '')
{
	global $user_config;

	$separator = "&nbsp;&nbsp;&nbsp;";

	$logged_in = $user_config['username'];
	$tooltips_logged_in = _('Logged in as') . ' ' . $logged_in;

	$credit = core_display_credit(rate_getusercredit($user_config['username']));
	$tooltips_credit = _('Your credit');

	$ret = '<div>';
	$ret .= '<span class="playsms-icon glyphicon glyphicon-user" alt="' . $tooltips_logged_in . '" title="' . $tooltips_logged_in . '"></span>' . $logged_in;
	$ret .= $separator . '<span class="playsms-icon glyphicon glyphicon-credit-card" alt="' . $tooltips_credit . '" title="' . $tooltips_credit . '"></span><div id="submenu-credit-show">' . $credit . '</div>';

	if (auth_login_as_check()) {
		$ret .= $separator . _a('index.php?app=main&inc=core_auth&route=logout', _('return'));
	}

	$ret .= $content;
	$ret .= '</div>';

	return $ret;
}

/**
 * Get menu tree
 * 
 * @param array $menus
 * @return string
 */
function common_hook_themes_buildmenu($menus = [])
{
	global $core_config, $user_config, $icon_config, $menu_config;

	$menus = is_array($menus) && $menus ? $menus : $menu_config;

	$m = [];
	$main_menu = '';
	foreach ( $menus as $menu_title => $array_menu ) {
		foreach ( $array_menu as $sub_menu ) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = (int) ($sub_menu[2] ? $sub_menu[2] : 10) + 100;

			// devider or valid entry
			if (($sub_menu_url == '#') && ($sub_menu_title == '-')) {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li class=\"divider\"></li>";
			} else if ($sub_menu_url == '#') {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<li>" . $sub_menu_title . "</li>";
			} else if ($sub_menu_url && $sub_menu_title) {
				if (acl_checkurl($sub_menu_url)) {
					$m[$sub_menu_index . '.' . $sub_menu_title] = "<li><a href='" . _u($sub_menu_url) . "'>" . $sub_menu_title . "</a></li>";
				}
			}
		}

		if (is_array($m) && count($m)) {
			$main_menu .= "<li class='dropdown'><a href='#' data-toggle='dropdown' class='dropdown-toggle'>" . $menu_title . " <b class='caret'></b></a>";
			$main_menu .= "<ul class='dropdown-menu'>";

			ksort($m);
			foreach ( $m as $mm ) {
				$main_menu .= $mm;
			}
			unset($m);

			$main_menu .= "</ul>";
			$main_menu .= "</li>";
		}
	}

	$content = "
		<nav class='navbar navbar-inverse navbar-fixed-top' role='navigation'>
			<div class='navbar-inner'>
				<div class='container'>
					<div class='navbar-header'>
						<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
							<span class='icon-bar'></span>
							<span class='icon-bar'></span>
							<span class='icon-bar'></span>
						</button>
						<a href='" . _u($core_config['main']['main_website_url']) . "' class='brand navbar-brand'>" . $core_config['main']['main_website_name'] . "</a>
					</div>
					<div class='navbar-collapse collapse'>
						<ul class='nav navbar-nav'>
							<li class='active'><a href='" . _u(_HTTP_PATH_BASE_) . "'>" . _('Home') . "</a></li>
							" . $main_menu . "
						</ul>
						<ul class='nav navbar-nav navbar-right'>
							<li><a href='" . _u('index.php?app=main&inc=core_auth&route=logout') . "'>" . $icon_config['logout'] . "</a></li>
						</ul>
					</div>
				</div>
			</div>
		</nav>
	";

	return $content;
}

/**
 * Get navigation bar
 * 
 * @param int $num number of item per page
 * @param int $nav active nav bar
 * @param int $max_nav maximum visible page on a nav bar
 * @param string $url base URL
 * @param int $page active page
 * @return string
 */
function common_hook_themes_navbar($num, $nav, $max_nav, $url, $page)
{
	global $core_config;

	$search = themes_search_session();

	if ($search['keyword']) {
		$search_url = '&search_keyword=' . urlencode($search['keyword']);
	}
	if ($search['category']) {
		$search_url .= '&search_category=' . urlencode($search['category']);
	}
	$url = $url . $search_url;

	$nav_pages = '';

	if ($num) {
		$nav_start = (($nav - 1) * $max_nav) + 1;
		$nav_end = $nav * $max_nav;
		$start = 1;
		$end = ceil($num / $max_nav);
		$nav_pages = "<div class=playsms-nav-bar>";
		$nav_pages .= "<a href='" . _u($url . '&page=1&nav=1') . "'> << </a>";
		$nav_pages .= ($start == $nav) ? " < " : "<a href='" . _u($url . '&page=' . (($nav - 2) * $max_nav) + 1 . '&nav=' . $nav - 1) . "'> < </a>";
		$nav_pages .= ($start == $nav) ? '' : " ... ";
		for ($i = $nav_start; $i <= $nav_end; $i++) {
			if ($i > $num) {
				break;
			}
			if ($i == $page) {
				$nav_pages .= "<u>$i</u> ";
			} else {
				$nav_pages .= "<a href='" . _u($url . '&page=' . $i . '&nav=' . $nav) . "'>" . $i . "</a> ";
			}
		}
		$nav_pages .= ($end == $nav) ? '' : "..";
		$nav_pages .= ($end == $nav) ? " > " : "<a href='" . _u($url . '&page=' . ($nav * $max_nav) + 1 . '&nav=' . $nav + 1) . "'> > </a>";
		$nav_pages .= "<a href='" . _u($url . '&page=' . $num . '&nav=' . $end) . "'> >> </a>";
		$nav_pages .= "</div>";
	}

	return $nav_pages;
}

/**
 * Get search form
 * 
 * @param array $search_category search categories
 * @param string $url base URL
 * @param array $keyword_converter pair keywords and their modifier functions
 * @return array
 */
function common_hook_themes_search($search_category = [], $url = '', $keyword_converter = [])
{
	global $core_config;

	$ret['keyword'] = $_REQUEST['search_keyword'];
	$ret['url'] = trim($url) ? trim($url) : $_SERVER['REQUEST_URI'];
	$ret['category'] = $_REQUEST['search_category'];
	$option_search_category = "<option value=''>" . _('Search') . "</option>";
	foreach ( $search_category as $key => $val ) {

		$c_keyword = $ret['keyword'];

		if ($c_function = $keyword_converter[$val]) {
			if (function_exists($c_function)) {
				$c_keyword = $c_function($ret['keyword']);
			}
		}

		if ($selected = ($ret['category'] == $val ? 'selected' : '') && $c_keyword) {
			$ret['dba_keywords'] = [
				$val => '%' . $c_keyword . '%'
			];
		}

		$option_search_category .= "<option value='" . $val . "' $selected>" . ucfirst($key) . "</option>";

		if ($c_keyword) {
			$tmp_dba_keywords[$val] = '%' . $c_keyword . '%';
		}
	}

	if ((!$ret['category']) && $ret['keyword']) {
		$ret['dba_keywords'] = $tmp_dba_keywords;
	}

	$content = "
		<form action='" . $ret['url'] . "' method=POST>
		" . _CSRF_FORM_ . "
		<div class=search_box>
			<div class=search_box_select><select name='search_category' class=search_input_category>" . $option_search_category . "</select></div>
			<div class=search_box_input><input type='text' name='search_keyword' class=search_input_keyword value='" . $ret['keyword'] . "' maxlength='30' onEnter='document.searchbar.submit();'></div>
		</div>
		</form>";
	$ret['form'] = $content;
	$_SESSION['tmp']['themes_search'] = $ret;

	return $ret;
}

/**
 * Build link
 * 
 * @param string $url base URL
 * @param string $title link title
 * @param string $css_class link CSS class name
 * @param string $css_id link CSS ID
 * @return string
 */
function common_hook_themes_link($url, $title = '', $css_class = '', $css_id = '')
{
	$url = _u($url);
	$c_title = $title ? $title : $url;
	$css_class = $css_class ? " class='" . $css_class . "'" : '';
	$css_id = $css_id ? " id='" . $css_id . "'" : '';
	$ret = "<a href='" . _u($url) . "'" . $css_class . $css_id . ">" . $c_title . "</a>";

	return $ret;
}

/**
 * Modify URL
 * 
 * @param string $url URL
 * @return string
 */
function common_hook_themes_url($url)
{
	$ret = strip_tags(stripcslashes(htmlspecialchars_decode(urldecode($url))));

	return $ret;
}

/**
 * Build back button
 * 
 * @param string $url base URL
 * @return string
 */
function common_hook_themes_button_back($url)
{
	$ret = themes_button($url, _('Back'), 'button_back');

	return $ret;
}

/**
 * Build button
 * 
 * @param string $url base URL
 * @param string $title button title
 * @param string $css_class button CSS class name
 * @param string $css_id button CSS ID
 * @return string
 */
function common_hook_themes_button($url, $title, $css_class = '', $css_id = '')
{
	$css_class = $css_class ? " " . $css_class : '';
	$css_id = $css_id ? " id='" . $css_id . "'" : '';
	$ret = "<a href=# class='button" . $css_class . "' " . $css_id . "value='" . $title . "' onClick=\"javascript:window.location.href='" . _u($url) . "'\"' />" . $title . "</a>";

	return $ret;
}

/**
 * Build hint label
 * 
 * @param string $text hint text
 * @return string
 */
function common_hook_themes_hint($text)
{
	$ret = "<i class='glyphicon glyphicon-info-sign playsms-tooltip' data-toggle='tooltip' title='" . _display($text) . "' rel='tooltip'></i>";

	return $ret;
}

/**
 * Build mandatory label
 * 
 * @param string $text mandatory text
 * @return string
 */
function common_hook_themes_mandatory($text)
{
	$ret = $text . " <i class='glyphicon glyphicon-exclamation-sign playsms-mandatory' data-toggle='tooltip' title='" . _('This field is required') . "' rel='tooltip'></i>";

	return $ret;
}

/**
 * Generate options for select HTML tag
 *
 * @param array $options select options
 * @param string $selected selected option
 * @return string options for select HTML tag
 */
function common_hook_themes_select_options($options = [], $selected = '')
{
	$ret = '';

	foreach ( $options as $key => $val ) {
		if (is_int($key)) {
			$key = $val;
		}
		$c_selected = ($val == $selected ? 'selected' : '');
		$ret .= '<option value="' . $val . '" ' . $c_selected . '>' . $key . '</option>';
	}

	return $ret;
}

/**
 * Generate select HTML tag
 *
 * @param string $name select tag name
 * @param array $options select options
 * @param string $selected selected option
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string select HTML tag
 */
function common_hook_themes_select($name, $options = [], $selected = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$params = '';
	$select_options = themes_select_options($options, $selected);
	if (is_array($tag_params)) {
		foreach ( $tag_params as $key => $val ) {
			$params .= ' ' . $key . '="' . $val . '"';
		}
	}

	$css_id = trim($css_id) ? trim($css_id) : 'playsms-select-' . core_sanitize_alphanumeric($name);
	$placeholder = isset($tag_params['placeholder']) ? $tag_params['placeholder'] : _('Please select');
	$width = isset($tag_params['width']) ? $tag_params['width'] : 'resolve';

	$js = '
			<script language="javascript" type="text/javascript">
				$(document).ready(function() {
					$("#' . $css_id . '").select2({
						placeholder: "' . $placeholder . '",
						width: "' . $width . '",
						separator: [\',\'],
						tokenSeparators: [\',\'],
					});
				});
			</script>
		';

	$ret = $js . PHP_EOL . '<select name="' . $name . '" id="' . $css_id . '" class="playsms-select ' . $css_class . '" ' . $params . '>' . $select_options . '</select>';

	return $ret;
}

/**
 * Generate select HTML tag for yes-no or enabled-disabled type of options
 *
 * @param string $name tag name
 * @param boolean $selected true if yes/enabled
 * @param string $yes 'Yes' or 'Enabled' option
 * @param string $no 'No' or 'Disabled' option
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string select HTML tag
 */
function common_hook_themes_select_yesno($name, $selected, $yes = '', $no = '', $tag_params = [], $css_id = '', $css_class = '')
{
	$yes = $yes ? $yes : _('yes');
	$no = $no ? $no : _('no');
	$options = [
		$yes => 1,
		$no => 0
	];

	$ret = themes_select($name, $options, $selected, $tag_params, $css_id, $css_class);

	return $ret;
}

/**
 * Generate HTML input tag
 *
 * @param string $type input type
 * @param string $name input name
 * @param string $value input default value
 * @param array $tag_params additional input tag parameters
 * @param string $css_id CSS ID
 * @param string $css_class CSS class name
 * @return string HTML input tag
 */
function common_hook_themes_input($type = 'text', $name = '', $value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	if (is_array($tag_params)) {
		foreach ( $tag_params as $key => $val ) {
			if (is_numeric($key)) {
				$params .= ' ' . $val;
			} else {
				$params .= ' ' . $key . '="' . $val . '"';
			}
		}
	} else {
		$params = $tag_params;
	}

	$ret = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" id="' . $css_id . '" class="playsms-input ' . $css_class . '" ' . $params . '>';

	return $ret;
}

/**
 * Popup compose message form
 *
 * @param string $to default destination
 * @param string $message default or previous message
 * @param string $return_url if empty this would be $_SERVER['REQUEST_URI']
 * @param string $button_icon if empty this would be a reply icon
 * @return string Javascript PopupSendsms()
 */
function common_hook_themes_popup_sendsms($to = '', $message = '', $return_url = '', $button_icon = '')
{
	global $icon_config;

	$return_url = $return_url ? $return_url : $_SERVER['REQUEST_URI'];
	$button_icon = $button_icon ? $button_icon : $icon_config['reply'];

	$ret = "<a href=# onClick=\"javascript:PopupSendSms('" . urlencode($to) . "', '" . urlencode($message) . "', '" . _('Compose message') . "', '" . urlencode($return_url) . "');\">" . $button_icon . "</a>";

	return $ret;
}

/**
 * Display error string from function parameter or session
 *
 * @param array $content
 *        Array of contents of dialog, format: $content['dialog'][<Type_of_dialog>]
 *        Type of dialog: default, info, primary, success, warning, danger
 * @param string $title dialog title
 * @return string HTML string of error strings
 */
function common_hook_themes_dialog($content = [], $title = '')
{
	$ret = '';

	if (is_array($content) && (count($content) > 0) && $content['dialog']) {
		$contents = $content['dialog'];
	} else {
		if ($_SESSION['dialog']) {
			$contents = $_SESSION['dialog'];
		} else {
			if (is_array($_SESSION['error_string'])) {
				$contents['info'] = $_SESSION['error_string'];
			} else {
				$contents['info'][] = $_SESSION['error_string'];
			}
		}
	}

	foreach ( $contents as $type => $data ) {
		$dialog_message = '';
		$continue = false;

		foreach ( $data as $texts ) {
			if (is_array($texts) && count($texts) > 0) {
				foreach ( $texts as $text ) {
					$dialog_message .= trim($text) ? _display(trim($text)) . '<br />' : '';
				}
				$continue = true;
			} elseif (trim($texts)) {
				$dialog_message = _display(trim($texts));
				$continue = true;
			}
		}

		if ($continue) {
			switch (strtoupper(trim($type))) {
				case 'DEFAULT':
				case 'INFO':
				case 'PRIMARY':
				case 'SUCCESS':
				case 'WARNING':
				case 'DANGER':
					$dialog_type = strtoupper(trim($type));
					break;
				default:
					$dialog_type = 'PRIMARY';
			}

			$dialog_title = $title ? $title : _('Information');

			$ret .= "
				<script type='text/javascript'>
					BootstrapDialog.show({
						type: BootstrapDialog.TYPE_" . $dialog_type . ",
						title: '" . $dialog_title . "',
						message: '" . $dialog_message . "',
						closable: true,
						draggable: true
					})
				</script>
			";
		}
	}

	return $ret;
}

/**
 * Generate select single user
 * 
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function common_hook_themes_select_users_single($select_field_name, $selected_value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	global $user_config;

	if (!is_array($selected_value)) {
		$selected_value = [
			$selected_value
		];
	}

	if (auth_isadmin()) {
		$admins = user_getallwithstatus(2);
		$users = user_getallwithstatus(3);
	}
	$subusers = user_getsubuserbyuid($user_config['uid']);

	$option_user = '<option value="0">' . _('Select users') . '</option>';
	if (count($admins) > 0) {
		$option_user .= '<optgroup label="' . _('Administrators') . '">';

		foreach ( $admins as $admin ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($admin['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $admin['uid'] . '" ' . $selected . '>' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($users) > 0) {

		$option_user .= '<optgroup label="' . _('Users') . '">';

		foreach ( $users as $user ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($user['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $user['uid'] . '" ' . $selected . '>' . $user['name'] . ' (' . $user['username'] . ') - ' . _('User') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($subusers) > 0) {

		$option_user .= '<optgroup label="' . _('Subusers') . '">';

		foreach ( $subusers as $subuser ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($subuser['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $subuser['uid'] . '"' . $selected . '>' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	$css_id = trim($css_id) ? trim($css_id) : 'playsms-select-users-single-' . core_sanitize_alphanumeric($select_field_name);

	if (is_array($tag_params)) {
		foreach ( $tag_params as $key => $val ) {
			$params .= ' ' . $key . '="' . $val . '"';
		}
	}

	$placeholder = $tag_params['placeholder'] ? $tag_params['placeholder'] : _('Select users');
	$width = $tag_params['width'] ? $tag_params['width'] : 'resolve';

	$js = '
		<script language="javascript" type="text/javascript">
			$(document).ready(function() {
				$("#' . $css_id . '").select2({
					placeholder: "' . $placeholder . '",
					width: "' . $width . '",
					separator: [\',\'],
					tokenSeparators: [\',\'],
				});
			});
		</script>
	';

	$ret = $js . PHP_EOL . '<select name="' . $select_field_name . '" id="' . $css_id . '" class="playsms-select ' . $css_class . '" ' . $params . '>' . $option_user . '</select>';

	return $ret;
}

/**
 * Generate select multiple users
 * 
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function common_hook_themes_select_users_multi($select_field_name, $selected_value = [], $tag_params = [], $css_id = '', $css_class = '')
{
	$tag_params['multiple'] = 'multiple';
	$ret = themes_select_users_single($select_field_name . '[]', $selected_value, $tag_params, $css_id, $css_class);

	return $ret;
}

/**
 * Generate select user with user level
 * 
 * @param int $status user level
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function common_hook_themes_select_account_level_single($status, $select_field_name, $selected_value = '', $tag_params = [], $css_id = '', $css_class = '')
{
	global $user_config;

	$admins = [];
	$users = [];
	$subusers = [];

	if (!is_array($selected_value)) {
		$selected_value = [
			$selected_value
		];
	}

	if ($status == 2) {
		$admins = user_getallwithstatus(2);
	} else if ($status == 3) {
		$users = user_getallwithstatus(3);
	} else {
		$subusers = user_getsubuserbyuid($user_config['uid']);
	}

	$option_user = '<option value="0">' . _('Select users') . '</option>';
	if (count($admins) > 0) {
		$option_user .= '<optgroup label="' . _('Administrators') . '">';

		foreach ( $admins as $admin ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($admin['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $admin['uid'] . '" ' . $selected . '>' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($users) > 0) {

		$option_user .= '<optgroup label="' . _('Users') . '">';

		foreach ( $users as $user ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($user['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $user['uid'] . '" ' . $selected . '>' . $user['name'] . ' (' . $user['username'] . ') - ' . _('User') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($subusers) > 0) {

		$option_user .= '<optgroup label="' . _('Subusers') . '">';

		foreach ( $subusers as $subuser ) {
			$selected = '';
			foreach ( $selected_value as $sv ) {
				if ($subuser['uid'] == $sv) {
					$selected = 'selected';
					break;
				}
			}
			$option_user .= '<option value="' . $subuser['uid'] . '"' . $selected . '>' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	$css_id = (trim($css_id) ? trim($css_id) : 'playsms-select-account-level-' . core_sanitize_alphanumeric($select_field_name));

	if (is_array($tag_params)) {
		foreach ( $tag_params as $key => $val ) {
			$params .= ' ' . $key . '="' . $val . '"';
		}
	}

	$placeholder = ($tag_params['placeholder'] ? $tag_params['placeholder'] : _('Select users'));
	$width = ($tag_params['width'] ? $tag_params['width'] : 'resolve');

	$js = '
		<script language="javascript" type="text/javascript">
			$(document).ready(function() {
				$("#' . $css_id . '").select2({
					placeholder: "' . $placeholder . '",
					width: "' . $width . '",
					separator: [\',\'],
					tokenSeparators: [\',\'],
				});
			});
		</script>
	';

	$ret = $js . PHP_EOL . '<select name="' . $select_field_name . '" id="' . $css_id . '" class="playsms-select ' . $css_class . '" ' . $params . '>' . $option_user . '</select>';

	return $ret;
}

/**
 * Generate select multiple users with user level
 * 
 * @param int $status user level
 * @param string $select_field_name field name
 * @param string $selected_value field value
 * @param array $tag_params select tag parameters
 * @param string $css_id select tag CSS ID
 * @param string $css_class select tag CSS class name
 * @return string
 */
function common_hook_themes_select_account_level_multi($status, $select_field_name, $selected_value = [], $tag_params = [], $css_id = '', $css_class = '')
{
	$tag_params['multiple'] = 'multiple';
	$ret = themes_select_account_level_single($status, $select_field_name . '[]', $selected_value, $tag_params, $css_id, $css_class);

	return $ret;
}