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

function recvsms($sms_datetime, $sms_sender, $message, $sms_receiver = "", $smsc = '')
{
	global $core_config;

	if ($core_config['isrecvsmsd']) {
		$c_isrecvsmsd = 1;

		// save to db and mark as queued (flag_processed = 1)
		$ret = dba_add(
			_DB_PREF_ . '_tblRecvSMS',
			[
				'flag_processed' => 1,
				'sms_datetime' => core_adjust_datetime($sms_datetime),
				'sms_sender' => $sms_sender,
				'message' => $message,
				'sms_receiver' => $sms_receiver,
				'smsc' => $smsc
			]
		);
	} else {
		$c_isrecvsmsd = 0;

		// save to db but mark as processed (flag_processed = 2) and then directly call recvsms_process()
		$ret = dba_add(
			_DB_PREF_ . '_tblRecvSMS',
			[
				'flag_processed' => 2,
				'sms_datetime' => core_adjust_datetime($sms_datetime),
				'sms_sender' => $sms_sender,
				'message' => $message,
				'sms_receiver' => $sms_receiver,
				'smsc' => $smsc
			]
		);

		recvsms_process(core_display_datetime($sms_datetime), $sms_sender, $message, $sms_receiver, $smsc);
	}
	_log("isrecvsmsd:" . $c_isrecvsmsd . " dt:" . $sms_datetime . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . " smsc:" . $smsc, 3, "recvsms");

	return $ret;
}

function recvsmsd()
{
	global $core_config;

	$core_config['recvsmsd_limit'] = (int) $core_config['recvsmsd_limit'] ? (int) $core_config['recvsmsd_limit'] : 200;

	$list = dba_search(
		_DB_PREF_ . '_tblRecvSMS',
		'*',
		[
			'flag_processed' => 1
		],
		[],
		[
			'LIMIT' => $core_config['recvsmsd_limit']
		]
	);

	$c_count = is_array($list) ? $c_count = count($list) : 0;
	$j = 0;
	for ($j = 0; $j < $c_count; $j++) {
		if ($id = $list[$j]['id']) {
			$sms_datetime = $list[$j]['sms_datetime'];
			$sms_sender = $list[$j]['sms_sender'];
			$message = $list[$j]['message'];
			$sms_receiver = $list[$j]['sms_receiver'];
			$smsc = $list[$j]['smsc'];
			if (
				dba_update(
					_DB_PREF_ . '_tblRecvSMS',
					[
						'flag_processed' => 2
					],
					[
						'id' => $id
					]
				)
			) {
				_log("id:" . $id . " dt:" . core_display_datetime($sms_datetime) . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . " smsc:" . $smsc, 3, "recvsmsd");

				recvsms_process(core_display_datetime($sms_datetime), $sms_sender, $message, $sms_receiver, $smsc);
			}
		}
	}
}

function recvsms_process_before($sms_datetime, $sms_sender, $message, $sms_receiver = '', $smsc = '')
{
	global $core_config;

	$ret = [];
	$ret_final = [];

	// feature list
	if (!(is_array($core_config['plugins']['list']['feature']) && $core_config['plugins']['list']['feature'])) {

		return $ret_final;
	}

	foreach ( $core_config['plugins']['list']['feature'] as $c_feature ) {
		$ret = core_hook(
			$c_feature,
			'recvsms_process_before',
			[
				$sms_datetime,
				$sms_sender,
				$message,
				$sms_receiver,
				$smsc
			]
		);
		if (isset($ret['modified']) && $ret['modified']) {
			$sms_datetime = isset($ret['param']['sms_datetime']) ? $ret['param']['sms_datetime'] : $sms_datetime;
			$sms_sender = isset($ret['param']['sms_sender']) ? $ret['param']['sms_sender'] : $sms_sender;
			$message = isset($ret['param']['message']) ? $ret['param']['message'] : $message;
			$sms_receiver = isset($ret['param']['sms_receiver']) ? $ret['param']['sms_receiver'] : $sms_receiver;
			$smsc = isset($ret['param']['smsc']) ? $ret['param']['smsc'] : $smsc;
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['smsc'] = $ret['param']['smsc'];
			$ret_final['modified'] = true;
		}
		$ret_final['uid'] = isset($ret['uid']) ? (int) $ret['uid'] : 0;
		$ret_final['hooked'] = isset($ret['hooked']) && $ret['hooked'] ? true : false;
		$ret_final['cancel'] = isset($ret['cancel']) && $ret['cancel'] ? true : false;

		if ($ret_final['cancel']) {

			return $ret_final;
		}
	}

	return $ret_final;
}

function recvsms_process_after($sms_datetime, $sms_sender, $message, $sms_receiver, $feature, $status, $uid, $smsc = '')
{
	global $core_config;

	$ret = [];
	$ret_final = [];

	// feature list
	if (!(is_array($core_config['plugins']['list']['feature']) && $core_config['plugins']['list']['feature'])) {

		return $ret_final;
	}

	foreach ( $core_config['plugins']['list']['feature'] as $c_feature ) {
		$ret = core_hook(
			$c_feature,
			'recvsms_process_after',
			[
				$sms_datetime,
				$sms_sender,
				$message,
				$sms_receiver,
				$feature,
				$status,
				$uid,
				$smsc
			]
		);
		if (isset($ret['modified']) && $ret['modified']) {
			$sms_datetime = isset($ret['param']['sms_datetime']) ? $ret['param']['sms_datetime'] : $sms_datetime;
			$sms_sender = isset($ret['param']['sms_sender']) ? $ret['param']['sms_sender'] : $sms_sender;
			$message = isset($ret['param']['message']) ? $ret['param']['message'] : $message;
			$sms_receiver = isset($ret['param']['sms_receiver']) ? $ret['param']['sms_receiver'] : $sms_receiver;
			$smsc = isset($ret['param']['smsc']) ? $ret['param']['smsc'] : $smsc;
			$feature = isset($ret['param']['feature']) ? $ret['param']['feature'] : $feature;
			$status = isset($ret['param']['status']) ? $ret['param']['status'] : $status;
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['feature'] = $ret['param']['feature'];
			$ret_final['param']['status'] = $ret['param']['status'];
			$ret_final['param']['smsc'] = $ret['param']['smsc'];
			$ret_final['modified'] = true;
		}
		$ret_final['uid'] = isset($ret['uid']) ? (int) $ret['uid'] : 0;
		$ret_final['hooked'] = isset($ret['hooked']) && $ret['hooked'] ? true : false;
		$ret_final['cancel'] = isset($ret['cancel']) && $ret['cancel'] ? true : false;

		if ($ret_final['cancel']) {

			return $ret_final;
		}
	}

	return $ret_final;
}

function recvsms_process($sms_datetime, $sms_sender, $message, $sms_receiver = '', $smsc = '')
{
	global $core_config;

	// blacklist
	if (blacklist_mobile_isexists(0, $sms_sender)) {
		_log("incoming SMS discarded sender is in the blacklist datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:[" . $message . "]  smsc:" . $smsc, 3, "recvsms_process");

		return false;
	}

	// incoming sms will be handled by plugins first
	$ret_intercept = recvsms_process_before($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
	if ($ret_intercept['modified']) {
		$sms_datetime = isset($ret_intercept['param']['sms_datetime']) ? $ret_intercept['param']['sms_datetime'] : $sms_datetime;
		$sms_sender = isset($ret_intercept['param']['sms_sender']) ? $ret_intercept['param']['sms_sender'] : $sms_sender;
		$message = isset($ret_intercept['param']['message']) ? $ret_intercept['param']['message'] : $message;
		$sms_receiver = isset($ret_intercept['param']['sms_receiver']) ? $ret_intercept['param']['sms_receiver'] : $sms_receiver;
		$smsc = isset($ret_intercept['param']['smsc']) ? $ret_intercept['param']['smsc'] : $smsc;
	}

	// set active gateway module as default gateway
	// if (!$smsc) {
	// $smsc = core_smsc_get();
	// }

	// log it
	_log("dt:" . $sms_datetime . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . ' smsc:' . $smsc, 3, "recvsms_process");

	// if hooked function returns cancel=true then stop the processing incoming sms, return false
	if ($ret_intercept['cancel']) {
		_log("cancelled datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:[" . $message . "]  smsc:" . $smsc, 3, "recvsms_process");

		return false;
	}

	// log a warning for unknown supplied SMSC
	if ($smsc) {
		$smsc_data = gateway_get_smscbyname($smsc);
		if (!$smsc_data['name']) {
			_log('unknown supplied SMSC smsc:[' . $smsc . ']', 3, "recvsms_process");
		}
	}

	$c_uid = 0;
	$c_feature = "";
	$ok = false;

	$raw_message = $message;
	$keyword_separator = $core_config['main']['keyword_separator'] ? $core_config['main']['keyword_separator'] : ' ';
	$message_array = explode($keyword_separator, $message, 2);
	$target_keyword = isset($message_array[0]) ? strtoupper(trim($message_array[0])) : '';
	$message = isset($message_array[1]) ? $message_array[1] : '';

	switch ($target_keyword) {
		case "BC":
			$c_uid = user_mobile2uid($sms_sender);
			$c_username = user_uid2username($c_uid);
			$c_feature = 'core';
			$array_target_group = explode($keyword_separator, $message, 2);
			$target_group = isset($array_target_group[0]) ? strtoupper(trim($array_target_group[0])) : '';
			$list = phonebook_search_group($c_uid, $target_group);
			$c_gpid = isset($list[0]['gpid']) ? $list[0]['gpid'] : 0;
			$message = isset($array_target_group[1]) ? $array_target_group[1] : '';
			_log("bc username:" . $c_username . " gpid:" . $c_gpid . " group:" . $target_group . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message . " raw:" . $raw_message, 3, "recvsms_process");
			if ($c_username && $c_gpid && $message && $target_group) {
				list($ok, $to, $smslog_id, $queue) = sendsms_bc($c_username, $c_gpid, $message);
				$ok = true;
			} else {
				_log('bc has failed due to missing option u:' . $c_username . ' gpid:' . $c_gpid . ' group:[' . $target_group . '] m:[' . $message . ']', 3, 'recvsms_process');
			}

			break;

		default:
			if (!(is_array($core_config['plugins']['list']['feature']) && isset($core_config['plugins']['list']['feature']))) {
				_log("feature not available", 2, "recvsms_process");

				break;
			}
			foreach ( $core_config['plugins']['list']['feature'] as $c_feature ) {
				$ret = core_hook(
					$c_feature,
					'recvsms_process',
					array(
						$sms_datetime,
						$sms_sender,
						$target_keyword,
						$message,
						$sms_receiver,
						$smsc,
						$raw_message
					)
				);
				if ($ok = $ret['status']) {
					$c_uid = $ret['uid'];
					_log("feature:" . $c_feature . " datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " keyword:" . $target_keyword . " message:" . $message . " raw:" . $raw_message . " smsc:" . $smsc, 3, "recvsms_process");

					break;
				}
			}
	}
	$c_status = $ok ? 1 : 0;
	if ($c_status === 0) {
		$c_feature = '';
		$target_keyword = '';
		$message = $raw_message;

		// from recvsms_process_before(), force status as 'handled'
		if ($ret_intercept['hooked']) {
			$c_status = 1;
			if ($ret_intercept['uid']) {
				$c_uid = $ret_intercept['uid'];
			}
			_log("intercepted datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message, 3, "recvsms_process");
		} else {
			_log("unhandled datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message, 3, "recvsms_process");
		}
	}

	// incoming sms intercept after
	unset($ret_intercept);
	$ret_intercept = recvsms_process_after($sms_datetime, $sms_sender, $message, $sms_receiver, $c_feature, $c_status, $c_uid, $smsc);
	if ($ret_intercept['modified']) {
		$sms_datetime = isset($ret_intercept['param']['sms_datetime']) ? $ret_intercept['param']['sms_datetime'] : $sms_datetime;
		$sms_sender = isset($ret_intercept['param']['sms_sender']) ? $ret_intercept['param']['sms_sender'] : $sms_sender;
		$message = isset($ret_intercept['param']['message']) ? $ret_intercept['param']['message'] : $message;
		$sms_receiver = isset($ret_intercept['param']['sms_receiver']) ? $ret_intercept['param']['sms_receiver'] : $sms_receiver;
		$smsc = isset($ret_intercept['param']['smsc']) ? $ret_intercept['param']['smsc'] : $smsc;
		$c_uid = isset($ret_intercept['uid']) ? (int) $ret_intercept['uid'] : $c_uid;
		$c_feature = isset($ret_intercept['param']['feature']) ? $ret_intercept['param']['feature'] : $c_feature;
		$c_status = isset($ret_intercept['param']['status']) ? (int) $ret_intercept['param']['status'] : $c_status;
	}

	// fixme anton - all incoming messages set to user with uid=1 if no one owns it
	$c_uid = $c_uid ? $c_uid : 1;

	$db_query = "
		INSERT INTO " . _DB_PREF_ . "_tblSMSIncoming
		(in_uid,in_feature,in_gateway,in_sender,in_receiver,in_keyword,in_message,in_datetime,in_status)
		VALUES (?,?,?,?,?,?,?,?,?)";
	$db_argv = [
		$c_uid,
		$c_feature,
		$smsc,
		$sms_sender,
		$sms_receiver,
		$target_keyword,
		$message,
		core_adjust_datetime($sms_datetime),
		$c_status,
	];
	dba_query($db_query, $db_argv);

	return $ok;
}

function recvsms_inbox_add_intercept($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver = "", $reference_id = '')
{
	global $core_config;

	$ret = [];
	$ret_final = [];

	// feature list
	if (!(is_array($core_config['plugins']['list']['feature']) && $core_config['plugins']['list']['feature'])) {

		return $ret_final;
	}

	foreach ( $core_config['plugins']['list']['feature'] as $c_feature ) {
		$ret = core_hook(
			$c_feature,
			'recvsms_inbox_add_intercept',
			[
				$sms_datetime,
				$sms_sender,
				$target_user,
				$message,
				$sms_receiver,
				$reference_id
			]
		);
		if ($ret['modified']) {
			$sms_datetime = isset($ret['param']['sms_datetime']) ? $ret['param']['sms_datetime'] : $sms_datetime;
			$sms_sender = isset($ret['param']['sms_sender']) ? $ret['param']['sms_sender'] : $sms_sender;
			$target_user = isset($ret['param']['target_user']) ? $ret['param']['target_user'] : $target_user;
			$message = isset($ret['param']['message']) ? $ret['param']['message'] : $message;
			$sms_receiver = isset($ret['param']['sms_receiver']) ? $ret['param']['sms_receiver'] : $sms_receiver;
			$reference_id = isset($ret['param']['reference_id']) ? $ret['param']['reference_id'] : $reference_id;
			$feature = isset($ret['param']['feature']) ? $ret['param']['feature'] : $feature;
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['target_user'] = $ret['param']['target_user'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['reference_id'] = $ret['param']['reference_id'];
			$ret_final['param']['feature'] = $ret['param']['feature'];
			$ret_final['modified'] = true;
		}
		$ret_final['uid'] = isset($ret['uid']) ? (int) $ret['uid'] : 0;
		$ret_final['hooked'] = isset($ret['hooked']) && $ret['hooked'] ? true : false;
		$ret_final['cancel'] = isset($ret['cancel']) && $ret['cancel'] ? true : false;

		if ($ret_final['cancel']) {

			return $ret_final;
		}
	}

	return $ret_final;
}

function recvsms_inbox_add($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver = "", $reference_id = '')
{
	global $core_config;

	// sms to inbox will be handled by plugins first
	$ret_intercept = recvsms_inbox_add_intercept($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver, $reference_id);
	if ($ret_intercept['modified']) {
		$sms_datetime = isset($ret_intercept['param']['sms_datetime']) ? $ret_intercept['param']['sms_datetime'] : $sms_datetime;
		$sms_sender = isset($ret_intercept['param']['sms_sender']) ? $ret_intercept['param']['sms_sender'] : $sms_sender;
		$target_user = isset($ret_intercept['param']['target_user']) ? $ret_intercept['param']['target_user'] : $target_user;
		$message = isset($ret_intercept['param']['message']) ? $ret_intercept['param']['message'] : $message;
		$sms_receiver = isset($ret_intercept['param']['sms_receiver']) ? $ret_intercept['param']['sms_receiver'] : $sms_receiver;
		$reference_id = isset($ret_intercept['param']['reference_id']) ? $ret_intercept['param']['reference_id'] : $reference_id;
	}

	$ok = false;
	if ($sms_sender && $target_user && $message) {
		$user = user_getdatabyusername($target_user);
		if ($uid = $user['uid']) {

			// discard if banned
			if (user_banned_get($uid)) {
				_log("user banned, message ignored uid:" . $uid, 2, "recvsms_inbox_add");
				return false;
			}

			// get name from target_user's phonebook
			$c_name = '';
			if (substr($sms_sender, 0, 1) == '@') {
				$c_username = str_replace('@', '', $sms_sender);
				$c_name = user_getfieldbyusername($c_username, 'name');
			} else {
				$c_name = phonebook_number2name($uid, $sms_sender);
			}
			$sender = $c_name ? $c_name . ' (' . $sms_sender . ')' : $sms_sender;

			// forward to inbox
			if ($user['fwd_to_inbox']) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_tblSMSInbox
					(in_sender,in_receiver,in_uid,in_msg,in_datetime,reference_id)
					VALUES (?,?,?,?,?,?)
				";
				$db_argv = [
					$sms_sender,
					$sms_receiver,
					$uid,
					$message,
					core_adjust_datetime($sms_datetime),
					$reference_id,
				];
				_log("saving sender:" . $sms_sender . " receiver:" . $sms_receiver . " target:" . $target_user . " reference_id:" . $reference_id, 2, "recvsms_inbox_add");
				if ($inbox_id = @dba_insert_id($db_query, $db_argv)) {
					_log("saved id:" . $inbox_id . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " target:" . $target_user, 2, "recvsms_inbox_add");

					$ok = true;
				}
			}

			// forward to email, consider site config too
			if ($parent_uid = user_getparentbyuid($uid)) {
				$site_config = site_config_get($parent_uid);
			}

			$web_title = isset($site_config['web_title']) && $site_config['web_title'] ? $site_config['web_title'] : $core_config['main']['web_title'];
			$email_service = isset($site_config['email_service']) && $site_config['email_service'] ? $site_config['email_service'] : $core_config['main']['email_service'];
			$email_footer = isset($site_config['email_footer']) && $site_config['email_footer'] ? $site_config['email_footer'] : $core_config['main']['email_footer'];

			$sms_receiver = $sms_receiver ? $sms_receiver : '-';

			if ($user['fwd_to_email']) {
				if ($email = $user['email']) {
					$subject = _('Message from') . " " . $sender;
					$body = $web_title . "\n\n";
					$body .= _('Message received at') . " " . $sms_receiver . " " . _('on') . " " . core_display_datetime($sms_datetime) . "\n\n";
					$body .= _('From') . " " . $sender . "\n\n";
					$body .= $message . "\n\n--\n";
					$body .= $email_footer . "\n\n";
					$body = _display($body);
					_log("send email from:" . $email_service . " to:" . $email . " message:[" . $message . "]", 3, "recvsms_inbox_add");
					$data = [
						'mail_from_name' => $web_title,
						'mail_from' => $email_service,
						'mail_to' => $email,
						'mail_subject' => $subject,
						'mail_body' => $body
					];
					sendmail($data);
					_log("sent email from:" . $email_service . " to:" . $email, 3, "recvsms_inbox_add");
				}
			}

			// forward to mobile
			if ($user['fwd_to_mobile']) {
				if ($mobile = $user['mobile']) {

					// fixme anton
					$c_message = $message . ' ' . $sender;
					if ($sender_uid = user_mobile2uid($sms_sender)) {
						if ($sender_username = user_uid2username($sender_uid)) {
							$c_message = $message . ' ' . '@' . $sender_username;
						}
					}
					$message = $c_message;
					$unicode = core_detect_unicode($message);
					$nofooter = true;

					_log("send to mobile:" . $mobile . " from:" . $sms_sender . " user:" . $target_user . " message:" . $message, 3, "recvsms_inbox_add");
					list($ok, $to, $smslog_id, $queue) = sendsms($target_user, $mobile, $message, 'text', $unicode, '', $nofooter);
					if ($ok[0] == 1) {
						_log("sent to mobile:" . $mobile . " from:" . $sms_sender . " user:" . $target_user, 2, "recvsms_inbox_add");
					}
				}
			}
		}
	}

	return $ok;
}

function getsmsinbox()
{
	$gateways = [];

	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}

	if ($gateways) {
		$gateways = array_unique($gateways);
		foreach ( $gateways as $gateway ) {
			core_hook($gateway, 'getsmsinbox');
		}
	}
}
