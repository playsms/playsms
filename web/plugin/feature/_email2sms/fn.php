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

function email2sms_hook_playsmsd() {
	global $core_config;
	
	// fetch every 60 seconds
	if (!core_playsmsd_timer(60)) {
		return;
	}
	
	// _log('fetch now:'.$now, 2, 'email2sms_hook_playsmsd');
	

	// get all users
	$users = dba_search(_DB_PREF_ . '_tblUser', 'uid', array(
		'flag_deleted' => 0 
	));
	
	foreach ($users as $user) {
		$uid = $user['uid'];
		
		// get email2sms registry data for $uid
		$items = registry_search($uid, 'features', 'email2sms');
		
		$enable = $items['features']['email2sms']['enable'];
		$protocol = $items['features']['email2sms']['protocol'];
		$port = $items['features']['email2sms']['port'];
		$server = $items['features']['email2sms']['server'];
		$username = $items['features']['email2sms']['username'];
		$password = $items['features']['email2sms']['password'];
		
		if (!($enable && $protocol && $port && $server && $username && $password)) {
			continue;
		}
		
		// _log('fetch uid:' . $uid, 3, 'email2sms_hook_playsmsd');
		

		$param = 'email2sms_uid_' . $uid;
		$is_fetching = (playsmsd_pid_get($param) ? TRUE : FALSE);
		if (!$is_fetching) {
			$RUN_THIS = "nohup " . $core_config['daemon']['PLAYSMS_BIN'] . "/playsmsd playsmsd once " . $param . " >/dev/null 2>&1 &";
			
			// _log('execute:' . $RUN_THIS, 3, 'email2sms_hook_playsmsd');
			shell_exec($RUN_THIS);
		}
	}
}

function email2sms_hook_playsmsd_once($param) {
	$c_param = explode('_', $param);
	if ($c_param[0] == 'email2sms') {
		if ($c_param[1] == 'uid') {
			$uid = (int) $c_param[2];
		}
	}
	
	// get username
	$username = user_uid2username($uid);
	
	// _log('fetch uid:' . $uid . ' username:' . $username, 3, 'email2sms_hook_playsmsd_once');
	

	if ($uid && $username) {
		
		$items = registry_search($uid, 'features', 'email2sms');
		
		$enable = $items['features']['email2sms']['enable'];
		if (!$enable) {
			return;
		}
		
		$ssl = ($items['features']['email2sms']['ssl'] == 1) ? "/ssl" : "";
		$novalidate_cert = ($items['features']['email2sms']['novalidate_cert'] == 1) ? "/novalidate-cert" : "";
		$email_hostname = '{' . $items['features']['email2sms']['server'] . ':' . $items['features']['email2sms']['port'] . '/' . $items['features']['email2sms']['protocol'] . $ssl . $novalidate_cert . '}INBOX';
		$email_username = $items['features']['email2sms']['username'];
		$email_password = $items['features']['email2sms']['password'];
		
		// _log('fetch ' . $email_username . ' at ' . $email_hostname, 3, 'email2sms_hook_playsmsd_once');
		

		// open mailbox
		$inbox = imap_open($email_hostname, $email_username, $email_password);
		
		if (!$inbox) {
			$errors = imap_errors();
			foreach ($errors as $error) {
				
				// _log('error:' . $error, 3, 'email2sms_hook_playsmsd_once');
			}
			return;
		}
		
		$emails = imap_search($inbox, 'UNSEEN');
		if (count($emails)) {
			rsort($emails);
			foreach ($emails as $email_number) {
				$overview = imap_fetch_overview($inbox, $email_number, 0);
				$email_subject = trim($overview[0]->subject);
				$email_sender = trim($overview[0]->from);
				$email_body = trim(imap_fetchbody($inbox, $email_number, 1));
				
				_log('email from:[' . $email_sender . '] subject:[' . $email_subject . '] body:[' . $email_body . ']', 3, 'email2sms_hook_playsmsd');
				
				// destination numbers is in array and retrieved from email body
				// remove email footer/signiture
				$sms_to = preg_replace('/--[\r\n]+.*/s', '', $email_body);
				$sms_to = explode(',', $sms_to);
				
				// Check "from" email before checking PIN if option "Check email sender" is TRUE
				if ($items['features']['email2sms']['check_sender']) {
					preg_match('#\<(.*?)\>#', $email_sender, $match);
					if (user_email2uid($match[1]) == "") {
						continue;
					}
				}
				
				// message is from email subject
				// $message = trim($email_subject);
				$message = trim(preg_replace('/' . $items['features']['email2sms']['pin'] . '/', '', $email_subject, -1, $count));
				if ($count <= 0) {
					_log('PIN does not match. Subject: ' . $email_subject, 2, 'email2sms_hook_playsmsd');
				}
				
				// sendsms
				if ($username && count($sms_to) && $message && $count > 0) {
					_log('email2sms username:' . $username, 3, 'email2sms_hook_playsmsd_once');
					list($ok, $to, $smslog_id, $queue, $counts, $sms_count, $sms_failed) = sendsms_helper($username, $sms_to, $message, '', '', '', '', '', '', $reference_id);
				}
			}
		}
		
		// close mailbox
		imap_close($inbox);
	}
}
