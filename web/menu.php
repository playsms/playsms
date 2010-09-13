<?php
include "init.php";
include $apps_path['libs']."/function.php";

if (!($console = $_REQUEST['console']))
{
    include $apps_path['themes']."/".$themes_module."/header.php";
}

// user
$c_fn = $apps_path['incs']."/user/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// admin
$c_fn = $apps_path['incs']."/admin/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// common
$c_fn = $apps_path['incs']."/common/".$inc.".php";
if (file_exists($c_fn))
{
    include $c_fn;
}

// plugins
for ($i=0;$i<count($plugins_category);$i++) {
    if ($pc = $plugins_category[$i]) {
	for ($c=0;$c<count($core_config[$pc.'list']);$c++)
	{
	    if ($inc == $pc.'_'.$core_config[$pc.'list'][$c])
	    {
		$c_fn = $apps_path['plug'].'/'.$pc.'/'.$core_config[$pc.'list'][$c].'/'.$core_config[$pc.'list'][$c].'.php';
		if (file_exists($c_fn))
		{
		    include_once $c_fn;
		    break;
		}
	    }
	}
    }
}

if (!($console = $_REQUEST['console']))
{
    include $apps_path['themes']."/".$themes_module."/footer.php";
}

?>