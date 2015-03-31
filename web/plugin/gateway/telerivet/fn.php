<?php
defined('_SECURE_') or die('Forbidden');

function telerivet_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	$ok = false;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 2, "telerivet_hook_sendsms");
	
	# Initialize CURL context
	$api_key = $plugin_config['telerivet']['api_key'];
	$project_id = $plugin_config['telerivet']['project_id'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://api.telerivet.com/v1/projects/$project_id/messages/outgoing");
	curl_setopt($curl, CURLOPT_USERPWD, "{$api_key}:");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
	# override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	# Pre-process parameters
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg) . $sms_footer;
	
	# Build data array
	$data = array();
	$data['to_number'] = $sms_to;
	$data['content'] = $sms_msg;
	
	if (trim($plugin_config['telerivet']['status_url'])) {
		$data['status_url'] = trim($plugin_config['telerivet']['status_url']);
	}
	if (trim($plugin_config['telerivet']['status_secret'])) {
		$data['status_secret'] = trim($plugin_config['telerivet']['status_secret']);
	}
	
	# Build API query
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
	_log('building http query', 3, 'telerivet_query');
	
	# Send query to API and get result
	$json = curl_exec($curl);
	$network_error = curl_error($curl);
	curl_close($curl);
	
	# Catch query error
	if ($network_error) {
		_log('curl error:' . $network_error, 2, 'telerivet_query');
		dlr($smslog_id, $uid, 2);
	} else {
		# Save JSON reply
		$res = json_decode($json, true);
		if (is_array($res)) {
			foreach ($res as $key => $val) {
				$log .= $key . ':' . $val . ' ';
			}
			_log('api response:' . $log, 3, 'telerivet_query');
		}
		# Catch API errors
		if (isset($res['error'])) {
			_log('api error' . $res['error_message'], 2, 'telerivet_query');
			dlr($smslog_id, $uid, 2);
		} else {
			_log('api success: id:' . $res['id'] . ' status:' . $res['status'] . ' source:' . $res['source'], 2, 'telerivet_query');
		}
		
		$c_remote_id = $res['id'];
		$c_status = $res['status'];
		$c_phone_id = $res['phone_id'];
		$c_message_type = $res['message_type'];
		$c_source = $res['source'];
		$c_error = $res['error_message'];
		
		# Ref: https://telerivet.com/api/webhook#send_status
		# Available status:
		#   sent    the message has been successfully sent to the mobile network
		#   queued  the message has not been sent yet
		#   failed  the message has failed to send
		#   failed_queued   the message has failed to send, but Telerivet will try to send it again later
		#   delivered   the message has been delivered to the recipient phone (if delivery reports are enabled)
		#   not_delivered   the message could not be delivered (if delivery reports are enabled)
		#   cancelled   the message was cancelled by the user
		

		# Reminder delivery status
		# $p_status = 0 --> pending
		# $p_status = 1 --> sent
		# $p_status = 2 --> failed
		# $p_status = 3 --> delivered
		

		if ($c_remote_id && $c_status) {
			$db_query = '
                INSERT INTO ' . _DB_PREF_ . '_gatewayTelerivet (local_slid, remote_slid, status, phone_id, message_type, source, error_text)
                VALUES (' . $smslog_id . ',"' . $c_remote_id . '","' . $c_status . '","' . $c_phone_id . '","' . $c_message_type . '","' . $c_source . '","' . $c_error . '")';
			_log('sql:' . $db_query, 3, 'telerivet query');
			if ($id = @dba_insert_id($db_query) && $c_status) {
				switch ($c_status) {
					case "queued":
						$ok = true;
						$p_status = 0;
						break;
					case "sent":
						$ok = true;
						$p_status = 1;
						break;
					case "delivered":
						$ok = true;
						$p_status = 3;
						break;
					case "failed":
					case "failed_queued":
					case "not_delivered":
					case "cancelled":
					default :
						$p_status = 2;
						break; // failed
				}
			}
			dlr($smslog_id, $uid, $p_status);
		}
	}
	
	return $ok;
}
