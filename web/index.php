<?php
include "init.php";
include $apps_path['libs']."/function.php";

bindtextdomain('messages', $apps_path['themes'].'/'.$themes_module.'/language/');

// fixme anton - load app extensions from index, such as menu and webservices
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


// frontpage
$error_content = "";
if ($err) {
    $error_content .= "<div class=error_string>$err</div>";
}

if (valid()) {
    include $apps_path['themes']."/".$themes_module."/welcomepage.php";
} else {
    include $apps_path['themes']."/".$themes_module."/loginpage.php";
}
?>