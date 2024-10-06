<?php

// Database connection configuration
$core_config['db']['type'] = 'mysql';		// database engine
$core_config['db']['host'] = '#DBHOST#';	// database host/server
$core_config['db']['port'] = '#DBPORT#';	// database port
$core_config['db']['user'] = '#DBUSER#';	// database username
$core_config['db']['pass'] = '#DBPASS#';	// database password
$core_config['db']['name'] = '#DBNAME#';	// database name

// SMTP configuration
$core_config['smtp']['relm'] = ''; // yes, not realm, it's relm
$core_config['smtp']['user'] = '';
$core_config['smtp']['pass'] = '';
$core_config['smtp']['host'] = 'localhost';
$core_config['smtp']['port'] = '25';


// Do not change anything below this line unless you know what to do
// -----------------------------------------------------------------


// you can turn on or off PHP error reporting
// on production level you should turn off PHP error reporting (set to 0), by default it's on
//error_reporting(0);
//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);

// logs directories
$core_config['apps_path']['logs'] = '#PATHLOG#';

// log level: 0=disabled, 1=info, 2=warning, 3=debug, 4=verbose
// WARNING: log level 3 and 4 will also save sensitive information such as password for used gateway
$core_config['logstate'] = 2;

// log file
$core_config['logfile'] = 'playsms.log';

// WARNING: will log almost anything but passwords
$core_config['logaudit'] = true;

// log audit file
$core_config['logauditfile'] = 'audit.log';

// are we using http or https ? the default is using https instead http
$core_config['ishttps'] = true;

// are we using dlrd or not. the default is using dlrd
$core_config['isdlrd'] = true;

// limit the number of DLR processed by dlrd in one time
$core_config['dlrd_limit'] = 1000;

// are we using recvsmsd or not. the default is using recvsmsd
$core_config['isrecvsmsd'] = true;

// are we using recvsmsd queue or not. the default is using recvsmsd queue
$core_config['isrecvsmsd_queue'] = true;

// limit the number of incoming SMS processed by recvsmsd in one time (max: 200)
// when isrecvsmsd_queue is true then 1 limit = 1 PHP CLI process processing 1 incoming SMS
$core_config['recvsmsd_limit'] = 10;

// are we using sendsmsd or not. the default is using sendsmsd
$core_config['issendsmsd'] = true;

// limit the number of queue processed by sendsmsd in one time
$core_config['sendsmsd_queue'] = 10;

// limit the number of chunk per queue
$core_config['sendsmsd_chunk'] = 20;

// chunk size
$core_config['sendsmsd_chunk_size'] = 100;

// limit the number of outgoing SMS processed by sendsmsd in one time
$core_config['sendsmsd_limit'] = 1000;

// webservices require username
$core_config['webservices_username'] = true;

// use alternate $_SERVER['REMOTE_ADDR']
// keep this empty unless you know what you are doing
$core_config['remote_addr'] = '';