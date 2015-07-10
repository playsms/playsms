<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

/**
 * hook_sendsms called by sendsms_process()
 *
 * @param string $smsc
 *        SMSC name
 * @param unknown $sms_sender
 *        Sender ID
 * @param string $sms_footer
 *        Message footer
 * @param string $sms_to
 *        Destination number
 * @param string $sms_msg
 *        Message
 * @param integer $uid
 *        User ID
 * @param integer $gpid
 *        Group ID
 * @param integer $smslog_id
 *        SMS Log ID
 * @param integer $sms_type
 *        Type of SMS
 * @param integer $unicode
 *        Unicode flag
 * @return boolean
 */
function uplink_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	
	// global $plugin_config; // global all variables needed, eg: varibles from config.php
	// ...
	// ...
	// return true or false
	// return $ok;
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "uplink_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['uplink']['module_sender']) {
		$sms_sender = $plugin_config['uplink']['module_sender'];
	}
	
	$sms_footer = ($sms_footer ? $sms_footer : stripslashes($sms_footer));
	$sms_msg = stripslashes($sms_msg) . $sms_footer;
	
	$ok = FALSE;
	
	if ($sms_to && $sms_msg) {
		$unicode = (trim($unicode) ? 1 : 0);
		$nofooter = ($plugin_config['uplink']['try_disable_footer'] ? 1 : 0);
		
		$ws = new Playsms\Webservices();
		
		$ws->url = $plugin_config['uplink']['master'] . '/index.php?app=ws';
		$ws->username = $plugin_config['uplink']['username'];
		$ws->token = $plugin_config['uplink']['token'];
		$ws->to = $sms_to;
		$ws->from = $sms_sender;
		$ws->msg = $sms_msg;
		$ws->unicode = $unicode;
		$ws->nofooter = $nofooter;
		
		$ws->sendSms();
		
		_log('sendsms url:[' . $ws->getWebservicesUrl() . '] smsc:[' . $smsc . ']', 3, 'uplink_hook_sendsms');
		
		// in playsms-webservices 1.0.5 and above the data returns as an array, unless a failed response
		$response = $ws->getData();
		if (is_array($response->data)) {
			$data = $response->data[0];
		} else {
			$data = $response;
		}
		
		//_log('data:[' . print_r($data, 1) . ']', 3, 'uplink_hook_sendsms');
		

		if ($data->status == 'OK') {
			if ($data->smslog_id || $data->queue) {
				$db_query = "
				INSERT INTO " . _DB_PREF_ . "_gatewayUplink (up_local_smslog_id,up_remote_smslog_id,up_status,up_remote_queue_code,up_dst)
				VALUES ('$smslog_id','" . $data->smslog_id . "','0','" . $data->queue . "','$sms_to')";
				if ($up_id = @dba_insert_id($db_query)) {
					$ok = TRUE;
					_log('sendsms success. smslog_id:' . $smslog_id . ' remote_smslog_id:' . $data->smslog_id . ' remote_queue:' . $data->queue, 3, 'uplink_hook_sendsms');
				} else {
					$ok = TRUE;
					$p_status = 1; // sent
					dlr($smslog_id, $uid, $p_status);
					_log('sendsms success but unable to save data', 3, 'uplink_hook_sendsms');
					
					return $ok;
				}
			} else {
				_log('sendsms failed no smslog_id or queue', 3, 'uplink_hook_sendsms');
			}
		} else {
			_log('sendsms failed. error:' . $ws->getError() . ' error_string:' . $ws->getErrorString(), 3, 'uplink_hook_sendsms');
		}
	}
	
	if ($ok && ($data->smslog_id || $data->queue)) {
		$p_status = 0; // pending
	} else {
		$p_status = 2; // failed
	}
	dlr($smslog_id, $uid, $p_status);
	
	return $ok;
}

// hook_getsmsstatus
// called by index.php?app=main&inc=daemon (periodic daemon) to set sms status
// no returns needed
// $p_datetime : first sms delivery datetime
// $p_update : last status update datetime
function uplink_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	
	// global $plugin_config;
	// p_status :
	// 0 = pending
	// 1 = delivered
	// 2 = failed
	// dlr($smslog_id,$uid,$p_status);
	global $plugin_config;
	
	$smscs = gateway_getall_smsc_names($plugin_config['uplink']['name']);
	foreach ($smscs as $smsc) {
		$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
		
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayUplink WHERE up_local_smslog_id='$smslog_id'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$local_smslog_id = $db_row['up_local_smslog_id'];
			$remote_smslog_id = $db_row['up_remote_smslog_id'];
			$remote_queue_code = $db_row['up_remote_queue_code'];
			$dst = $db_row['up_dst'];
			
			if ($local_smslog_id && ($remote_smslog_id || ($remote_queue_code && $dst))) {
				
				$ws = new Playsms\Webservices();
				
				$ws->url = $plugin_config['uplink']['master'] . '/index.php?app=ws';
				$ws->username = $plugin_config['uplink']['username'];
				$ws->token = $plugin_config['uplink']['token'];
				$ws->smslog_id = $remote_smslog_id;
				$ws->queue = $remote_queue_code;
				$ws->count = 1;
				
				$ws->getOutgoing();
				
				// _log('getsmsstatus url:[' . $ws->getWebservicesUrl() . '] smsc:[' . $smsc . ']', 3, 'uplink_hook_getsmsstatus');
				

				$response = $ws->getData()->data[0];
				if ($response->status == 2) {
					$p_status = 2;
					dlr($local_smslog_id, $uid, $p_status);
				} else {
					if ($p_status = (int) $response->status) {
						dlr($local_smslog_id, $uid, $p_status);
					}
				}
			}
		}
	}
}
