<?
include "init.php";
include "$apps_path[libs]/function.php";

if (!($console = $_REQUEST['console']))
{
    include $apps_path[themes]."/".$themes_module."/header.php";
}

// user
$c_fn = $apps_path[incs]."/user/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// admin
$c_fn = $apps_path[incs]."/admin/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// common
$c_fn = $apps_path[incs]."/common/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// plugin tools
for ($c=0;$c<count($core_config['toolslist']);$c++)
{
    if ($inc == 'feature_'.$core_config['toolslist'][$c])
    {
	$c_fn = $apps_path['plug'].'/tools/'.$core_config['toolslist'][$c].'/'.$core_config['toolslist'][$c].'.php';
	if (file_exists($c_fn))
	{
	    include $c_fn;
	    break;
	}
    }
}

// plugin feature
for ($c=0;$c<count($core_config['featurelist']);$c++)
{
    if ($inc == 'feature_'.$core_config['featurelist'][$c])
    {
	$c_fn = $apps_path['plug'].'/feature/'.$core_config['featurelist'][$c].'/'.$core_config['featurelist'][$c].'.php';
	if (file_exists($c_fn))
	{
	    include $c_fn;
	    break;
	}
    }
}

// plugin gateway
for ($c=0;$c<count($core_config['gatewaylist']);$c++)
{
    if ($inc == 'gateway_'.$core_config['gatewaylist'][$c])
    {
	$c_fn = $apps_path['plug'].'/gateway/'.$core_config['gatewaylist'][$c].'/'.$core_config['gatewaylist'][$c].'.php';
	if (file_exists($c_fn))
	{
	    include $c_fn;
	    break;
	}
    }
}

if (!($console = $_REQUEST['console']))
{
    include $apps_path[themes]."/".$themes_module."/footer.php";
}

?>