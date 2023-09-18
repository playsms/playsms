<?php
defined('_SECURE_') or die('Forbidden');

if (auth_isadmin()) {
	$menutab = $core_config['menutab']['reports'];
	$menu_config[$menutab][] = ["index.php?app=main&inc=feature_playsmslog&op=playsmslog_list", _('View log')];
}

$plugin_config['playsmslog']['playsmsd']['bin'] = '/home/playsms/bin/playsmsd';
$plugin_config['playsmslog']['playsmsd']['conf'] = '/home/playsms/etc/playsmsd.conf';
