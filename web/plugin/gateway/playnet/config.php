<?php
defined('_SECURE_') or die('Forbidden');

// get playnet config from registry
$data = registry_search(0, 'gateway', 'playnet');
$plugin_config['playnet'] = $data['gateway']['playnet'];
$plugin_config['playnet']['name'] = 'playnet';
$plugin_config['playnet']['poll_interval'] = 2;
$plugin_config['playnet']['poll_limit'] = 400;

// smsc configuration
$plugin_config['playnet']['_smsc_config_'] = array(
	'local_playnet_username' => _('Local playnet username'),
	'local_playnet_password' => _('Local playnet password'),
	'remote_on' => _('Remote is on'),
	'remote_playsms_url' => _('Remote playSMS URL'),
	'remote_playnet_smsc' => _('Remote playnet SMSC name'),
	'remote_playnet_username' => _('Remote playnet username'),
	'remote_playnet_password' => _('Remote playnet password'),
	'sendsms_username' => _('Send SMS from remote using local username'),
	'module_sender' => _('Module sender ID'),
	'module_timezone' => _('Module timezone') 
);
