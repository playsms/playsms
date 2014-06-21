<?php
defined('_SECURE_') or die('Forbidden');

$menutab = $core_config['menutab']['settings'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_sender_id&op=sender_id_list",
	_('Manage sender ID')
);
