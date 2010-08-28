<?
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
$http_path['logs']	= '';

// 0 for single session login; 1 for multi session login
// multi session login is not secure because playsms leaves md5 crypted username and password
// on admin's computer
$core_config['multilogin']	= 0;

// true to enable debug or logging, false to disable
$core_config['logstate']	= false;

// are we using http or https ? the default is using http instead https
$core_config['ishttps']		= false;

// max sms text length
$core_config['smsmaxlength']	= 2*153; // single text sms can be 160 char instead of 1*153

// plugin feature list
$core_config['featurelist']	= array('sms_autoreply','sms_autosend','sms_board','sms_command','sms_custom','sms_poll','sms_quiz','sms_subscribe');

// plugin gateway list
$core_config['gatewaylist']	= array('clickatell','gnokii','kannel','smstools','uplink');

// plugin themes list
$core_config['themeslist']	= array('default');

// plugin tools list
$core_config['toolslist']	= array();

?>