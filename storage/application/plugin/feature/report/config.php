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

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['reports'];
	
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_inbox&op=all_inbox',
		_('All inbox') ,
		3
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_incoming&op=all_incoming',
		_('All feature messages') ,
		4
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=all_outgoing&op=all_outgoing',
		_('All sent messages') ,
		4
	);
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_report&route=sandbox&op=sandbox',
		_('Sandbox') ,
		5
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=admin",
		_('Report all users') ,
		10
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=online",
		_('Report whose online') ,
		10
	);
	$menu_config[$menutab][] = array(
		"index.php?app=main&inc=feature_report&route=banned",
		_('Report banned users') ,
		10
	);
}

$menutab = $core_config['menutab']['my_account'];

$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox',
	_('Inbox') ,
	1
);

$menutab = $core_config['menutab']['reports'];

$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_incoming&op=user_incoming',
	_('My feature messages'),
	1
);
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing',
	_('My sent messages'),
	1
);

$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_report&route=user",
	_('My report'),
	2
);
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_report&route=credit&op=credit_list",
	_('My credit transactions'),
	2
);
