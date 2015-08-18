<?php
defined('_SECURE_') or die('Forbidden');

// get kannel config from registry
$data = registry_search(1, 'gateway', 'kannel');
$plugin_config['kannel'] = $data['gateway']['kannel'];
$plugin_config['kannel']['name'] = 'kannel';
$plugin_config['kannel']['bearerbox_host'] = ($plugin_config['kannel']['bearerbox_host'] ? $plugin_config['kannel']['bearerbox_host'] : 'localhost');
$plugin_config['kannel']['sendsms_host'] = ($plugin_config['kannel']['sendsms_host'] ? $plugin_config['kannel']['sendsms_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['sendsms_port'] = (int) ($plugin_config['kannel']['sendsms_port'] ? $plugin_config['kannel']['sendsms_port'] : '13131');
$plugin_config['kannel']['dlr_mask'] = (int) ($plugin_config['kannel']['dlr_mask'] ? $plugin_config['kannel']['dlr_mask'] : '27');
$plugin_config['kannel']['playsms_web'] = ($plugin_config['kannel']['playsms_web'] ? $plugin_config['kannel']['playsms_web'] : _HTTP_PATH_BASE_);
$plugin_config['kannel']['admin_host'] = ($plugin_config['kannel']['admin_host'] ? $plugin_config['kannel']['admin_host'] : $plugin_config['kannel']['bearerbox_host']);
$plugin_config['kannel']['admin_port'] = (int) ($plugin_config['kannel']['admin_port'] ? $plugin_config['kannel']['admin_port'] : '13000');
$plugin_config['kannel']['local_time'] = (int) ($plugin_config['kannel']['local_time'] ? 1 : 0);

// smsc configuration
$plugin_config['kannel']['_smsc_config_'] = array(
	'username' => _('Username'),
	'password' => _('Password'),
	'module_sender' => _('Module sender ID'),
	'module_timezone' => _('Module timezone'),
	'bearerbox_host' => _('Bearerbox hostname or IP'),
	'sendsms_host' => _('Send SMS hostname or IP'),
	'sendsms_port' => _('Send SMS port'),
	'dlr_mask' => _('DLR mask'),
	'additional_param' => _('Additional URL parameter'),
	'playsms_web' => _('playSMS web URL') 
);
