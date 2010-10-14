<?php
include "init.php";
include $apps_path['libs']."/function.php";

// fixme anton - load menu and webservices from index
if ($app = $_REQUEST['app']) {
    switch ($app) {
	case 'menu': 
	    include $apps_path['incs'].'/app/menu.php'; 
	    break;
	case 'ws':
	    include $apps_path['incs'].'/app/webservices.php';
	    break;
    }
    exit();
}

$error_content = "";
if ($err) {
    $error_content .= "<p align=center><font color=red>$err</font></p>";
}

bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');

if (valid()) {
    include $apps_path['themes']."/".$themes_module."/welcomepage.php";
} else {
    include $apps_path['themes']."/".$themes_module."/loginpage.php";
}
?>