<?php

// logs directory
// must be writable by both web server user and playSMS daemon
$core_config['apps_path']['logs'] 		= '#PATHLOG#';

// script/daemon directory
// location of playSMS daemon script
$core_config['apps_path']['bin'] 		= '#PATHBIN#';

// storage directory
// outside of web root and not accessible directly from web
$core_config['apps_path']['storage']	= '#PATHSTR#';

// web/base directory
// accessible directly from web
$core_config['apps_path']['base'] 		= '#PATHWEB#';

// web/base http url
$core_config['http_path']['base'] 		= '#PLAYSMSURL#';

// Application ID
$core_config['application']['name']		= 'default';
$core_config['application']['dir']		= 'application';
