<?php
include "init.php";
include $apps_path['libs']."/function.php";

// fixme anton - load menu and webservices from index
$app = $_REQUEST['app'];
switch ($app) {
    case 'menu': 
	include $apps_path['base'].'/menu.php'; 
	exit();
	break;
    case 'ws':
	include $apps_path['incs'].'/common/webservices.php';
	exit();
	break;
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