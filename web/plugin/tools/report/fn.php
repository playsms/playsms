<?php

function resendsms($smslog_id,$mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
    global $datetime_now, $core_config, $gateway_module;

    // make sure sms_datetime is in supported format and in GMT+0
    // timezone used for outgoing message is not module timezone, but gateway timezone
    // module gateway may have set already to +0000 (such kannel and clickatell)
    $sms_datetime = core_adjust_datetime($core_config['datetime']['now'], $core_config['main']['cfg_datetime_timezone']);

    $ok = false;
    $username = uid2username($uid);
    $sms_to = sendsms_getvalidnumber($sms_to);
    logger_print("start", 3, "resendsms");
    if (rate_cansend($username, $sms_to)) {
        // fixme anton - its a total mess ! need another DBA
        $sms_sender = addslashes($sms_sender);
        $sms_msg = addslashes($sms_msg);
        // we save all info first and then process with gateway module
        // the thing about this is that message saved may not be the same since gateway may not be able to process
        // message with that length or certain characters in the message are not supported by the gateway
        $db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='" . mktime() . "', p_datetime='$sms_datetime', p_status=0 WHERE smslog_id='$smslog_id'";

        logger_print("saving:$uid,$gpid,$gateway_module,$mobile_sender,$sms_to,$sms_type,$unicode", 3, "resendsms");
        // continue to gateway only when save to db is true
        if (@dba_affected_rows($db_query)) {
            logger_print("smslog_id:".$smslog_id." saved", 3, "resendsms");
            // fixme anton - another mess !
            $sms_sender = stripslashes($sms_sender);
            $sms_msg = stripslashes($sms_msg);
            if (x_hook($gateway_module, 'sendsms', array($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid,$smslog_id,$sms_type,$unicode))) {
                // fixme anton - deduct user's credit as soon as gateway returns true
                rate_deduct($smslog_id);
                $ok = true;
            }
        }
    }
    $ret['status'] = $ok;
    $ret['smslog_id'] = $smslog_id;
    return $ret;
}



?>
