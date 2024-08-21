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

// export row limit
$report_export_limit = 10000;

// page reports
$menutab = $core_config['menutab']['reports'];

if (auth_isadmin()) {
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_report&route=all_inbox&op=all_inbox',
		_('Inbox messages'),
		1
	];
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_report&route=all_incoming&op=all_incoming&sandbox=1',
		_('Sandbox'),
		4
	];
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_report&route=admin',
		_('Report all users'),
		10
	];
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_report&route=online',
		_('Report whose online'),
		10
	];
	$menu_config[$menutab][] = [
		'index.php?app=main&inc=feature_report&route=banned',
		_('Report banned users'),
		10
	];
}

$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_report&route=all_incoming&op=all_incoming',
	_('Feature messages'),
	2
];
$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_report&route=all_outgoing&op=all_outgoing',
	_('Sent messages'),
	3
];
$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_report&route=user',
	_('My report'),
	5
];
$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_report&route=credit&op=credit_list',
	_('My credit transactions'),
	5
];

// if not admin then put in my account
$menutab = $core_config['menutab']['my_account'];

$menu_config[$menutab][] = [
	'index.php?app=main&inc=feature_report&route=all_inbox&op=all_inbox&user_inbox=1',
	_('My inbox'),
	1
];
