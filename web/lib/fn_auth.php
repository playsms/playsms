<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function validatelogin($username,$password) {
    $db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    $res_password = trim($db_row['password']);
    if ($password && $res_password && ($password==$res_password)) {
	$ticket = md5(mktime().$username);
	logger_print("u:".$username." t:".$ticket." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	return $ticket;
    } else {
	return false;
    }
}

function valid($var_ticket="",$var_username="",$var_multilogin_id="") {
    global $core_config;
    $ticket = $_COOKIE['vc1'];
    $username = $_COOKIE['vc2'];
    $multilogin_id = $_COOKIE['vc3'];
    if ($var_ticket && $var_username && $var_multilogin_id) {
	$ticket = $var_ticket;
	$username = $var_username;
	$multilogin_id = $var_multilogin_id;
    }
    if ($core_config['multilogin']) {
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($multilogin_id && md5($username.$db_row['password']) && ($multilogin_id==md5($username.$db_row['password']))) {
	    return true;
	} else {
	    return false;
	}
    } else {
	$db_query = "SELECT ticket FROM "._DB_PREF_."_tblUser WHERE username='$username' AND ticket='$ticket'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($ticket && $db_row['ticket']) {
	    return true;
	} else {
	    return false;
	}
    }
}

function isadmin($var_ticket="",$var_username="",$var_multilogin_id="") {
    global $core_config;
    $ticket = $_COOKIE['vc1'];
    $username = $_COOKIE['vc2'];
    $multilogin_id = $_COOKIE['vc3'];
    if ($var_ticket && $var_username && $var_multilogin_id) {
	$ticket = $var_ticket;
	$username = $var_username;
	$multilogin_id = $var_multilogin_id;
    }
    if ($core_config['multilogin']) {
	$db_query = "SELECT status,password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['status'] && ($db_row['status']==2) && ($multilogin_id==md5($username.$db_row['password']))) {
	    return true;
	} else {
	    return false;    
	}
    } else {
	$db_query = "SELECT status FROM "._DB_PREF_."_tblUser WHERE username='$username' AND ticket='$ticket'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['status'] && ($db_row['status']==2)) {
	    return true;
	} else {
	    return false;
	}
    }
}

function forcenoaccess() {
    $error_string = _('You have no access to this page');
    $errid = logger_set_error_string($error_string);
    header("Location: index.php?app=page&inc=noaccess&errid=".$errid);
    exit();
}

/*
 * Process login
 *
 */
function auth_login() {
    global $core_config;
    $username = trim($_REQUEST['username']);
    $password = trim($_REQUEST['password']);
    if ($username && $password) {
        if ($ticket = validatelogin($username,$password)) {
    	    $db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp='".mktime()."',ticket='$ticket' WHERE username='$username'";
	    if (@dba_affected_rows($db_query)) {
	        setcookie("vc1","$ticket");
	        setcookie("vc2","$username");
		if ($core_config['multilogin']) {
		    $multilogin_id = md5($username.$password);
		    setcookie("vc3","$multilogin_id");
		}
	    } else {
		$error_string = _('Unable to update login session');
	    }
	} else {
	    $error_string = _('Invalid username or password');
	}
    }
    if (isset($error_string)) {
	$errid = logger_set_error_string($error_string);
	header("Location: ".$core_config['http_path']['base']."/?errid=".$errid);
    } else {
	header("Location: ".$core_config['http_path']['base']);
    }
    exit();
}

/*
 * Process logout
 *
 */
function auth_logout() {
    global $core_config;
    $db_query = "UPDATE "._DB_PREF_."_tblUser SET ticket='".md5(mktime())."' ".
    $db_query .= "WHERE username='".$_COOKIE['vc2']."' AND ticket='".$_COOKIE['vc1']."'";
    $db_result = dba_query($db_query);
    logger_print("u:".$_COOKIE['vc2']." t:".$_COOKIE['vc1']." ip:".$_SERVER['REMOTE_ADDR'], 3, "logout");
    setcookie("vc1");
    setcookie("vc2");
    setcookie("vc3");
    $error_string = _('You have been logged out');
    $errid = logger_set_error_string($error_string);
    header("Location: ".$core_config['http_path']['base']."?errid=".$errid);
    exit();
}

/*
 * Process forgot password
 *
 */
function auth_forgot() {
    global $core_config;
    $username = trim($_REQUEST['username']);
    $email = trim($_REQUEST['email']);
    $error_string = _('Fail to recover password');
    if ($username && $email) {
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username' AND email='$email'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
	    if ($password = $db_row['password']) {
		$subject = "[SMSGW-recover-password] "._('for')." ".$username;
		$body = _('Website')."\t: ".$core_config['http_path']['base']."\n";
		$body .= _('Username')."\t: $username\n";
		$body .= _('Password')."\t: $password\n\n";
		$body .= $core_config['main']['cfg_email_footer']."\n\n";
		if (sendmail($core_config['main']['cfg_email_service'],$email,$subject,$body)) {
		    $error_string = _('Password has been sent to your email');
		} else {
		    $error_string = _('Fail to send email');
		}
	    }
	}
    }    
    $errid = logger_set_error_string($error_string);
    header("Location: ".$core_config['http_path']['base']."?errid=".$errid);
    exit();
}

?>