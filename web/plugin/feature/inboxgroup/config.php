<?php

defined('_SECURE_') or die('Forbidden');

// insert to left menu array
if (auth_isadmin()) {
	$menutab_feature = $core_config['menutab']['feature'];
	$menu_config[$menutab_feature][] = array("index.php?app=main&inc=feature_inboxgroup&op=list", _('Group inbox'));
}
