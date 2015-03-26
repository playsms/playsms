<?php
// PHP PEAR DB compatible database engine:
// mysql, mysqli, pgsql, odbc and others supported by PHP PEAR DB
$core_config['db']['type'] = 'mysql';		// database engine
$core_config['db']['host'] = '#DBHOST#';	// database host/server
$core_config['db']['port'] = '#DBPORT#';	// database port
$core_config['db']['user'] = '#DBUSER#';	// database username
$core_config['db']['pass'] = '#DBPASS#';	// database password
$core_config['db']['name'] = '#DBNAME#';	// database name

// alternatively you can pass DSN and connect options
// ref:
// - http://pear.php.net/manual/en/package.database.db.intro-dsn.php
// - http://pear.php.net/manual/en/package.database.db.intro-connect.php
//$core_config['db']['dsn'] = 'mysql://root:password@localhost/playsms';
//$core_config['db']['options'] = $options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL);

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
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

// logs directories
$core_config['apps_path']['logs'] = '#PATHLOG#';

// log level: 0=disabled, 1=info, 2=warning, 3=debug, 4=verbose
// WARNING: log level 3 and 4 will also save sensitive information such as password for used gateway
$core_config['logstate']	= 2;

// log file
$core_config['logfile']		= 'playsms.log';

// WARNING: will log almost anything but passwords
$core_config['logaudit']	= true;

// log audit file
$core_config['logauditfile']	= 'audit.log';

// are we using http or https ? the default is using http instead https
$core_config['ishttps']		= false;

// are we using dlrd or not. the default is using dlrd
$core_config['isdlrd']		= true;

// limit the number of DLR processed by dlrd in one time
$core_config['dlrd_limit']	= 100;

// are we using recvsmsd or not. the default is using recvsmsd
$core_config['isrecvsmsd']	= true;

// limit the number of incoming SMS processed by recvsmsd in one time
$core_config['recvsmsd_limit']	= 200;

// are we using sendsmsd or not. the default is using sendsmsd
$core_config['issendsmsd']	= true;

// limit the length of each queue processed by sendsmsd in one time
$core_config['sendsmsd_limit']	= 1000;

// limit the number of queue processed by sendsmsd in one time
$core_config['sendsmsd_queue']	= 30;

// webservices require username
$core_config['webservices_username']	= true;
