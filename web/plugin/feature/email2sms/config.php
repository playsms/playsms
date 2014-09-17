<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab = $core_config['menutab']['settings'];
$menu_config[$menutab][] = array(
	'index.php?app=main&inc=feature_email2sms&op=email2sms',
	_('Manage email to SMS') 
);
