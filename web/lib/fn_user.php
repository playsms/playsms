<?php
function uid2username($uid)
{
    if ($uid)
    {
	$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$username = $db_row['username'];
    }
    return $username;
}

function username2uid($username)
{
    if ($username)
    {
	$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$uid = $db_row['uid'];
    }
    return $uid;
}

function username2mobile($username)
{
    if ($username)
    {
	$db_query = "SELECT mobile FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$mobile = $db_row['mobile'];
    }
    return $mobile;
}

function username2credit($username)
{
    if ($username)
    {
	$db_query = "SELECT credit FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$credit = $db_row['credit'];
    }
    return $credit;
}

function username2sender($username)
{
    if ($username)
    {
	$db_query = "SELECT sender FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$sender = $db_row['sender'];
    }
    return $sender;
}

function username2email($username)
{
    if ($username)
    {
	$db_query = "SELECT email FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$email = $db_row['email'];
    }
    return $email;
}

function username2name($username)
{
    if ($username)
    {
	$db_query = "SELECT name FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$name = $db_row['name'];
    }
    return $name;
}

function username2status($username)
{
    if ($username)
    {
	$db_query = "SELECT status FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$status = $db_row['status'];
    }
    return $status;
}

function mobile2uid($mobile)
{
    if ($mobile)
    {
	if (substr($mobile,0,1) == 0)
	{
	    $mobile = substr($mobile,1);
	}
	$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE mobile LIKE '%$mobile'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$uid = $db_row['uid'];
    }
    return $uid;
}

?>