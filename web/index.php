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
include 'init.php';
include $core_config['apps_path']['libs'] . '/function.php';

// fixme anton
// load app extensions from index, such as menu, webservices and callbacks
// using _APP_ you can even load another application from playSMS if you need to
// but the point is to make a single gate into playSMS, that is through index.php
if (_APP_) {
	switch (_APP_) {
		case 'menu':
		case 'main':
			// _APP_=main to access main application
			logger_audit();
			$fn = $core_config['apps_path']['incs'] . '/app/main.php';
			if (file_exists($fn)) {
				include $fn;
			}
			break;
		case 'ws':
		case 'webservice':
		case 'webservices':
			// _APP_=webservices to access webservices, replacement of input.php and output.php
			$fn = $core_config['apps_path']['incs'] . '/app/webservices.php';
			if (file_exists($fn)) {
				include $fn;
			}
			break;
		case 'call':
			// _APP_=call to access subroutine in a plugin
			// can be used to replace callback.php in clickatell or dlr.php and geturl.php in kannel
			if (_CAT_ && _PLUGIN_) {
				if (function_exists('bindtextdomain')) {
					bindtextdomain('messages', $core_config['apps_path']['plug'] . '/' . _CAT_ . '/' . _PLUGIN_ . '/language/');
					bind_textdomain_codeset('messages', 'UTF-8');
					textdomain('messages');
				}
				core_hook(_PLUGIN_, 'call', array(
					$_REQUEST 
				));
			}
			break;
		case 'page':
			// _APP_=page to access a page inside themes
			// by default this is used for displaying 'forgot password' page and 'register an account' page
			// login, logout, register, forgot password, noaccess
			logger_audit();
			if (_INC_) {
				$fn = $core_config['apps_path']['themes'] . '/' . core_themes_get() . '/page_' . _INC_ . '.php';
				if (file_exists($fn)) {
					if (function_exists('bindtextdomain')) {
						bindtextdomain('messages', $core_config['apps_path']['themes'] . '/' . core_themes_get() . '/language/');
						bind_textdomain_codeset('messages', 'UTF-8');
						textdomain('messages');
					}
					include $fn;
				} else {
					$fn = $core_config['apps_path']['themes'] . '/common/page_' . _INC_ . '.php';
					if (file_exists($fn)) {
						include $fn;
					}
				}
			}
	}
} else {
	// no _APP_ then load default page
	if (auth_isvalid()) {
		$query_string = '';
		if ($core_config['main']['default_inc']) {
			$query_string .= '&inc=' . $core_config['main']['default_inc'];
		} else {
			$query_string .= '&inc=core_welcome';
		}
		if ($core_config['main']['default_route']) {
			$query_string .= '&route=' . $core_config['main']['default_route'];
		}
		if ($core_config['main']['default_op']) {
			$query_string .= '&op=' . $core_config['main']['default_op'];
		}
		header("Location: " . _u('index.php?app=main' . $query_string));
	} else {
		header("Location: " . _u('index.php?app=main&inc=core_auth&route=login'));
	}
	exit();
}

unset($_SESSION['dialog']);

// fixme anton - still exists for compatibilty
unset($_SESSION['error_string']);

// fixme anton - remove last_post
unset($_SESSION['tmp']['last_post']);
