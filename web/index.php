<?php
include 'init.php';
include $apps_path['libs'].'/function.php';


// fixme anton
// load app extensions from index, such as menu, webservices and callbacks
// using $app you can do up to load another application from playSMS if you need to
// but the point is to make a single gate into playSMS, that is through index.php
$app = $_REQUEST['app'];
if (isset($app)) {
	switch ($app) {
		case 'mn':
		case 'menu':
			// $app=menu to access menus, replacement of direct access to menu.php
			logger_audit();
			$fn = $apps_path['incs'].'/app/menu.php';
			if (file_exists($fn)) {
				include $fn;
			}
			break;
		case 'ws':
		case 'webservice':
		case 'webservices':
			// $app=webservices to access webservices, replacement of input.php and output.php
			$fn = $apps_path['incs'].'/app/webservices.php';
			if (file_exists($fn)) {
				include $fn;
			}
			break;
		case 'call':
			// $app=call to access subroutine in a plugin
			// can be used to replace callback.php in clickatell or dlr.php and geturl.php in kannel
			// plugin's category such as feature, tools or gateway
			$cat = trim($_REQUEST['cat']);
			// plugin's name such as kannel, sms_board or sms_subscribe
			$plugin = trim($_REQUEST['plugin']);
			if (function_exists('bindtextdomain')) {
				bindtextdomain('messages', $apps_path['plug'].'/'.$cat.'/'.$plugin.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			x_hook($plugin,'call',array($_REQUEST));
			break;
		case 'page':
			// $app=page to access a page inside themes
			// by default this is used for displaying 'forgot password' page and 'register an account' page
			// login, logout, register, forgot password, noaccess
			logger_audit();
			switch ($op) {
				case 'auth_login':
				case 'auth_logout':
				case 'auth_forgot':
				case 'auth_register':
					if (function_exists($op)) {
						call_user_func($op);
					}
					break;
				default:
					// error messages
					$error_content = '';
					if ($err = $_SESSION['error_string']) {
						$error_content = "<div class=error_string>$err</div>";
					}
					// load page
					$fn = $core_config['apps_path']['themes'].'/'.$core_config['module']['themes'].'/page_'.$inc.'.php';
					if (file_exists($fn)) {
						include $fn;
					}
			}
	}
	unset($_SESSION['error_string']);
	exit();
}

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

// frontpage
if (valid()) {
	$core_config['default_include'] = ( empty($core_config['default_include']) ? $core_config['default_include'] = 'page_welcome' : $core_config['default_include'] );
	$core_config['default_option'] = ( empty($core_config['default_option']) ? $core_config['default_option'] = 'page_welcome' : $core_config['default_option'] );
	ob_end_clean();
	header("Location: index.php?app=menu&inc=".$core_config['default_include']."&op=".$core_config['default_option']);
	exit();
} else {
	if (function_exists('bindtextdomain')) {
		bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $core_config['apps_path']['themes'].'/'.$core_config['module']['themes'].'/page_login.php';
}

unset($_SESSION['error_string']);
?>