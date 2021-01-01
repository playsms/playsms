<?php
defined('_SECURE_') or die('Forbidden');

function default_hook_themes_apply($content) {
	global $core_config, $user_config;
	
	$themes_lang = strtolower(substr($user_config['language_module'], 0, 2));
	
	if ($themes_layout = trim($_SESSION['tmp']['themes']['layout'])) {
		$themes_layout = 'themes_layout_' . $themes_layout;
		unset($_SESSION['tmp']['themes']['layout']);
	} else {
		$themes_layout = 'themes_layout';
	}
	
	$tpl = array(
		'name' => $themes_layout,
		'vars' => array(
			'CONTENT' => $content,
			'HTTP_PATH_BASE' => $core_config['http_path']['base'],
			'HTTP_PATH_THEMES' => $core_config['http_path']['themes'],
			'THEMES_MODULE' => core_themes_get(),
			'THEMES_MENU_TREE' => themes_menu_tree(),
			'THEMES_SUBMENU' => themes_submenu(),
			'THEMES_LANG' => ($themes_lang ? $themes_lang : 'en'),
			'CREDIT_SHOW_URL' => _u('index.php?app=ws&op=credit'),
			'NAME' => $user_config['name'],
			'USERNAME' => $user_config['username'],
			'GRAVATAR' => $user_config['opt']['gravatar'],
			'LAYOUT_FOOTER' => $core_config['main']['layout_footer'],
			'Logout' => _('Logout') 
		),
		'ifs' => array(
			'valid' => auth_isvalid() 
		) 
	);
	$content = tpl_apply($tpl, array(
		'core_config',
		'user_config' 
	));
	
	return $content;
}

function default_hook_themes_submenu($content = '') {
	global $user_config;
	
	$separator = "&nbsp;&nbsp;&nbsp;";
	
	$logged_in = $user_config['username'];
	$tooltips_logged_in = _('Logged in as') . ' ' . $logged_in;
	
	$credit = core_display_credit(rate_getusercredit($user_config['username']));
	$tooltips_credit = _('Your credit');
	
	$ret = '<div>';
	$ret .= '<span class="playsms-icon fas fa-user" alt="' . $tooltips_logged_in . '" title="' . $tooltips_logged_in . '"></span>' . $logged_in;
	$ret .= $separator . '<span class="playsms-icon fas fa-credit-card" alt="' . $tooltips_credit . '" title="' . $tooltips_credit . '"></span><div id="submenu-credit-show">' . $credit . '</div>';
	
	if (auth_login_as_check()) {
		$ret .= $separator . _a('index.php?app=main&inc=core_auth&route=logout', _('return'));
	}
	
	$ret .= $content;
	$ret .= '</div>';
	
	return $ret;
}

function default_hook_themes_menu_tree($menu_config) {
	global $core_config, $user_config, $icon_config;
	
	$main_menu = "";
	foreach ($menu_config as $menu_title => $array_menu) {
		foreach ($array_menu as $sub_menu) {
			$sub_menu_url = $sub_menu[0];
			$sub_menu_title = $sub_menu[1];
			$sub_menu_index = (int) ($sub_menu[2] ? $sub_menu[2] : 10) + 100;
			
			// devider or valid entry
			if (($sub_menu_url == '#') && ($sub_menu_title == '-')) {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<div class='dropdown-divider'></div>";
			} else if ($sub_menu_url == '#') {
				$m[$sub_menu_index . '.' . $sub_menu_title] = "<div class='dropdown-item'>" . $sub_menu_title . "</div>";
			} else if ($sub_menu_url && $sub_menu_title) {
				if (acl_checkurl($sub_menu_url)) {
					$m[$sub_menu_index . '.' . $sub_menu_title] = "<a class='dropdown-item' href='" . _u($sub_menu_url) . "'>" . $sub_menu_title . "</a>";
				}
			}
		}
		
		if (count($m)) {
			$main_menu .= "
				<div class='nav-item dropdown'>
					<a href='#' data-toggle='dropdown' id='" . core_sanitize_alphanumeric($menu_title) . "' class='nav-item nav-link dropdown-toggle'>" . $menu_title . "</a>
					<div class='dropdown-menu' aria-labelledby='" . core_sanitize_alphanumeric($menu_title) . "'>";
			
			ksort($m);
			foreach ($m as $mm) {
				$main_menu .= $mm;
			}
			unset($m);
			
			$main_menu .= "</div>";
			$main_menu .= "</div>";
		}
	}
	
	$content = "
		<nav class='navbar navbar-expand-md navbar-dark fixed-top bg-dark' role='navigation'>
				<div class='container'>
					<div class='navbar-header'>
						<button type='button' class='navbar-toggler' data-toggle='collapse' data-target='#navbar-collapse'>
							<span class='navbar-toggler-icon'></span>
						</button>
						<a href='" . _u($core_config['main']['main_website_url']) . "' class='navbar-brand'>" . $core_config['main']['main_website_name'] . "</a>
					</div>
					<div id='navbar-collapse' class='navbar-collapse collapse justify-content-between'>
						<div class='navbar-nav'>
							<a class='nav-item nav-link' href='" . _u(_HTTP_PATH_BASE_) . "'>" . _('Home') . "</a>
							" . $main_menu . "
						</div>
						<div class='navbar-nav'>
							<a class='nav-item nav-link' href='" . _u('index.php?app=main&inc=core_auth&route=logout') . "'>" . $icon_config['logout'] . "</a>
						</div>
					</div>
				</div>
		</nav>
	";
	
	return $content;
}

function default_hook_themes_navbar($num, $nav, $max_nav, $url, $page) {
	global $core_config;
	
	$nav_pages = "";
	if ($num) {
		$nav_start = ((($nav - 1) * $max_nav) + 1);
		$nav_end = (($nav) * $max_nav);
		$start = 1;
		$end = ceil($num / $max_nav);
		
		$nav_pages = "<div class=playsms-nav-bar><ul class=pagination>";
		$nav_pages .= "<li class=page-item><a class=page-link href='" . _u($url . '&page=1&nav=1') . "'> << </a></li>";
		
		if ($nav == $start) {
			$nav_pages .= "";
		} else {
			$nav_pages .= "<li class=page-item><a class=page-link href='" . _u($url . '&page=' . ((($nav - 2) * $max_nav) + 1) . '&nav=' . ($nav - 1)) . "'> < </a></li>";
		}
		
		for ($i = $nav_start; $i <= $nav_end; $i++) {
			if ($i > $num) {
				break;
			}
			
			if ($i == $page) {
				$page_active = 'active';
			} else {
				$page_active = '';
			}
			
			$nav_pages .= "<li class='page-item " . $page_active . "'><a class=page-link href='" . _u($url . '&page=' . $i . '&nav=' . $nav) . "'>" . $i . "</a></li>";
		}
		
		if ($nav == $end) {
			$nav_pages .= "";
		} else {
			$nav_pages .= "<li class=page-item><a class=page-link href='" . _u($url . '&page=' . (($nav * $max_nav) + 1) . '&nav=' . ($nav + 1)) . "'> > </a></li>";
		}

		$nav_pages .= "<li class=page-item><a class=page-link href='" . _u($url . '&page=' . $num . '&nav=' . $end) . "'> >> </a></li>";
		$nav_pages .= "</ul></div>";
	}
	
	return $nav_pages;
}

function default_hook_themes_dialog($type, $message) {
	$modal_id = uniqid();

	$ret .= "<!-- Modal " . $modal_id . " -->";
	$ret .= "
		<div class='modal fade' id='dialog_box_" . $modal_id . "' tabindex='-1' role='dialog' aria-labelledby='dialog_box_title' aria-hidden='true'>
			<div class='modal-dialog' role='document'>
				<div class='modal-content'>
					<div class='modal-header bg-" . $type . "'>
						<h5 class='modal-title' id='dialog_box_title'>" . _('Information') . "</h5>
					</div>
					<div class='modal-body'>
						" . $message . "
					</div>
					<div class='modal-footer'>
						<button type='button' id='dialog_box_close_" . $modal_id . "' class='btn btn-primary' data-dismiss='modal'>" . _('Close') . "</button>
					</div>
				</div>
			</div>
		</div>

		<script type='text/javascript'>
			$(document).ready(function() {
				$('#dialog_box_" . $modal_id . "').modal('show');
				$('#dialog_box_" . $modal_id . "').on('shown.bs.modal', function(event) {
					$('#dialog_box_close_" . $modal_id . "').focus();
				});
			});
		</script>
		<!-- /Modal " . $modal_id . "-->";

	return $ret;
}

function default_hook_themes_dialog_confirmation($content, $url, $icon, $title, $form, $load, $nofooter) {
	$modal_id = uniqid();
	
	if ($form) {
		$action = "$('#" . $url . "').submit();";
	} else {
		$action = "window.location.href = '" . $url . "';";
	}
	
	if ($load) {
		$url_to_load = $content;
		$content_load_js = "$('#dialog_confirmation_box_content_" . $modal_id . "').load('" . $url_to_load . "');";
		$content = "";
	}
	
	if ($nofooter) {
		$modal_footer = "";
	} else {
		$modal_footer = ";
			<div class='modal-footer'>
				<button type='button' id='confirmation_button_no_" . $modal_id . "' class='btn btn-primary' data-dismiss='modal'>" . _('No') . "</button>
				<button type='button' id='confirmation_button_yes_" . $modal_id . "' class='btn btn-danger' data-dismiss='modal'>" . _('Yes') . "</button>
			</div>";
	}
	
	$ret .= "<!-- Modal " . $modal_id . "-->";
	$ret .= "<a href='#' data-toggle='modal' data-target='#dialog_confirmation_box_" . $modal_id . "' id='confirmation_icon_" . $modal_id . "' class='confirmation-icon'>" . $icon . "</a>";
	$ret .= "
		<div class='modal fade' id='dialog_confirmation_box_" . $modal_id . "' tabindex='-1' role='dialog' aria-labelledby='dialog_confirmation_box_title_" . $modal_id . "' aria-hidden='true'>
			<div class='modal-dialog' role='document'>
				<div class='modal-content'>
					<div class='modal-header bg-danger'>
						<h5 class='modal-title' id='dialog_confirmation_box_title_" . $modal_id . "'>" . $title . "</h5>
					</div>
					<div class='modal-body'>
						<div id='dialog_confirmation_box_content_" . $modal_id . "'>
							" . $content . "
						</div>
					</div>
					" . $modal_footer . "
				</div>
			</div>
		</div>
		
		<script type='text/javascript'>
			$(document).ready(function() {
				$('#dialog_confirmation_box_" . $modal_id . "').on('shown.bs.modal', function(event) {
					$('#confirmation_button_no_" . $modal_id . "').focus();
					$('#confirmation_button_yes_" . $modal_id . "').click(function() {
						" . $action . "
						return false;
					});
					" . $content_load_js . "
				});
			});
		</script>
		<!-- /Modal " . $modal_id . "-->";

	return $ret;
}
