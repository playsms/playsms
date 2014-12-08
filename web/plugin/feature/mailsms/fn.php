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

function mailsms_hook_playsmsd() {
	global $core_config;
	
	// fetch every 60 seconds
	if (!core_playsmsd_timer(60)) {
		return;
	}
	
	// _log('fetch now:'.$now, 2, 'mailsms_hook_playsmsd');
	
	// get global mailsms registry data
	$items_global = registry_search(0, 'features', 'mailsms');
	
	$enable_fetch = $items_global['features']['mailsms']['enable_fetch'];
	$protocol = $items_global['features']['mailsms']['protocol'];
	$port = $items_global['features']['mailsms']['port'];
	$server = $items_global['features']['mailsms']['server'];
	$username = $items_global['features']['mailsms']['username'];
	$password = $items_global['features']['mailsms']['password'];
	
	if (!($enable_fetch && $protocol && $port && $server && $username && $password)) {
		return;
	}
	
	// _log('fetch uid:' . $uid, 3, 'mailsms_hook_playsmsd');
	
	$param = 'mailsms_fetch';
	$is_fetching = (playsmsd_pid_get($param) ? TRUE : FALSE);
	if (!$is_fetching) {
		$RUN_THIS = "nohup " . $core_config['daemon']['PLAYSMS_BIN'] . "/playsmsd playsmsd once " . $param . " >/dev/null 2>&1 &";
		
		// _log('execute:' . $RUN_THIS, 3, 'mailsms_hook_playsmsd');
		shell_exec($RUN_THIS);
	}
}

function mailsms_hook_playsmsd_once($param) {
	if ($param != 'mailsms_fetch') {
		return;
	}
	
	// get username
	$username = user_uid2username($uid);
	
	// _log('fetch uid:' . $uid . ' username:' . $username, 3, 'mailsms_hook_playsmsd_once');
	
	$items_global = registry_search(0, 'features', 'mailsms');
	
	$enable_fetch = $items_global['features']['mailsms']['enable_fetch'];
	if (!$enable_fetch) {
		return;
	}
	
	$ssl = ($items_global['features']['mailsms']['ssl'] == 1) ? "/ssl" : "";
	$novalidate_cert = ($items_global['features']['mailsms']['novalidate_cert'] == 1) ? "/novalidate-cert" : "";
	$email_hostname = '{' . $items_global['features']['mailsms']['server'] . ':' . $items_global['features']['mailsms']['port'] . '/' . $items_global['features']['mailsms']['protocol'] . $ssl . $novalidate_cert . '}INBOX';
	$email_username = $items_global['features']['mailsms']['username'];
	$email_password = $items_global['features']['mailsms']['password'];
	
	// _log('fetch ' . $email_username . ' at ' . $email_hostname, 3, 'mailsms_hook_playsmsd_once');
	
	// open mailbox
	$inbox = imap_open($email_hostname, $email_username, $email_password);
	
	if (!$inbox) {
		$errors = imap_errors();
		foreach ($errors as $error ) {
			
			// _log('error:' . $error, 3, 'mailsms_hook_playsmsd_once');
		}
		return;
	}
	
	$emails = imap_search($inbox, 'UNSEEN');
	if (count($emails)) {
		rsort($emails);
		foreach ($emails as $email_number ) {
			$overview = imap_fetch_overview($inbox, $email_number, 0);
			$email_subject = trim($overview[0]->subject);
			$email_sender = trim($overview[0]->from);
			$email_body = trim(imap_fetchbody($inbox, $email_number, 1));
			
			_log('email from:[' . $email_sender . '] subject:[' . $email_subject . '] body:[' . $email_body . ']', 3, 'mailsms_hook_playsmsd');
			
			$e = preg_replace('/\s+/', ' ', trim($email_subject));
			$f = preg_split('/ +/', $e);
			$sender_username = str_replace('@', '', $f[0]); // in case user use @username
			$sender_pin = $f[1];
			$message = str_replace($sender_username . ' ' . $sender_pin . ' ', '', $email_subject);
			
			$sender = user_getdatabyusername($sender_username);
			
			if ($sender['uid']) {
				$items = registry_search($sender['uid'], 'features', 'mailsms_user');
				$pin = $items['features']['mailsms_user']['pin'];
				if ($sender_pin && $pin && ($sender_pin == $pin)) {
					if ($items_global['features']['mailsms']['check_sender']) {
						preg_match('#\<(.*?)\>#', $email_sender, $match);
						$sender_email = $match[1];
						if ($sender['email'] != $sender_email) {
							_log('check_sender:1 unknown sender from:' . $sender_email . ' uid:' . $sender['uid'] . ' e:' . $sender['email'], 3, 'mailsms_hook_playsmsd_once');
							continue;
						}
					}
				} else {
					_log('invalid pin uid:' . $sender['uid'] . ' sender_pin:[' . $sender_pin . ']', 3, 'mailsms_hook_playsmsd_once');
					continue;
				}
			} else {
				_log('invalid username sender_username:[' . $sender_username . ']', 3, 'mailsms_hook_playsmsd_once');
				continue;
			}
			
			// destination numbers is in array and retrieved from email body
			// remove email footer/signiture
			$sms_to = preg_replace('/--[\r\n]+.*/s', '', $email_body);
			$sms_to = explode(',', $sms_to);
			
			// sendsms
			if ($sender_username && count($sms_to) && $message) {
				_log('mailsms uid:' . $sender['uid'] . ' from:[' . $sender_email . '] username:[' . $sender_username . ']', 3, 'mailsms_hook_playsmsd_once');
				list($ok, $to, $smslog_id, $queue, $counts, $sms_count, $sms_failed) = sendsms_helper($sender_username, $sms_to, $message, '', '', '', '', '', '', $reference_id);
			}
		}
	}
	
	// close mailbox
	imap_close($inbox);
}
