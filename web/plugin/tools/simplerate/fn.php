<?php

function simplerate_getdst($id) {
    if ($id) {
	$db_query = "SELECT dst FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$dst = $db_row['dst'];
    }
    return $dst;
}

function simplerate_getprefix($id) {
    if ($id) {
	$db_query = "SELECT prefix FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$prefix = $db_row['prefix'];
    }
    return $prefix;
}

function simplerate_getbyid($id) {
    if ($id) {
	$db_query = "SELECT rate FROM "._DB_PREF_."_toolsSimplerate WHERE id='$id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$rate = $db_row['rate'];
    }
    return $rate;
}

function simplerate_hook_rate_getbyprefix($p_dst) {
    global $default_rate;
    $rate = $default_rate;
    $prefix = $p_dst;
    $m = ( strlen($prefix) > 10 ? 10 : strlen($prefix) );
    for ($i=$m+1;$i>0;$i--) {
	$prefix = substr($prefix, 0, $i);
	$db_query = "SELECT rate FROM "._DB_PREF_."_toolsSimplerate WHERE prefix='$prefix'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
	    $rate = $db_row['rate'];
	    break;
	}
    }
    return $rate;
}

function simplerate_hook_rate_setusercredit($uid, $remaining=0) {
    $db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp=NOW(),credit='$remaining' WHERE uid='$uid'";
    $db_result = @dba_affected_rows($db_query);
    return true;
}

function simplerate_hook_rate_getusercredit($username) {
    if ($username) {
	$db_query = "SELECT credit FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$credit = $db_row['credit'];
    }
    return $credit;
}

function simplerate_hook_rate_getmax($default="") {
    global $default_rate;
    if ($default && ($default > 0)) {
	$default_rate = $default;
    }
    $rate = 0;
    $db_query = "SELECT rate FROM "._DB_PREF_."_toolsSimplerate ORDER BY rate DESC LIMIT 1";
    $db_result = dba_query($db_query);
    if ($db_row = dba_fetch_array($db_result)) {
	$rate = $db_row['rate'];
    }
    if ($default_rate > $rate) {
	$rate = $default_rate;
    }
    return $rate;
}

function simplerate_hook_rate_cansend($username, $default="") {
    $credit = simplerate_hook_rate_getusercredit($username);
    $maxrate = simplerate_hook_rate_getmax($default);
    $ok = ( ($credit >= $maxrate) ? true : false );
    return $ok;
}

function simplerate_hook_rate_setcredit($smslog_id) {
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    $p_dst = $db_row['p_dst'];
    $p_msg = $db_row['p_msg'];
    $uid = $db_row['uid'];
    // here should be added a routine to check charset encoding
    // utf8 devided by 140, ucs2 devided by 70
    $count = ceil(strlen($p_msg) / 153);
    $rate = simplerate_hook_rate_getbyprefix($p_dst);
    $username = uid2username($uid);
    $credit = simplerate_hook_rate_getusercredit($username);
    $remaining = $credit - ($rate*$count);
    simplerate_hook_rate_setusercredit($uid, $remaining);
    return true;
}

?>