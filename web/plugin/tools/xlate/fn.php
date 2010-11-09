<?php

/*
 * intercept incoming sms and translate words
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @return
 *   array $ret
 */
function xlate_hook_interceptincomingsms($sms_datetime, $sms_sender, $message) {
    global $core_config;
    $msg = explode(" ", $message);
    $ret = array();
    if (count($msg) > 1) {
	$keyword = trim($msg[0]);
	if (substr($keyword,0,1) == '@') {
	    $xlate = substr($keyword,1);
	    $xlate = explode('2',$xlate);
	    $xlate_from = $xlate[0];
	    $xlate_to = $xlate[1];
	    for ($i=1;$i<count($msg);$i++) {
		$words .= $msg[$i]." ";
	    }
	    $words = trim($words);
	    // contact google
	    $lib = $core_config['apps_path']['plug'].'/tools/xlate/lib/GoogleTranslate';
	    // load JSON.php for PHP version lower than 5.2.x
	    require_once($lib.'/JSON.php');
	    require_once($lib.'/googleTranslate.class.php');
	    if ($gt = new GoogleTranslateWrapper()) {
		/* Translate */
		$xlate_words = $gt->translate($words, $xlate_to, $xlate_from);
		// incoming sms is handled
		$ret['handled'] = true;
		/* Was translation successful */
		if ($gt->isSuccess()) {
		    $reply = 'xlate:'.$xlate_words;
		    logger_print("success dt:".$sms_datetime." s:".$sms_sender." w:".$words." from:".$xlate_from." to:".$xlate_to." xlate:".$xlate_words,3,"xlate");
		} else {
		    $reply = _("Unable to translate").":".$words;
		    logger_print("failed dt:".$sms_datetime." s:".$sms_sender." w:".$words." from:".$xlate_from." to:".$xlate_to,3,"xlate");
		}
		// send reply SMS using admin account
		// should add a web menu in xlate.php to choose which account will be used to send reply SMS
		list($ok,$to,$smslog_id) = sendsms_pv('admin',$sms_sender,$reply,'text',0);
		// usualy here we inspect the result of sendsms_pv, but not this time
	    } else {
		// unable to load the class, set incoming sms unhandled
		$ret['handled'] = false;
		logger_print("class not exists or fail to load",3,"xlate");
	    }
		
	}
    }
    return $ret;
}

?>