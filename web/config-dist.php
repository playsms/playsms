<?php
// PHP PEAR DB compatible database engine:
// mysql, mysqli, pgsql, odbc and others supported by PHP PEAR DB
$core_config['db']['type'] = 'mysql';		// database engine
$core_config['db']['host'] = 'localhost';	// database host/server
$core_config['db']['port'] = '3306';		// database port
$core_config['db']['user'] = '#DBUSER#';	// database username
$core_config['db']['pass'] = '#DBPASS#';	// database password
$core_config['db']['name'] = '#DBNAME#';	// database name
$core_config['db']['pref'] = 'playsms';		// table's prefix without trailing underscore

// SMTP configuration
$core_config['smtp']['relm'] = ''; // yes, not realm, its relm
$core_config['smtp']['user'] = '';
$core_config['smtp']['pass'] = '';
$core_config['smtp']['host'] = 'localhost';
$core_config['smtp']['port'] = '25';


// Do not change anything below this line unless you know what to do
// -----------------------------------------------------------------


// you can turn on or off PHP error reporting
// on production level you should turn off PHP error reporting (set to 0), by default its on
//error_reporting(0);
//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

// logs directories
$apps_path['logs']	= '#PATHLOG#';

// log level: 0=disabled, 1=info, 2=warning, 3=debug, 4=verbose
// WARNING: log level 3 and 4 will also save sensitif information such as password for used gateway
$core_config['logstate']	= 2;

// log file
$core_config['logfile']		= 'playsms.log';

// WARNING: will log almost anything but passwords
$core_config['logaudit']	= true;

// log audit file
$core_config['logauditfile']	= 'audit.log';

// do we allow the users to edit the sender number (default = no),
// only effective when there is no gateway sender ID defined
$core_config['denycustomsender']= true;

// are we using http or https ? the default is using http instead https
$core_config['ishttps']		= false;

// are we using sendsmsd or not. the default is using sendsmsd
$core_config['issendsmsd']	= true;

// limit the number of queue processed by sendsmsd in one time
$core_config['sendsmsd_queue']	= 30;

// limit the length of each queue processed by sendsmsd in one time
$core_config['sendsmsd_limit']	= 1000;

// webservices require username
$core_config['webservices_username']	= true;

?>