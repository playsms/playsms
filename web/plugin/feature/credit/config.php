<?php
defined('_SECURE_') or die('Forbidden');

// admin and users allowed to use this plugin
if (($user_config['status'] == 2) || ($user_config['status'] == 3)) {
	$menutab = $core_config['menutab']['settings'];
	$menu_config[$menutab][] = array("index.php?app=main&inc=feature_credit&op=credit_list", _('Manage credit'));
}

$plugin_config['credit']['db_table'] = _DB_PREF_.'_featureCredit';
