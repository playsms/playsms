<?php
function validatelogin($username,$password)
{
    $db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    $res_password = trim($db_row['password']);
    if ($password && $res_password && ($password==$res_password))
    {
	$ticket = md5(mktime().$username);
	logger_print("u:".$username." t:".$ticket." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	return $ticket;
    }
    else
    {
	return false;
    }
}

function valid($var_ticket="",$var_username="",$var_multilogin_id="")
{
    global $core_config;
    $ticket = $_COOKIE['vc1'];
    $username = $_COOKIE['vc2'];
    $multilogin_id = $_COOKIE['vc3'];
    if ($var_ticket && $var_username && $var_multilogin_id)
    {
	$ticket = $var_ticket;
	$username = $var_username;
	$multilogin_id = $var_multilogin_id;
    }
    if ($core_config['multilogin'])
    {
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($multilogin_id && md5($username.$db_row['password']) || ($multilogin_id==md5($username.$db_row['password'])))
	{
	    return true;
	}
	else
	{
	    return false;    
	}
    }
    else
    {
	$db_query = "SELECT ticket FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($ticket && $db_row['ticket'])
	{
	    return true;
	}
	else
	{
	    return false;    
	}
    }
}

function isadmin($var_ticket="",$var_username="",$var_multilogin_id="")
{
    global $core_config;
    $ticket = $_COOKIE['vc1'];
    $username = $_COOKIE['vc2'];
    $multilogin_id = $_COOKIE['vc3'];
    if ($var_ticket && $var_username && $var_multilogin_id)
    {
	$ticket = $var_ticket;
	$username = $var_username;
	$multilogin_id = $var_multilogin_id;
    }
    if ($core_config['multilogin'])
    {
	$db_query = "SELECT status,password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['status'] && ($db_row['status']==2) || ($multilogin_id==md5($username.$db_row['password'])))
	{
	    return true;
	}
	else
	{
	    return false;    
	}
    }
    else
    {
	$db_query = "SELECT status FROM "._DB_PREF_."_tblUser WHERE username='$username' AND ticket='$ticket'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['status'] && ($db_row['status']==2))
	{
	    return true;
	}
	else
	{
	    return false;    
	}
    }
}

function forcelogout()
{
    global $http_path;
    $db_query = "UPDATE "._DB_PREF_."_tblUser SET ticket='".md5(mktime())."' ".
    $db_query .= "WHERE username='".$_COOKIE['vc2']."' AND ticket='".$_COOKIE['vc1']."'";
    $db_result = dba_query($db_query);
    logger_print("u:".$_COOKIE['vc2']." t:".$_COOKIE['vc1']." ip:".$_SERVER['REMOTE_ADDR'], 3, "logout");
    setcookie("vc1");
    setcookie("vc2");
    setcookie("vc3");
    header("Location: ".$http_path['base']."?err=".urlencode("You have been logged out!"));
    die();
}

function forcenoaccess()
{
    header("Location: menu.php?inc=noaccess&err=".urlencode("You have no access to this page!"));
    die();
}

?>