<?php

if (! $called_from_hook_call) {
    chdir("../../../");
    include "init.php";
    include $apps_path['libs']."/function.php";
    chdir("plugin/gateway/clickatell/");
    $requests = $_REQUEST;
}

echo "OK";

$cb_from = "+".$requests['phone'];
$timedata = date('Y-m-d H:i:s');
$cb_timestamp = strtotime($timedata);
$cb_text = $requests['message'];
if($requests['status'] == "D") {
  $cb_status = "3";
} else {
  $cb_status = "1";
}
//$cb_status = "001";
//$cb_charge = $requests['charge'];
$cb_apimsgid = $requests['id'];

$fc = "from: $cb_from - to: $cb_to - timestamp: $cb_timestamp - text: $cb_text - status: $cb_status - charge: $cb_charge - apimsgid: $cb_apimsgid\n";
logger_print($fc,"3","Message Media Callback");

if ($cb_timestamp && $cb_from && $cb_text)
{
    //$cb_datetime = date($datetime_format, $cb_timestamp);
    //$sms_datetime = trim($cb_datetime);
    $sms_datetime = $timedata;

    $sms_sender = trim($cb_from);
    $message = trim($cb_text);
    //$message = strtolower($message);
    // Hack for Parking Service from Deisen
    //$r_message = explode("parking ", $message);
    //$message = $r_message[1]; // without parking
    $sms_receiver = trim($cb_to);
    // collected:
    // $sms_datetime, $sms_sender, $message, $sms_receiver
    logger_print("sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".$message, 3, "messagemedia incoming");
    setsmsincomingaction($sms_datetime, $sms_sender, $message, $sms_receiver);
    logger_print("sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".$message, 3, "messagemedia incoming");
}

if ($cb_status && $cb_apimsgid)
{
$db_query = "
        SELECT "._DB_PREF_."_tblSMSOutgoing.smslog_id AS smslog_id,"._DB_PREF_."_tblSMSOutgoing.uid AS uid
        FROM "._DB_PREF_."_tblSMSOutgoing,"._DB_PREF_."_gatewayMessagemedia_apidata
        WHERE
            "._DB_PREF_."_tblSMSOutgoing.smslog_id="._DB_PREF_."_gatewayMessagemedia_apidata.smslog_id AND
            "._DB_PREF_."_gatewayMessagemedia_apidata.apimsgid='$cb_apimsgid'
    ";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    $uid = $db_row['uid'];
logger_print($db_query." - ".$uid,"3","DEBUG");

    setsmsdeliverystatus($cb_apimsgid,$uid,$cb_status);
    // log dlr
/*    $db_query = "SELECT apidata_id FROM "._DB_PREF_."_gatewayMessagemedia_apidata WHERE smslog_id='$cb_apimsgid'";
    $db_result = dba_num_rows($db_query);
    if ($db_result > 0) {
        $db_query = "UPDATE "._DB_PREF_."_gatewayMessagemedia_apidata SET c_timestamp='".mktime()."' WHERE smslog_id='$cb_apimsgid'";
        $db_result = dba_query($db_query);
    } else {
        $db_query = "INSERT INTO "._DB_PREF_."_gatewayMessagemedia_apidata (smslog_id) VALUES ('$cb_apimsgid')";
        $db_result = dba_query($db_query);
    }
*/
}

?>
