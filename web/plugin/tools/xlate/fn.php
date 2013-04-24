<?php
defined('_SECURE_') or die('Forbidden');

/*
 * intercept incoming sms and translate words
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @param $sms_receiver
 *   receiver number that is receiving incoming SMS
 * @return
 *   array $ret
 */
function xlate_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
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
			if ($xlate_from && $xlate_to && (strlen($xlate_from)==2) && (strlen($xlate_to)==2)) {
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
					$ret['hooked'] = true;
					/* Was translation successful */
					$sms_datetime = core_display_datetime($sms_datetime);
					if ($gt->isSuccess()) {
						$reply = '@'.$xlate_from.'2'.$xlate_to.' '.$words.' => '.$xlate_words;
						logger_print("success dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." w:".$words." from:".$xlate_from." to:".$xlate_to." xlate:".$xlate_words, 2, "xlate");
					} else {
						$reply = '@'.$xlate_from.'2'.$xlate_to.' '._("unable to translate").': '.$words;
						logger_print("failed dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." w:".$words." from:".$xlate_from." to:".$xlate_to, 2, "xlate");
					}
					// detect reply message, set unicode if not ASCII
					$unicode = core_detect_unicode($reply);
					// send reply SMS using admin account
					// should add a web menu in xlate.php to choose which account will be used to send reply SMS
					// usualy we inspect the result of sendsms, but not this time
					logger_print("send reply encoding:".$encoding, 2, "xlate");
					list($ok, $to, $smslog_id, $queue) = sendsms('admin', $sms_sender, $reply, 'text', $unicode);
					// do not forget to tell parent that this SMS has been hooked
					$ret['hooked'] = true;
				} else {
					// unable to load the class, set incoming sms unhandled
					$ret['hooked'] = false;
					logger_print("class not exists or fail to load", 2, "xlate");
				}
			}
		}
	}
	return $ret;
}

?>