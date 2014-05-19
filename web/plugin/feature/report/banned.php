<?php
if (!auth_isadmin()) {
	auth_block();
};

$uid = $_REQUEST['uid'];

// if ban/unban action
if (_OP_ == 'unban') {
	if (user_banned_remove($uid)) {
		$_SESSION['error_string'] = _('User has been unbanned').' ('._('username').': '.user_uid2username($uid).')';
	} else {
		$_SESSION['error_string'] = _('Unable to unban user').' ('._('username').': '.user_uid2username($uid).')';
	}
	header('Location: '._u('index.php?app=main&inc=feature_report&route=banned'));
	exit();
}

// display whose online

if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

$tpl = array(
	'name' => 'report_banned',
	'vars' => array(
		'Report' => _('Report'),
		'Banned users list' => _('Banned users list'),
		'ERROR' => $error_content,
		'User' => _('User'),
		'Email' => _('Email'),
		'Ban date/time' => _('Ban date/time'),
		'Action' => 'Action',
	)
);

// display admin users

$users = report_banned_admin();
foreach ($users as $user) {
	$tpl['loops']['data'][] = array(
		'tr_class' => $tr_class,
		'username' => $user['username'],
		'is_admin' => $user['icon_is_admin'],
		'email' => $user['email'],
		'bantime' => $user['bantime'],
		'action' => $user['action_link'],
	);
}

// display normal users

$users = report_banned_user();
foreach ($users as $user) {
	$tpl['loops']['data'][] = array(
		'tr_class' => $tr_class,
		'username' => $user['username'],
		'is_admin' => $user['icon_is_admin'],
		'email' => $user['email'],
		'bantime' => $user['bantime'],
		'action' => $user['action_link'],
	);
}

// display normal users

$users = report_banned_subuser();
foreach ($users as $user) {
	$tpl['loops']['data'][] = array(
		'tr_class' => $tr_class,
		'username' => $user['username'],
		'is_admin' => $user['icon_is_admin'],
		'email' => $user['email'],
		'bantime' => $user['bantime'],
		'action' => $user['action_link'],
	);
}

_p(tpl_apply($tpl));
