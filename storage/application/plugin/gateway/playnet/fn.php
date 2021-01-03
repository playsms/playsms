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
function playnet_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	$ok = FALSE;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "playnet_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['playnet']['module_sender']) {
		$sms_sender = $plugin_config['playnet']['module_sender'];
	}
	
	$sms_footer = stripslashes(htmlspecialchars_decode($sms_footer));
	$sms_msg = stripslashes(htmlspecialchars_decode($sms_msg));
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	$unicode = (trim($unicode) ? 1 : 0);
	//if (!$unicode) {
	//	$unicode = core_detect_unicode($sms_msg);
	//}
	
	if ($sms_to && $sms_msg) {
		$now = core_get_datetime();
		
		$items = array(
			'created' => $now,
			'last_update' => $now,
			'flag' => 1,
			'uid' => $uid,
			'smsc' => $smsc,
			'smslog_id' => $smslog_id,
			'sender_id' => $sms_sender,
			'sms_to' => $sms_to,
			'message' => $sms_msg,
			'sms_type' => $sms_type,
			'unicode' => $unicode 
		);
		if ($id = dba_add(_DB_PREF_ . '_gatewayPlaynet_outgoing', $items)) {
			$ok = TRUE;
		}
	}
	
	if ($ok) {
		$p_status = 0; // pending
	} else {
		$p_status = 2; // failed
	}
	dlr($smslog_id, $uid, $p_status);
	
	return $ok;
}

function playnet_hook_webservices_output($operation, $requests, $returns) {
	global $plugin_config;
	
	$go = $requests['go'];
	$smsc = $requests['smsc'];
	$username = $requests['u'];
	$password = $requests['p'];
	
	if (!($operation == 'playnet' && $go && $smsc && $username && $password)) {
		return FALSE;
	}
	
	$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	// auth remote
	if (!($c_plugin_config['playnet']['local_playnet_username'] && $c_plugin_config['playnet']['local_playnet_password'] && ($c_plugin_config['playnet']['local_playnet_username'] == $username) && ($c_plugin_config['playnet']['local_playnet_password'] == $password))) {
		$content['status'] = 'ERROR';
		$content['error_string'] = 'Authentication failed';
		
		$returns['modified'] = TRUE;
		$returns['param']['content'] = json_encode($content);
		$returns['param']['content-type'] = 'text/json';
		
		return $returns;
	}
	
	switch ($go) {
		case 'get_outgoing':
			$conditions = array(
				'flag' => 1,
				'smsc' => $smsc 
			);
			$extras = array(
				'ORDER BY' => 'id',
				'LIMIT' => $c_plugin_config['playnet']['poll_limit'] 
			);
			$list = dba_search(_DB_PREF_ . '_gatewayPlaynet_outgoing', '*', $conditions, '', $extras);
			foreach ($list as $data) {
				$rows[] = array(
					'smsc' => $data['smsc'],
					'smslog_id' => $data['smslog_id'],
					'uid' => $data['uid'],
					'sender_id' => $data['sender_id'],
					'sms_to' => $data['sms_to'],
					'message' => $data['message'],
					'sms_type' => $data['sms_type'],
					'unicode' => $data['unicode'] 
				);
				
				// update flag
				$items = array(
					'flag' => 2 
				);
				$condition = array(
					'flag' => 1,
					'id' => $data['id'] 
				);
				dba_update(_DB_PREF_ . '_gatewayPlaynet_outgoing', $items, $condition, 'AND');
				
				// update dlr
				$p_status = 1;
				dlr($data['smslog_id'], $data['uid'], $p_status);
			}
			
			if (count($rows)) {
				$content['status'] = 'OK';
				$content['data'] = $rows;
			} else {
				$content['status'] = 'ERROR';
				$content['error_string'] = 'No outgoing data';
			}
			break;
		
		case 'set_incoming':
			$payload = json_decode(stripslashes($requests['payload']), 1);
			
			if ($payload['message']) {
				$sms_sender = $payload['sms_sender'];
				$message = $payload['message'];
				$sms_receiver = $payload['sms_receiver'];
				if ($id = recvsms(core_get_datetime(), $sms_sender, $message, $sms_receiver, $smsc)) {
					$content['status'] = 'OK';
					$content['data'] = array(
						'recvsms_id' => $id 
					);
				} else {
					$content['status'] = 'ERROR';
					$content['error_string'] = 'Unable to save incoming data';
				}
			} else {
				$content['status'] = 'ERROR';
				$content['error_string'] = 'No incoming data';
			}
	}
	
	$returns['modified'] = TRUE;
	$returns['param']['content'] = json_encode($content);
	$returns['param']['content-type'] = 'text/json';
	
	if ($content['status'] == 'OK') {
		_log('accessed param_go:[' . $go . '] param_smsc:[' . $smsc . '] param_u:[' . $username . '] param_p:[' . $password . ']', 3, 'playnet_hook_webservices_output');
	}
	
	return $returns;
}

function playnet_hook_playsmsd() {
	global $core_config, $plugin_config;
	
	if (!core_playsmsd_timer($plugin_config['playnet']['poll_interval'])) {
		return;
	}
	
	$smscs = gateway_getall_smsc_names('playnet');
	foreach ($smscs as $smsc) {
		$c_plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
		
		$is_master = (boolean) ($c_plugin_config['playnet']['local_playnet_username'] && $c_plugin_config['playnet']['local_playnet_password']);
		
		if ((int) $c_plugin_config['playnet']['remote_on'] && !$is_master) {
			
			// fetch from remote
			$ws = $c_plugin_config['playnet']['remote_playsms_url'] . '/index.php?app=ws&op=playnet';
			$ws .= '&go=get_outgoing';
			$ws .= '&smsc=' . $c_plugin_config['playnet']['remote_playnet_smsc'];
			$ws .= '&u=' . $c_plugin_config['playnet']['remote_playnet_username'];
			$ws .= '&p=' . $c_plugin_config['playnet']['remote_playnet_password'];
			$response_raw = @file_get_contents($ws);
			$response = json_decode($response_raw, 1);
			
			// validate response
			if (strtoupper($response['status']) == 'OK') {
				if (is_array($response['data'])) {
					foreach ($response['data'] as $data) {
						$remote_smsc = $data['smsc'];
						$remote_smslog_id = $data['smslog_id'];
						$remote_uid = $data['uid'];
						$username = $c_plugin_config['playnet']['sendsms_username'];
						$sms_to = $data['sms_to'];
						$message = $data['message'];
						$unicode = core_detect_unicode($message);
						$sms_type = $data['sms_type'];
						$sms_sender = $data['sender_id'];
						_log('sendsms remote_smsc:' . $remote_smsc . ' remote_smslog_id:' . $remote_smslog_id . ' remote_uid:' . $remote_uid . ' u:' . $username . ' sender_id:' . $sms_sender . ' to:' . $sms_to . ' m:[' . $message . '] unicode:' . $unicode, 3, 'playnet_hook_playsmsd');
						sendsms_helper($username, $sms_to, $message, $sms_type, $unicode, '', 1, '', $sms_sender);
					}
				}
			}
		}
	}
}
