<?php
$username = trim($_POST['username']);
$password = trim($_POST['password']);

if ($_POST['username'] && $_POST['password']) {
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
    header("Location: ".$http_path['base']."/?err=".urlencode($error_string));
} else {
    header("Location: ".$http_path['base']);
}

die();
?>