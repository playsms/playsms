<?php
defined('_SECURE_') or die('Forbidden');

$plugin_config['stoplist']['login_attempt_limit'] = 3;

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['settings'];
	$menu_config[$menutab][] = array(
		'index.php?app=main&inc=feature_stoplist&op=stoplist_list',
		_('Manage stoplist'),
		3 
	);
}
