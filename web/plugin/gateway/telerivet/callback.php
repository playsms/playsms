<?php
error_reporting(0);

# Prepare environment
chdir("../../../");

# Ignore CSRF
$core_config['init']['ignore_csrf'] = TRUE;

include "init.php";
include $core_config['apps_path']['libs'] . "/function.php";
chdir("plugin/gateway/telerivet/");
$requests = $_REQUEST;

# Log callback request
$log = '';
if (is_array($requests)) {
	foreach ($requests as $key => $val) {
		$log .= $key . ':' . $val . ' ';
	}
	_log("pushed " . $log, 2, "telerivet callback");
}

# Security check on webhook secret
if ($_POST['secret'] !== $plugin_config['telerivet']['status_secret']) {
	header('HTTP/1.1 403 Forbidden');
	echo "Invalid webhook secret";
	exit();
}

if ($_POST['event'] == 'send_status') {
	$c_remote_id = $_POST['id'];
	$c_status = $_POST['status'];
	$c_error = $_POST['error_message'];
	
	# Ref: https://telerivet.com/api/webhook#send_status
	# Available status:
	#    sent    the message has been successfully sent to the mobile network
	#    queued    the message has not been sent yet
	#    failed    the message has failed to send
	#    failed_queued    the message has failed to send, but Telerivet will try to send it again later
	#    delivered    the message has been delivered to the recipient phone (if delivery reports are enabled)
	#    not_delivered    the message could not be delivered (if delivery reports are enabled)
	#    cancelled    the message was cancelled by the user
	

	# Reminder delivery status
	# $p_status = 0 --> pending
	# $p_status = 1 --> sent
	# $p_status = 2 --> failed
	# $p_status = 3 --> delivered
	

	if ($c_remote_id && $c_status) {
		_log("report uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $remote_smslog_id . " status:" . $status, 3, "telerivet callback");
		# Lookup in database
		$db_query = "SELECT local_slid FROM " . _DB_PREF_ . "_gatewayTelerivet WHERE remote_slid='$c_remote_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$smslog_id = $db_row['local_slid'];
		if ($smslog_id) {
			$data = sendsms_get_sms($smslog_id);
			$uid = $data['uid'];
			$p_status = $data['p_status'];
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
			# Change SMS status in database
			dlr($smslog_id, $uid, $p_status);
			ob_end_clean();
		}
	}
	exit();
}

if ($_POST['event'] == 'incoming_message') {
	$c_smsc = 'telerivet';
	$c_remote_id = $_POST['id'];
	$c_message_type = $_POST['message_type'];
	$c_content = htmlspecialchars_decode($_POST['content']);
	$c_from_number = $_POST['from_number'];
	$c_to_number = $_POST['to_number'];
	$c_time_created = $_POST['time_created'];
	$c_time_sent = $_POST['time_sent'];
	$c_contact_id = $_POST['contact_id'];
	$c_phone_id = $_POST['phone_id'];
	$c_service_id = $_POST['service_id'];
	$c_project_id = $_POST['project_id'];
	
	# Convert timestamp to datetime
	$c_time = date('Y-m-d H:i:s', $c_time_sent);
	
	logger_print("incoming smsc:" . $c_smsc . " message_id:" . $c_remote_slid . " s:" . $c_from_number . " d:" . $c_to_number, 2, "telerivet callback");
	recvsms($c_time, $c_from_number, $c_content, $c_to_number, $c_smsc);
	
	# Clean buffers and exit
	ob_end_clean();
	exit();
}
