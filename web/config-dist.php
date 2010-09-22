<?php
// PHP PEAR DB compatible database engine: 
// msql, mssql, mysql, oci8, odbc, pgsql, sqlite
$db_param['type'] = 'mysql';		// database engine
$db_param['host'] = 'localhost';	// database host/server
$db_param['port'] = '';			// database port
$db_param['user'] = 'root';		// database username
$db_param['pass'] = 'rootpassword';	// database password
$db_param['name'] = 'playsms';		// database name
$db_param['pref'] = 'playsms';		// table's prefix without trailing underscore

// SMTP sendmail
define('_SMTP_RELM_','');
define('_SMTP_USER_','');
define('_SMTP_PASS_','');
define('_SMTP_HOST_','localhost');
define('_SMTP_PORT_','25');

// Do not change anything below this line unless you know what to do
// -----------------------------------------------------------------


// you can turn off PHP error reporting by uncommenting below line
// on production level you should turn off PHP error reporting
//error_reporting(0);

// logs directories
$apps_path['logs']	= '/var/log/playsms';

// 0 for single session login; 1 for multi session login
// multi session login is not secure because playsms leaves md5 crypted username and password
// on admin's computer
$core_config['multilogin']	= 0;

// log level: 0=disabled, 1=info, 2=warning, 3=debug
// WARNING: log level 3 will also save sensitif information such as password for used gateway
$core_config['logstate']	= 0;

// are we using http or https ? the default is using http instead https
$core_config['ishttps']		= false;

// max sms text length
$core_config['smsmaxlength']	= 2*153; // single text sms can be 160 char instead of 1*153

?>