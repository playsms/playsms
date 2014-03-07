<?php
defined('_SECURE_') or die('Forbidden');

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array("index.php?app=main&inc=tools_queuelog&op=queuelog_list", _('View SMS queue'));
