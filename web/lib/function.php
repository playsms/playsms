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

defined('_SECURE_') or die('Forbidden');

// main functions
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_billing.php";
include $apps_path['libs']."/fn_dlr.php";
include $apps_path['libs']."/fn_recvsms.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_webservices.php";

// init global variables

// load additional user's data from user's DB table
if (auth_isvalid()) {
	$userstatus = ( $status == 2 ? _('Administrator') : _('Normal User') );
	$core_config['user']['opt']['sms_footer_length'] = ( strlen($footer) > 0 ? strlen($footer) + 1 : 0 );
	$core_config['user']['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $core_config['user']['opt']['sms_footer_length'];
	$core_config['user']['opt']['gravatar'] = "https://www.gravatar.com/avatar/".md5(strtolower(trim($core_config['user']['email'])));
}

// reserved important keywords
$reserved_keywords = array ("BC");
$core_config['reserved_keywords'] = $reserved_keywords;

// menus
$core_config['menutab']['home'] = _('Home');
$core_config['menutab']['my_account'] = _('My Account');
$core_config['menutab']['tools'] = _('Tools');
$core_config['menutab']['feature'] = _('Feature');
$core_config['menutab']['administration'] = _('Administration');

$menutab_my_account = $core_config['menutab']['my_account'];
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=send_sms&op=send_sms", _('Send message'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_inbox&op=user_inbox", _('Inbox'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_incoming&op=user_incoming", _('Incoming messages'), 1);
$menu_config[$menutab_my_account][] = array("index.php?app=menu&inc=user_outgoing&op=user_outgoing", _('Outgoing messages'), 1);

if (auth_isadmin()) {
	// administrator menus
	$menutab_administration = $core_config['menutab']['administration'];
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_inbox&op=all_inbox", _('All inbox'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_incoming&op=all_incoming", _('All incoming messages'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=all_outgoing&op=all_outgoing", _('All outgoing messages'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=sandbox&op=sandbox", _('Sandbox'), 1);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=user_mgmnt&op=user_list", _('Manage user'), 2);
	$menu_config[$menutab_administration][] = array("index.php?app=menu&inc=main_config&op=main_config", _('Main configuration'), 2);
	//ksort($menu_config[$menutab_administration]);
}

// fixme anton - uncomment this if you want to know what are available in global config arrays
//print_r($menu_config); die();
//print_r($core_config); die();

// end of init global variables

// load plugin's config and libraries
for ($i=0;$i<count($plugins_category);$i++) {
	if ($pc = $plugins_category[$i]) {
		// get plugins
		$dir = $apps_path['plug'].'/'.$pc.'/';
		unset($core_config[$pc.'list']);
		unset($tmp_core_config[$pc.'list']);
		$fd = opendir($dir);
		$pc_names = array();
		while(false !== ($pl_name = readdir($fd))) {
			// plugin's dir prefixed with dot or underscore will not be loaded
			if (substr($pl_name, 0, 1) != "." && substr($pl_name, 0, 1) != "_" ) {
				// exeptions for themes/common
				if (! (($pc == 'themes') && ($pl_name == 'common'))) {
					$pc_names[] = $pl_name;
				}
			}
		}
		closedir();
		sort($pc_names);
		for ($j=0;$j<count($pc_names);$j++) {
			if (is_dir($dir.$pc_names[$j])) {
				$core_config[$pc.'list'][] = $pc_names[$j];
			}
		}
	}
}

// load each plugin's config
$dir = $apps_path['plug'].'/';
$pcs = array('themes', 'language', 'gateway', 'feature', 'tools');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/config.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load each plugin's libs
$dir = $apps_path['plug'].'/';
$pcs = array('feature', 'tools');
foreach ($pcs as $pc) {
	for ($i=0;$i<count($core_config[$pc.'list']);$i++) {
		$pl = $core_config[$pc.'list'][$i];
		$pl_dir = $dir.$pc.'/'.$pl;
		$c_fn1 = $pl_dir.'/fn.php';
		if (file_exists($c_fn1)) {
			if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language')) {
				bindtextdomain('messages', $pl_dir.'/language/');
				bind_textdomain_codeset('messages', 'UTF-8');
				textdomain('messages');
			}
			include $c_fn1;
		}
	}
}

// load active themes libs
$dir = $apps_path['plug'].'/';
$pc = 'themes';
$pl = core_themes_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/fn.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
}

// load common items for themes
$c_fn1 = $apps_path['plug'].'/themes/common/config.php';
if (file_exists($c_fn1)) {
	include $c_fn1;
	$c_fn2 = $apps_path['plug'].'/themes/common/fn.php';
	if (file_exists($c_fn2)) {
		include $c_fn2;
	}
}

// themes main overrides
$mains = $core_config['plugin'][core_themes_get()]['main'];
if (is_array($mains)) {
	foreach ($mains as $main_key => $main_val) {
		if ($main_key && $main_val) {
			$core_config['main'][$main_key] = $main_val;
		}
	}
}

// themes icons overrides
$icons = $core_config['plugin'][core_themes_get()]['icon'];
if (is_array($icons)) {
	foreach ($icons as $icon_action => $icon_url) {
		if ($icon_action && $icon_url) {
			$core_config['icon'][$icon_action] = $icon_url;
		}
	}
}

// load active gateway libs
$dir = $apps_path['plug'].'/';
$pc = 'gateway';
$pl = core_gateway_get();
$pl_dir = $dir.$pc.'/'.$pl;
$c_fn1 = $pl_dir.'/fn.php';
if (file_exists($c_fn1)) {
	if (function_exists('bindtextdomain') && file_exists($pl_dir.'/language/')) {
		bindtextdomain('messages', $plugin_dir.'/language/');
		bind_textdomain_codeset('messages', 'UTF-8');
		textdomain('messages');
	}
	include $c_fn1;
}

if (function_exists('bindtextdomain')) {
	bindtextdomain('messages', $apps_path['plug'].'/language/');
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain('messages');
}

// init global variables after plugins

// load menus into core_config
$core_config['menu'] = $menu_config;

// fixme anton - uncomment this if you want to know what are available in global config arrays
//print_r($menu_config); die();
//print_r($core_config); die();

// end of global variables after plugins
