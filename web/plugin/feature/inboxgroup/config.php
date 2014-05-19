<?php

defined('_SECURE_') or die('Forbidden');

// insert to left menu array
if (auth_isadmin()) {
	$menutab = $core_config['menutab']['features'];
	$menu_config[$menutab][] = array("index.php?app=main&inc=feature_inboxgroup&op=list", _('Group inbox'));
}
