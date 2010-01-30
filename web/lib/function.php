<?

if(!(defined('_SECURE_'))){die('Intruder alert');};

// main functions
include "$apps_path[libs]/fn_logger.php";
include "$apps_path[libs]/fn_auth.php";
include "$apps_path[libs]/fn_user.php";
include "$apps_path[libs]/fn_sendsms.php";
include "$apps_path[libs]/fn_sendmail.php";
include "$apps_path[libs]/fn_phonebook.php";
include "$apps_path[libs]/fn_core.php";
include "$apps_path[libs]/fn_themes.php";

// init global variables
include "$apps_path[libs]/lib_init1.php";

// custom functions before plugins loading
include "$apps_path[libs]/fn_custom1.php";

// feature
for ($c=0;$c<count($core_config['featurelist']);$c++)
{
    $c_fn1 = "$apps_path[plug]/feature/".$core_config['featurelist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = "$apps_path[plug]/feature/".$core_config['featurelist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}

// gateway
for ($c=0;$c<count($core_config['gatewaylist']);$c++)
{
    $c_fn1 = "$apps_path[plug]/gateway/".$core_config['gatewaylist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = "$apps_path[plug]/gateway/".$core_config['gatewaylist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}

// themes
for ($c=0;$c<count($core_config['themeslist']);$c++)
{
    $c_fn1 = "$apps_path[plug]/themes/".$core_config['themeslist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = "$apps_path[plug]/themes/".$core_config['themeslist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}

// init global variables after plugins
include "$apps_path[libs]/lib_init2.php";

// custom functions after plugins loaded
include "$apps_path[libs]/fn_custom2.php";

?>