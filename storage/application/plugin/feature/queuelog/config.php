<?php
defined('_SECURE_') or die('Forbidden');

$menutab = $core_config['menutab']['reports'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_queuelog&op=queuelog_list",
	_('View SMS queue') 
);
