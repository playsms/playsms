<?php
defined('_SECURE_') or die('Forbidden');

function setsmsdeliverystatus($smslog_id,$uid,$p_status) {
	global $core_config;
	// $p_status = 0 --> pending
	// $p_status = 1 --> sent
	// $p_status = 2 --> failed
	// $p_status = 3 --> delivered
	//logger_print("smslog_id:".$smslog_id." uid:".$uid." p_status:".$p_status, 2, "setsmsdeliverystatus");
	$ok = false;
	$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',p_update='".core_get_datetime()."',p_status='$p_status' WHERE smslog_id='$smslog_id' AND uid='$uid'";
	if ($aff_id = @dba_affected_rows($db_query)) {
		//logger_print("saved smslog_id:".$smslog_id, 2, "setsmsdeliverystatus");
		$ok = true;
		if ($p_status > 0) {
			for ($c=0;$c<count($core_config['toolslist']);$c++) {
				x_hook($core_config['toolslist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
			}
			for ($c=0;$c<count($core_config['featurelist']);$c++) {
				x_hook($core_config['featurelist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
			}
			$gw = gateway_get();
			x_hook($gw,'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
		}
	}
	return $ok;
}

?>