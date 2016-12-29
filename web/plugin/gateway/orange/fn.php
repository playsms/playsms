<?php
defined('_SECURE_') or die('Forbidden');


function parseHeaders( $headers )
{
	// from http://php.net/manual/en/reserved.variables.httpresponseheader.php
    $head = array();
    foreach( $headers as $k=>$v )
    {
        $t = explode( ':', $v, 2 );
        if( isset( $t[1] ) )
            $head[ trim($t[0]) ] = trim( $t[1] );
        else
        {
            $head[] = $v;
            if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                $head['reponse_code'] = intval($out[1]);
        }
    }
    return $head;
}


function orange_callapi($headers, $args, $url, $method='POST', $successCode=200, $jsonEncodeArgs = false){
	// thanks to Ismael https://github.com/ismaeltoe/osms-php/blob/master/src/Osms.php
	$options = array(
	    'http' => array(
	        'header'  => join("\r\n", empty($headers)? array('Content-type: application/x-www-form-urlencoded'): $headers),
	        'method'  => $method
	    )
	);

	if(!empty($args)){
		if($method === "POST" && $jsonEncodeArgs === true){
			$options['http']['content'] = json_encode($args);
		}else{
			$options['http']['content'] = http_build_query($args);
		}
	}

	$context  = stream_context_create($options);
	$data = file_get_contents($url, false, $context);

	$http_header  = parseHeaders($http_response_header);

	if($data === false){
		return array('{"error": "API Failed with file_get_error"}');
	}

	$response =json_decode($data);

    $jsonErrorCode = json_last_error();
    if ($jsonErrorCode !== JSON_ERROR_NONE) {
        return  json_decode('
            {"error": "API response not well-formed (json error code: '
                . $jsonErrorCode . ')"}');
    }

    if ($http_header['reponse_code'] !== $successCode) {
        $errorMessage = '';

        if (!empty($response->{'error_description'})) {
            $errorMessage = $response->{'error_description'};
        } elseif (!empty($response->{'error'})) {
            $errorMessage = $response->{'error'};
        } elseif (!empty($response->{'description'})) {
            $errorMessage = $response->{'description'};
        } elseif (!empty($response->{'message'})) {
            $errorMessage = $response->{'message'};
        } elseif (!empty($response->{'requestError'}->{'serviceException'})) {
            $errorMessage = $response->{'requestError'}->{'serviceException'}->{'text'}
                . ' ' . $response->{'requestError'}->{'serviceException'}->{'variables'};
        } elseif (!empty($response->{'requestError'}->{'policyException'})) {
            $errorMessage = $response->{'requestError'}->{'policyException'}->{'text'}
                . ' ' . $response->{'requestError'}->{'policyException'}->{'variables'};
        }
        return json_decode('{"error": "' . $errorMessage.'"}');
    }
    return $response;
}

function orange_get_token($clientId, $clientSecret, $base_url='https://api.orange.com'){
	$url= $base_url.'/oauth/v2/token';
	$credentials = $clientId. ':' . $clientSecret;
	$headers = array('Authorization: Basic ' . base64_encode($credentials));
	$args = array('grant_type' => 'client_credentials');
	$response = orange_callapi($headers, $args, $url, 'POST');
	if(!empty($response->{'access_token'})){
		return $response;
	}
	return null;
}

function orange_is_token_valide($smscName, $token_updated_at=0, $token_expirates_in=0, $smscLastUpdate=""){
	$last_update = strval(gateway_get_smscbyname($smscName)['last_update']);

	if($token_updated_at === 0 || $token_updated_at > mktime() || $last_update !== $smscLastUpdate)
		return false;
	$now_time = mktime();
	$expire_time = strtotime("+". $token_expirates_in. " seconds", $token_updated_at);
	$seconds_remaining =  $expire_time - $now_time;
	_log(" remaining seconds = ".$seconds_remaining, 2, 'orange_token_update');
	// refresh token if there is only 1 hour before expiration date
	return  $seconds_remaining > 360 ? true: false;
}

function orange_update_smsc_token($smscName, $token, $expires_in){
	$smsc = gateway_get_smscbyname($smscName);
	$c_data = json_decode($smsc['data']);
	$c_data->{'token'} = $token;
	$c_data->{'token_expirates_in'}= $expires_in;
	$c_data->{'token_updated_at'} = mktime();
	$c_data->{'smsc_last_update'} = core_get_datetime();

	$db_table = _DB_PREF_ . '_tblGateway';
	$items = array(
		'last_update' => $c_data->{'smsc_last_update'},
		'data' => json_encode($c_data) 
	);
	$condition = array(
		'id' => intval($smsc['id']) 
	);
	if ($new_id = dba_update($db_table, $items, $condition)) {
		_log("SMSC(" . $smscName . ") token has been updated", 2, "orange_token_update");
		usleep(500000);
	} else {
		_log("FAIL to update SMSC(" . $smscName . ") token", 2, "orange_token_update");
	}
}


function orange_refresh_token($smsc, $token_updated_at=0, $token_expirates_in=0, $clientId, $clientSecret, $smsc_last_update){
	if(orange_is_token_valide($smsc, $token_updated_at, $token_expirates_in, $smsc_last_update)){
		return null; 
	}
	_log("Start refreshing token ", 2, "orange_token_update");
	// Get the new token
	$response = orange_get_token($clientId, $clientSecret);
	if(!empty($response->{'error'})){
		_log("Refresh token errror :".$response->{'error'}, 2, "orange_token_update");
		return null;
	}
	$token = $response->{'access_token'};
	$expires_in = intval($response->{'expires_in'});
	orange_update_smsc_token($smsc, $token, $expires_in);
	return $token;
}


function orange_sendsms($senderAddress, $receiverAddress, $message, $token, $senderName ="", $base_url='https://api.orange.com'){
	// thanks to Ismael https://github.com/ismaeltoe/osms-php/blob/master/src/Osms.php
	$url = $base_url . '/smsmessaging/v1/outbound/' . urlencode(substr($senderAddress, 0,3) === "tel:"? $senderAddress: "tel:".$senderAddress )
            . '/requests';

    $headers = array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    );
    if (!empty($senderName)) {
        $args = array(
            'outboundSMSMessageRequest' => array(
                'address'                   => substr($receiverAddress, 0,3) === "tel:"? $receiverAddress: "tel:".$receiverAddress,
                'senderAddress'             => substr($senderAddress, 0,3) === "tel:"? $senderAddress: "tel:".$senderAddress,
                'senderName'                => empty($senderName)? $senderAddress: $senderName,
                'outboundSMSTextMessage'    => array(
                    'message' => $message
                )
            )
        );
    } else {
        $args = array(
            'outboundSMSMessageRequest' => array(
                'address'                   => substr($receiverAddress, 0,3) === "tel:"? $receiverAddress: "tel:".$receiverAddress,
                'senderAddress'             => substr($senderAddress, 0,3) === "tel:"? $senderAddress: "tel:".$senderAddress,
                'senderName'                => empty($senderName)? $senderAddress: $senderName,
                'outboundSMSTextMessage'    => array(
                    'message' => $message
                )
            )
        );
    }
    $response = orange_callapi($headers, $args, $url, 'POST', 201, true);
    return $response;
}

function orange_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	// Based on http://www.orange.com/int/docs/eapi/submission/send_sms/
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "orange_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$module_sms_sender = stripslashes($sms_sender);
	$sender_address= '';

	$token = '';
	$token_updated_at = 0;
	$token_expirates_in = 0;
	$smsc_last_update = "";

	if ($plugin_config['orange']['sender_name']) {
		$module_sms_sender = $plugin_config['orange']['sender_name'];
	}

	if ($plugin_config['orange']['sender_address']) {
		$sender_address = $plugin_config['orange']['sender_address'];
	}

	if ($plugin_config['orange']['token']) {
		$token = $plugin_config['orange']['token'];
	}
	
	if ($plugin_config['orange']['token_expirates_in']) {
		$token_expirates_in = intval($plugin_config['orange']['token_expirates_in']);
	}
	
	if ($plugin_config['orange']['token_updated_at']) {
		$token_updated_at = intval($plugin_config['orange']['token_updated_at']);
	}

	if ($plugin_config['orange']['smsc_last_update']) {
		$smsc_last_update = $plugin_config['orange']['smsc_last_update'];
	}
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$set_sms_from = ($sms_sender ? $sms_sender : $module_sms_sender);


	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	// failed
	$p_status = 2;  // 0= pending, 1 == sent, 2 = failed 
	$ok= false;
	// refresh token
	$refreshed_token = orange_refresh_token($smsc, $token_updated_at, $token_expirates_in, $plugin_config['orange']['client_id'], $plugin_config['orange']['client_secret'], $smsc_last_update);
	if($refreshed_token){
		$token = $refreshed_token;
	}

	$response = orange_sendsms($sender_address, $sms_to, $sms_msg, $token, $set_sms_from);
	if(empty($response->{'error'})){
		//delivered
		$ok= true;
		$p_status = 3;
		_log("smslog_id:" . $smslog_id . " sms_status:" . $p_status . " response: OK", 2, "orange  outgoing");
	}else{
		_log("smslog_id:" . $smslog_id . " sms_status:" . $p_status . " response: FAILED ".$response->{'error'}, 2, "orange  outgoing");		
	}

	dlr($smslog_id, $uid, $p_status);
	return $ok;
}



function orange_hook_call($requests) {
	// please note that we must globalize these 2 variables
	global $core_config, $plugin_config;
	$called_from_hook_call = true;
	$access = $requests['access'];
	if ($access == 'callback') {
		$fn = $core_config['apps_path']['plug'] . '/gateway/orange/callback.php';
		_log("start load:" . $fn, 2, "orange call");
		include $fn;
		_log("end load callback", 2, "orange call");
	}
}

function orange_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	global $plugin_config;
	list($c_sms_credit, $c_sms_status) = orange_getsmsstatus($smslog_id);
	// pending
	$p_status = 0;
	if ($c_sms_status) {
		$p_status = $c_sms_status;
	}
	dlr($smslog_id, $uid, $p_status);
}

function orange_getsmsstatus($smslog_id) {
	global $plugin_config;
	$c_sms_status = 2;
	$c_sms_credit = 1;
	return array(
		$c_sms_credit,
		$c_sms_status 
	);
}