<?php
defined('_SECURE_') or die('Forbidden');

/**
 * Post billing statement
 * @param integer $smslog_id
 * @param float $rate
 * @param float $credit
 * @return boolean TRUE if posted
 */
function billing_post($smslog_id,$rate,$credit,$count,$charge) {
	global $core_config;
	$ok = false;
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'billing_post',array($smslog_id,$rate,$credit,$count,$charge))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

/**
 * Rollback a posted billing statement
 * @param integer $smslog_id SMS log ID
 * @return boolean TRUE if rollback succeeded
 */
function billing_rollback($smslog_id) {
	global $core_config;
	$ok = false;
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'billing_rollback',array($smslog_id))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

/**
 * Set status that billing process is finalized, called from setsmsdeliverystatus
 * @param integer $smslog_id SMS log ID
 * @return boolean TRUE if finalization succeeded
 */
function billing_finalize($smslog_id) {
	global $core_config;
	$ok = false;
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'billing_finalize',array($smslog_id))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

/**
 * Get billing data or information for specific SMS log ID
 * @param integer $smslog_id SMS log ID
 * @return array Billing information
 */
function billing_getdata($smslog_id) {
	global $core_config;
	$ret = array();
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($ret = x_hook($core_config['toolslist'][$c],'billing_getdata',array($smslog_id))) {
				break;
			}
		}
	}
	return $ret;
}

?>