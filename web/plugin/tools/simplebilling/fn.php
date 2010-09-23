<?php
function simplebilling_hook_billing_post($smslog_id,$rate,$credit) {
    global $core_config, $datetime_now;
    $ok = false;
    logger_print("saving smslog_id:".$smslog_id." rate:".$rate." credit:".$credit, 3, "simplebilling post");
    $db_query = "INSERT INTO "._DB_PREF_."_tblBilling (post_datetime,smslog_id,rate,credit,status) VALUES ('$datetime_now','$smslog_id','$rate','$credit','1')";
    if ($db_result = @dba_insert_id($db_query)) {
	logger_print("saved smslog_id:".$smslog_id, 3, "simplebilling post");
	$ok = true;
    }
    return $ok;
}

function simplebilling_hook_billing_roll($smslog_id) {
    global $core_config;
    $ok = false;
    $rate = 0;
    $credit = 0;
    $db_query = "SELECT rate,credit FROM "._DB_PREF_."_tblBilling WHERE smslog_id='$smslog_id'";
    $db_result = dba_query($db_query);
    if ($db_row = dba_fetch_array($db_result)) {
	$rate = $db_row['rate'];
	$credit = $db_row['credit'];
	$db_query = "UPDATE "._DB_PREF_."_tblBilling SET status='2' WHERE smslog_id='$smslog_id'";
	if ($db_result = dba_affected_rows($db_query)) {
	    $ok = true;
	}
    }
    $ret_array = array($ok, $rate, $credit);
    return $ret_array;
}
?>