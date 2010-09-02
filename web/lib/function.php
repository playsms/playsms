<?php

if(!(defined('_SECURE_'))){die('Intruder alert');};

// main functions
include $apps_path['libs']."/fn_logger.php";
include $apps_path['libs']."/fn_auth.php";
include $apps_path['libs']."/fn_user.php";
include $apps_path['libs']."/fn_rate.php";
include $apps_path['libs']."/fn_sendsms.php";
include $apps_path['libs']."/fn_sendmail.php";
include $apps_path['libs']."/fn_phonebook.php";
include $apps_path['libs']."/fn_core.php";
include $apps_path['libs']."/fn_themes.php";

// init global variables
include $apps_path['libs']."/lib_init1.php";

// custom functions before plugins loading
include $apps_path['libs']."/fn_custom1.php";
/*
// tools
for ($c=0;$c<count($core_config['toolslist']);$c++)
{
    $c_fn1 = $apps_path['plug']."/tools/".$core_config['toolslist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = $apps_path['plug']."/tools/".$core_config['toolslist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}

// feature
for ($c=0;$c<count($core_config['featurelist']);$c++)
{
    $c_fn1 = $apps_path['plug']."/feature/".$core_config['featurelist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = $apps_path['plug']."/feature/".$core_config['featurelist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}

// gateway
for ($c=0;$c<count($core_config['gatewaylist']);$c++)
{
    $c_fn1 = $apps_path['plug']."/gateway/".$core_config['gatewaylist'][$c]."/config.php";
    if (file_exists($c_fn1))
    {
	include $c_fn1;
	$c_fn2 = $apps_path['plug']."/gateway/".$core_config['gatewaylist'][$c]."/fn.php";
	if (file_exists($c_fn2))
	{
	    include $c_fn2;
	}
    }
}
*/
$plugins_category = array('tools', 'feature', 'gateway', 'themes');

for ($i=0;$i<count($plugins_category);$i++) {
    $pc = $plugins_category[$i];
    // get plugins
    ${$themes}_dir = $apps_path['{$themes}']."/";
    unset($core_config['{$themes}list']);
    $fd = opendir(${$themes}_dir);
    while(false !== (${$themes} = readdir($fd)))
    {
	if (is_dir(${$themes}_dir.${$themes}) && substr(${$themes},0,1) != "." ) {
	    $core_config['{$themes}list'][] = ${$themes};
	    if (${$themes} == ${$themes}_module) $selected = "selected";
	    $option_{$themes}_module .= "<option value=\"$theme\" $selected>".${$themes}."</option>\n";
	    $selected = "";
	}
    }
    closedir();
    // load plugin's config and libaries
    for ($c=0;$c<count($core_config['{$themes}list']);$c++)
    {
	$c_fn1 = $apps_path['plug']."/{$themes}/".$core_config['{$themes}list'][$c]."/config.php";
	if (file_exists($c_fn1))
	{
	    include $c_fn1;
	    $c_fn2 = $apps_path['plug']."/{$themes}/".$core_config['{$themes}list'][$c]."/fn.php";
	    if (file_exists($c_fn2))
	    {
		include $c_fn2;
	    }
	}
    }
}

// init global variables after plugins
include $apps_path['libs']."/lib_init2.php";

// custom functions after plugins loaded
include $apps_path['libs']."/fn_custom2.php";

?>