<?php

function gpid2gpname($gpid)
{
    if ($gpid)
    {
	$db_query = "SELECT gp_name FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE gpid='$gpid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$gp_name = $db_row['gp_name'];
    }
    return $gp_name;
}

function gpcode2gpname($uid,$gp_code)
{
    if ($uid && $gp_code)
    {
	$db_query = "SELECT gp_name FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid' AND gp_code='$gp_code'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$gp_name = $db_row['gp_name'];
    }
    return $gp_name;
}

function pid2pnum($pid)
{
    global $username;
    if ($pid)
    {
	$uid = username2uid($username);
	$db_query = "SELECT p_num FROM "._DB_PREF_."_tblUserPhonebook WHERE pid='$pid' AND uid='$uid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$p_num = $db_row['p_num'];
    }
    return $p_num;
}

function pnum2pemail($p_num)
{
    global $username;
    if ($p_num)
    {
	$uid = username2uid($username);
	$db_query = "SELECT p_email FROM "._DB_PREF_."_tblUserPhonebook WHERE p_num='$p_num' AND uid='$uid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$p_email = $db_row['p_email'];
    }
    return $p_email;
}

?>