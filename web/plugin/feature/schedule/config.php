<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_schedule&op=list",
	_('Schedule messages'),
	1 
);

// plugin config
$plugin_config['schedule']['rules'] = array(
	_('Once') => 0,
	_('Annually') => 1,
	_('Monthly') => 2,
	_('Weekly') => 3,
	_('Daily') => 4 
);

$plugin_config['schedule']['rules_display'] = array_flip($plugin_config['schedule']['rules']);

$plugin_config['schedule']['import_row_limit'] = 1000;

$plugin_config['schedule']['export_row_limit'] = 1000;
