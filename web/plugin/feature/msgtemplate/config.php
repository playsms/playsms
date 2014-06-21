<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array("index.php?app=main&inc=feature_msgtemplate&op=list", _('Message template'));
