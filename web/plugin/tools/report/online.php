<?php
if (!auth_isadmin()) {
	auth_block();
};

// if kick action
if (_OP_ == 'kick') {
	if ($hash = $_GET['hash']) {
		user_session_remove('', '', $hash);
		header('Location: '._u('index.php?app=main&inc=tools_report&route=online'));
		exit();
	}
}

// display whose online

$tpl = array(
	'name' => 'report_online',
	'var' => array(
		'Report' => _('Report') ,
		'Whose online' => _('Whose online') ,
		'User' => _('User') ,
		'Last update' => _('Last update'),
		'Current IP address' => _('Current IP address'),
		'User agent' => _('User agent'),
		'Login status' => 'Login status',
		'Action' => 'Action',
	)
);

$hashes = user_session_get();
foreach ($hashes as $key => $val) {
	$c_uid = $val['uid'];
	$c_user = user_getdatabyuid($c_uid);
	$c_username = $c_user['username'];
	$c_status = $c_user['status'];

	$c_is_admin = '';
	if ($c_status == '2') {
		$c_is_admin = $icon_config['admin'];
	}

	$c_ip = $val['ip'];
	$c_user_agent = $val['http_user_agent'];
	$c_last_update = core_display_datetime($val['last_update']);

	$c_idle = (int)(strtotime(core_get_datetime()) - strtotime($val['last_update']));
	if ($c_idle > 15*60) {
		$c_login_status = 'idle';
	} else {
		$c_login_status = 'online';
	}

	$c_action = _a('index.php?app=main&inc=tools_report&route=online&op=kick&hash='.$key, 'kick');

	$tpl['loop']['data'][] = array(
		'tr_class' => $tr_class,
		'c_username' => $c_username,
		'c_is_admin' => $c_is_admin,
		'last_update' => $c_last_update,
		'current_ip' => $c_ip,
		'user_agent' => $c_user_agent,
		'login_status' => $c_login_status,
		'action' => $c_action,
	);
}

_p(tpl_apply($tpl));
