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

/**
 * Get settings
 *
 * @return array Settings
 *         Available setting keys:
 *         - leave_copy_sandbox
 *         - match_all_sender_id
 */
function incoming_settings_get() {
	
	// settings to leave copy on sandbox
	$data = registry_search(1, 'feature', 'incoming', 'settings_leave_copy_sandbox');
	$settings['leave_copy_sandbox'] = (int) $data['feature']['incoming']['settings_leave_copy_sandbox'];
	
	// settings to match with all approved sender ID
	$data = registry_search(1, 'feature', 'incoming', 'settings_match_all_sender_id');
	$settings['match_all_sender_id'] = (int) $data['feature']['incoming']['settings_match_all_sender_id'];
	
	return $settings;
}

/**
 * Get post rules
 *
 * @return array Post rules
 *         Available post rules keys:
 *         - match_sender_id
 *         - insert_prefix
 *         - forward_to
 */
function incoming_post_rules_get() {
	
	// sandbox match receiver number and sender ID
	$data = registry_search(1, 'feature', 'incoming', 'sandbox_match_sender_id');
	$post_rules['match_sender_id'] = (int) $data['feature']['incoming']['sandbox_match_sender_id'];
	
	// sandbox prefix
	// $data = registry_search(1, 'feature', 'incoming', 'sandbox_prefix');
	// $post_rules['insert_prefix'] = trim(strtoupper(core_sanitize_alphanumeric($data['feature']['incoming']['sandbox_prefix'])));
	

	// sandbox forward to users
	$data = registry_search(1, 'feature', 'incoming', 'sandbox_forward_to');
	$post_rules['forward_to'] = array_unique(unserialize($data['feature']['incoming']['sandbox_forward_to']));
	
	return $post_rules;
}

/**
 * Get pre rules
 *
 * @return array Pre rules
 *         Available pre rules keys:
 *         - match_username
 *         - match_groupcode
 */
function incoming_pre_rules_get() {
	
	// scan message for @username
	$data = registry_search(1, 'feature', 'incoming', 'incoming_match_username');
	$pre_rules['match_username'] = (int) $data['feature']['incoming']['incoming_match_username'];
	
	// scan message for #groupcode
	$data = registry_search(1, 'feature', 'incoming', 'incoming_match_groupcode');
	$pre_rules['match_groupcode'] = (int) $data['feature']['incoming']['incoming_match_groupcode'];
	
	return $pre_rules;
}

/**
 * Intercept on after-process stage for incoming SMS and forward it to selected user's inbox
 *
 * @param $sms_datetime incoming
 *        SMS date/time
 * @param $sms_sender incoming
 *        SMS sender
 * @param $message incoming
 *        SMS message before interepted
 * @param $sms_receiver receiver
 *        number that is receiving incoming SMS
 * @param $feature feature
 *        managed to hook current incoming SMS
 * @param $status recvsms()
 *        status, 0 or FALSE for unhandled
 * @param $uid keyword
 *        owner
 * @return array $ret
 */
function incoming_hook_recvsms_intercept_after($sms_datetime, $sms_sender, $message, $sms_receiver, $feature, $status, $uid, $smsc) {
	global $core_config;
	
	$ret = array();
	$users = array();
	$is_routed = FALSE;
	
	if (!$status) {
		
		// get settings
		$settings = incoming_settings_get();
		
		// get post rules
		$post_rules = incoming_post_rules_get();
		
		// sandbox match receiver number and sender ID
		if (!$is_routed) {
			
			// route sandbox if receiver number matched with default sender ID of users
			if ($post_rules['match_sender_id']) {
				$s = array();
				
				if ($settings['match_all_sender_id']) {
					
					// get all approved sender ID
					$s = sender_id_getall();
				} else {
					$data = user_search($sms_receiver, 'sender');
					foreach ($data as $user) {
						
						// get default sender ID
						if ($user['sender']) {
							$s[] = $user['sender'];
							
							// in case an error occured where multiple users own the same sender ID
							break;
						}
					}
				}
				
				// start matching
				foreach ($s as $sender_id) {
					if ($sender_id && $sms_receiver && ($sender_id == $sms_receiver)) {
						
						unset($usernames);
						unset($username);
						
						if ($settings['match_all_sender_id']) {
							
							// get $username who owns $sender_id
							$uids = sender_id_owner($sender_id);
							foreach ($uids as $uid) {
								$usernames[] = user_uid2username($uid);
							}
						} else {
							
							$usernames[] = $user['username'];
						}
						
						array_unique($usernames);
						
						foreach ($usernames as $username) {
							if ($username) {
								_log("sandbox match sender start u:" . $username . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:[" . $message . "]", 3, 'incoming recvsms_intercept_after');
								recvsms_inbox_add($sms_datetime, $sms_sender, $username, $message, $sms_receiver);
								_log("sandbox match sender end u:" . $username, 3, 'incoming recvsms_intercept_after');
								$is_routed = TRUE;
								
								// single match only
								// break;
							}
						}
					}
				}
			}
		}
		
		// sandbox prefix
		if (!$is_routed) {
			
			// route sandbox by adding a prefix to message and re-enter it to recvsms()
		/**
		 * if ($post_rules['insert_prefix'] && trim($message)) {
		 * $message = $post_rules['insert_prefix'] .
		 *
		 *
		 * ' ' . trim($message);
		 * _log("sandbox add prefix start keyword:" . $post_rules['insert_prefix'] . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:" . $message, 3, 'incoming recvsms_intercept_after');
		 * recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
		 * _log("sandbox add prefix end keyword:" . $post_rules['insert_prefix'], 3, 'incoming recvsms_intercept_after');
		 * $is_routed = TRUE;
		 * }
		 */
		}
		
		// sandbox forward to users
		if (!$is_routed) {
			
			foreach ($post_rules['forward_to'] as $uid) {
				$c_username = user_uid2username($uid);
				if ($c_username) {
					$users[] = $c_username;
				}
			}
			
			// route sandbox to users inbox
			foreach ($users as $username) {
				_log("sandbox to user start u:" . $username . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:[" . $message . "]", 3, 'incoming recvsms_intercept_after');
				recvsms_inbox_add($sms_datetime, $sms_sender, $username, $message, $sms_receiver);
				_log("sandbox to user end u:" . $username, 3, 'incoming recvsms_intercept_after');
				$is_routed = TRUE;
			}
		}
		
		// flag the hook if is_routed
		if ($is_routed) {
			$ret['param']['feature'] = 'incoming';
			
			if ($settings['leave_copy_sandbox']) {
				$ret['param']['status'] = 0;
			} else {
				$ret['param']['status'] = 1;
			}
			
			$ret['param']['uid'] = 1;
			$ret['modified'] = TRUE;
		}
	}
	
	return $ret;
}

/**
 * Intercept on before-process stage for incoming SMS
 *
 * @param $sms_datetime incoming
 *        SMS date/time
 * @param $sms_sender incoming
 *        SMS sender
 * @param $message incoming
 *        SMS message before interepted
 * @param $sms_receiver receiver
 *        number that is receiving incoming SMS
 * @param $reference_id reference_id
 *        data
 * @return array $ret
 */
function incoming_hook_recvsms_intercept($sms_datetime, $sms_sender, $message, $sms_receiver, $reference_id) {
	$ret = array();
	$found_bc = FALSE;
	$found_pv = FALSE;
	
	// continue only when keyword does not exists
	$m = explode(' ', $message);
	if (!checkavailablekeyword($m[0])) {
		return $ret;
	}
	
	// get settings
	$settings = incoming_settings_get();
	
	// get post rules
	$pre_rules = incoming_pre_rules_get();
	
	// scan for #<sender's phonebook group code> and @<username> according to pre rules
	$msg = explode(' ', $message);
	if (count($msg) > 0) {
		$bc = array();
		$pv = array();
		for ($i = 0; $i < count($msg); $i++) {
			$c_text = trim($msg[$i]);
			
			// scan message for @username
			if ($pre_rules['match_username']) {
				if (substr($c_text, 0, 1) === '@') {
					$pv[] = strtolower(substr($c_text, 1));
					$found_pv = TRUE;
				}
			}
			
			// scan message for #groupcode
			if ($pre_rules['match_groupcode']) {
				if (substr($c_text, 0, 1) === '#') {
					$bc[] = strtoupper(substr($c_text, 1));
					$found_bc = TRUE;
				}
			}
		}
	}
	
	if ($found_bc || $found_pv) {
		_log("recvsms_intercept dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:" . $message, 3, 'incoming recvsms_intercept');
	}
	
	if ($found_bc) {
		$groups = array_unique($bc);
		foreach ($groups as $key => $c_group_code) {
			$c_group_code = strtoupper($c_group_code);
			$c_group_code = core_sanitize_alphanumeric($c_group_code);
			$c_uid = user_mobile2uid($sms_sender);
			$list = phonebook_search_group($c_uid, $c_group_code, '', TRUE);
			$c_gpid = $list[0]['gpid'];
			if ($c_uid && $c_gpid) {
				$c_username = user_uid2username($c_uid);
				_log("bc g:" . $c_group_code . " gpid:" . $c_gpid . " uid:" . $c_uid . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:" . $message, 3, 'incoming recvsms_intercept');
				sendsms_bc($c_username, $c_gpid, $message);
				_log("bc end", 3, 'incoming recvsms_intercept');
				$ret['uid'] = $c_uid;
				$ret['hooked'] = true;
			}
		}
	}
	
	if ($found_pv) {
		$users = array_unique($pv);
		foreach ($users as $key => $c_username) {
			$c_username = core_sanitize_username($c_username);
			if ($c_uid = user_username2uid($c_username)) {
				_log("pv u:" . $c_username . " uid:" . $c_uid . " dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " m:[" . $message . "] reference_id:" . $reference_id, 3, 'incoming recvsms_intercept');
				recvsms_inbox_add($sms_datetime, $sms_sender, $c_username, $message, $sms_receiver, $reference_id);
				_log("pv end", 3, 'incoming recvsms_intercept');
				$ret['uid'] = $c_uid;
				$ret['hooked'] = true;
			}
		}
	}
	
	return $ret;
}
