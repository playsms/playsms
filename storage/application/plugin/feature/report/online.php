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

if (!auth_isadmin()) {
	auth_block();
};

// if kick action
if (_OP_ == 'kick') {
	if ($hash = $_GET['hash']) {
		user_session_remove('', '', $hash);
		header('Location: ' . _u('index.php?app=main&inc=feature_report&route=online'));
		exit();
	}
}

// display whose online

$tpl = array(
	'name' => 'report_online',
	'vars' => array(
		'Report' => _('Report') ,
		'Whose online' => _('Whose online') ,
		'User' => _('User') ,
		'Last update' => _('Last update') ,
		'Current IP address' => _('Current IP address') ,
		'User agent' => _('User agent') ,
		'Action' => 'Action',
	)
);

// display admin users

$users = report_whoseonline_admin();
foreach ($users as $user) {
	foreach ($user as $hash) {
		$tpl['loops']['data'][] = array(
			'tr_class' => $tr_class,
			'c_username' => $hash['username'],
			'c_isadmin' => $hash['icon_isadmin'],
			'last_update' => $hash['last_update'],
			'current_ip' => $hash['ip'],
			'user_agent' => $hash['http_user_agent'],
			'login_status' => $hash['login_status'],
			'action' => $hash['action_link'],
		);
	}
}

// display users

$users = report_whoseonline_user();
foreach ($users as $user) {
	foreach ($user as $hash) {
		$tpl['loops']['data'][] = array(
			'tr_class' => $tr_class,
			'c_username' => $hash['username'],
			'c_isadmin' => $hash['icon_isadmin'],
			'last_update' => $hash['last_update'],
			'current_ip' => $hash['ip'],
			'user_agent' => $hash['http_user_agent'],
			'login_status' => $hash['login_status'],
			'action' => $hash['action_link'],
		);
	}
}

// display subusers

$users = report_whoseonline_subuser();
foreach ($users as $user) {
	foreach ($user as $hash) {
		$tpl['loops']['data'][] = array(
			'tr_class' => $tr_class,
			'c_username' => $hash['username'],
			'c_isadmin' => $hash['icon_isadmin'],
			'last_update' => $hash['last_update'],
			'current_ip' => $hash['ip'],
			'user_agent' => $hash['http_user_agent'],
			'login_status' => $hash['login_status'],
			'action' => $hash['action_link'],
		);
	}
}

_p(tpl_apply($tpl));
