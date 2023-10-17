<?php
defined('_SECURE_') or die('Forbidden');

// registered autostart services for playSMS daemon
$plugin_config['core']['playsmsd']['services'] = [
	'starterd',
	'schedule',
	'ratesmsd',
	'dlrssmsd',
	'sendsmsd',
	'recvsmsd',
];