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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

include 'init.php';
include $core_config['apps_path']['libs'].'/function.php';


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
			$fn = $core_config['apps_path']['incs'].'/app/menu.php';
			if (file_exists($fn)) {
				include $fn;
			}
			break;
		case 'ws':
		case 'webservice':
		case 'webservices':
			// $app=webservices to access webservices, replacement of input.php and output.php
			$fn = $core_config['apps_path']['incs'].'/app/webservices.php';
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
				bindtextdomain('messages', $core_config['apps_path']['plug'].'/'.$cat.'/'.$plugin.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			core_hook($plugin,'call',array($_REQUEST));
			break;
		case 'page':
			// $app=page to access a page inside themes
			// by default this is used for displaying 'forgot password' page and 'register an account' page
			// login, logout, register, forgot password, noaccess
			if (function_exists('bindtextdomain')) {
				bindtextdomain('messages', $core_config['apps_path']['themes'].'/'.$themes_module.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
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
if (auth_isvalid()) {
	ob_end_clean();
	header("Location: index.php?app=menu&inc=".$core_config['main']['default_inc']."&op=".$core_config['main']['default_op']);
	exit();
} else {
	if (function_exists('bindtextdomain')) {
		bindtextdomain('messages', $core_config['apps_path']['themes'].'/'.$themes_module.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $core_config['apps_path']['themes'].'/'.$core_config['module']['themes'].'/page_login.php';
}

unset($_SESSION['error_string']);
