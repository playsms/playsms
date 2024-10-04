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
 * Run mail fetch
 * This function hooks playsmsd()
 * 
 * @return void
 */
function mailsms_hook_playsmsd()
{
	global $core_config;

	// get global mailsms registry data
	$items_global = registry_search(0, 'features', 'mailsms');

	// fetch interval
	$c_fetch_interval = (int) $items_global['features']['mailsms']['fetch_interval'];
	$c_fetch_interval = $c_fetch_interval > 10 ? $c_fetch_interval : 60;
	if (!core_playsmsd_timer($c_fetch_interval)) {

		return;
	}

	// _log('fetch now:'.$now, 2, 'mailsms_hook_playsmsd');

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
	$is_fetching = playsmsd_pid_get($param) ? true : false;
	if (!$is_fetching) {
		$playsmsd_bin = trim($core_config['daemon']['PLAYSMS_BIN'] . "/playsmsd");
		$playsmsd_conf = $core_config['daemon']['PLAYSMSD_CONF'];
		if (is_file($playsmsd_conf) && is_file($playsmsd_bin) && is_executable($playsmsd_bin)) {
			$RUN_THIS = "nohup " . escapeshellcmd($playsmsd_bin) . " " . escapeshellarg($playsmsd_conf) . " schdqueue once " . $param . " >/dev/null 2>&1 &";

			// _log('execute:' . $RUN_THIS, 3, 'mailsms_hook_playsmsd');

			shell_exec($RUN_THIS);
		}
	}
}

/**
 * Run mail fetch once
 * This function hooks playsmsd_once()
 * 
 * @param string $param
 * @return void
 */
function mailsms_hook_playsmsd_once($param)
{
	$param = is_array($param) && isset($param[0]) ? $param[0] : '';
	$param = trim($param);

	if ($param != 'mailsms_fetch') {

		return;
	}

	$items_global = registry_search(0, 'features', 'mailsms');

	$enable_fetch = $items_global['features']['mailsms']['enable_fetch'];
	if (!$enable_fetch) {

		return;
	}

	$ssl = $items_global['features']['mailsms']['ssl'] == 1 ? "/ssl" : "";
	$novalidate_cert = $items_global['features']['mailsms']['novalidate_cert'] == 1 ? "/novalidate-cert" : "";
	$email_hostname = '{' . $items_global['features']['mailsms']['server'] . ':' . $items_global['features']['mailsms']['port'] . '/' . $items_global['features']['mailsms']['protocol'] . $ssl . $novalidate_cert . '}INBOX';
	$email_username = $items_global['features']['mailsms']['username'];
	$email_password = $items_global['features']['mailsms']['password'];

	// _log('fetch ' . $email_username . ' at ' . $email_hostname, 3, 'mailsms_hook_playsmsd_once');

	// open mailbox
	$inbox = imap_open($email_hostname, $email_username, $email_password);

	if (!$inbox) {
		$errors = imap_errors();
		foreach ( $errors as $error ) {

			// _log('error:' . $error, 3, 'mailsms_hook_playsmsd_once');
		}

		return;
	}

	$emails = imap_search($inbox, 'UNSEEN');
	$c_count = is_array($emails) ? count($emails) : 0;
	if ($c_count) {
		rsort($emails);
		foreach ( $emails as $email_number ) {
			$overview = imap_fetch_overview($inbox, $email_number, 0);
			$email_subject = isset($overview[0]->subject) ? trim($overview[0]->subject) : '';
			if (!$email_subject) {
				_log('email subject not found email_number:' . $email_number, 3, 'mailsms_hook_playsmsd_once');
				continue;
			}

			$email_sender = isset($overview[0]->from) ? trim($overview[0]->from) : '';
			if (!$email_sender) {
				_log('email sender not found email_number:' . $email_number, 3, 'mailsms_hook_playsmsd_once');
				continue;
			}

			$email_body = imap_fetchbody($inbox, $email_number, 1);
			if (!$email_body) {
				_log('fail to fetch email body email_number:' . $email_number, 3, 'mailsms_hook_playsmsd_once');
				continue;
			}

			$email_body = trim($email_body);

			_log('email from:[' . $email_sender . '] subject:[' . $email_subject . '] body:[' . $email_body . ']', 3, 'mailsms_hook_playsmsd');

			// fixme me - https://playsms.discourse.group/t/mail2sms-encoding-problem/836/6
			if (function_exists('iconv_mime_decode')) {
				$email_subject = iconv_mime_decode($email_subject);
				_log('decoded subject:[' . $email_subject . ']', 3, 'mailsms_hook_playsmsd');
			}

			$e = preg_replace('/\s+/', ' ', trim($email_subject));
			$f = preg_split('/ +/', $e);
			$sender_username = str_replace('@', '', isset($f[0]) ? $f[0] : ''); // in case user use @username
			$sender_pin = isset($f[1]) ? $f[1] : '';
			//$message = str_replace($sender_username . ' ' . $sender_pin . ' ', '', $email_subject);
			$c_message = preg_split("/[\s]+/", $email_subject, 3);
			$message = isset($c_message[2]) ? $c_message[2] : '';

			$sender = user_getdatabyusername($sender_username);

			if (isset($sender['uid']) && $sender['uid']) {
				$items = registry_search($sender['uid'], 'features', 'mailsms_user');
				$pin = $items['features']['mailsms_user']['pin'];
				if ($sender_pin && $pin && $sender_pin == $pin) {
					if ($items_global['features']['mailsms']['check_sender']) {
						preg_match('#\<(.*?)\>#', $email_sender, $match);
						$sender_email = isset($match[1]) ? $match[1] : '';
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
			$sms_to_temp = preg_replace('/--[\r\n]+.*/s', '', $email_body);
			$sms_to_temp = explode(',', $sms_to_temp);

			// check and send SMS only to valid SMS target
			$sms_to = [];
			if (is_array($sms_to_temp) && $sms_to_temp) {
				foreach ( $sms_to_temp as $to ) {
					if (!preg_match('/[^\p{L}\p{N}\+\@]+/u', $to)) {
						$sms_to[] = $to;
					}
				}
			}

			if (!count($sms_to)) {
				_log('SMS target not found', 3, 'mailsms_hook_playsmsd_once');
				continue;
			}

			// sendsms
			if ($sender_username && $message) {
				_log('mailsms uid:' . $sender['uid'] . ' from:[' . $sender_email . '] username:[' . $sender_username . ']', 3, 'mailsms_hook_playsmsd_once');
				$unicode = core_detect_unicode($message);
				sendsms_helper($sender_username, $sms_to, $message, '', $unicode);
			}
		}
	}

	// close mailbox
	imap_close($inbox);
}
