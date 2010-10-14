<?php
include "init.php";
include $apps_path['libs']."/function.php";

// fixme anton - load menu and webservices from index
if ($app = $_REQUEST['app']) {
    switch ($app) {
	case 'menu': 
	    $fn = $apps_path['incs'].'/app/menu.php'; 
	    break;
	case 'ws':
	case 'webservice':
	case 'webservices':
	    $fn = $apps_path['incs'].'/app/webservices.php';
	    break;
    }
    if (file_exists($fn)) {
	include $fn;
    }
    exit();
}

$error_content = "";
if ($err) {
    $error_content .= "<div class=error_string>$err</div>";
}

bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');

if (valid()) {
    include $apps_path['themes']."/".$themes_module."/welcomepage.php";
} else {
    include $apps_path['themes']."/".$themes_module."/loginpage.php";
}
?>