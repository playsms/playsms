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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

function user_getallwithstatus($status) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE status='$status'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function user_getdatabyuid($uid) {
	global $core_config;
	$ret = array();
	if ($uid) {
		$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row;
			$ret['opt']['sms_footer_length'] = ( strlen($ret['footer']) > 0 ? strlen($ret['footer']) + 1 : 0 );
			$ret['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $ret['opt']['sms_footer_length'];
		}
	}
	return $ret;
}

function user_getdatabyusername($username) {
	$uid = user_username2uid($username);
	return user_getdatabyuid($uid);
}

function user_getfieldbyuid($uid, $field) {
	$field = core_query_sanitize($field);
	if ($uid && $field) {
		$db_query = "SELECT $field FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row[$field];
		}
	}
	return $ret;
}

function user_getfieldbyusername($username, $field) {
	$uid = user_username2uid($username);
	return user_getfieldbyuid($uid, $field);
}

function user_uid2username($uid) {
	if ($uid) {
		$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$username = $db_row['username'];
	}
	return $username;
}

function user_username2uid($username) {
	if ($username) {
		$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE username='$username'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

function user_mobile2uid($mobile) {
	if ($mobile) {
		// remove +
		$mobile = str_replace('+','',$mobile);
		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) { $mobile = substr($mobile,3); }
		$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE mobile LIKE '%$mobile'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

/**
 * Get uid from email
 * @param  string $email Email
 * @return integer User ID
 */
function user_email2uid($email) {
	$list = dba_search(_DB_PREF_.'_tblUser', 'uid', array('email' => $email));
	return $list[0]['uid'];
}

/**
 * Get username from email
 * @param  string $email Email
 * @return string username
 */
function user_email2username($email) {
	$list = dba_search(_DB_PREF_.'_tblUser', 'username', array('email' => $email));
	return $list[0]['username'];
}

/**
 * Validate data for user registration
 * @param array $data User data
 * @return array $ret('error_string', 'status')
 */
function user_add_validate($data=array()) {
	$ret['status'] = true;
	if (is_array($data)) {
		foreach ($data as $key => $val) {
			$data[$key] = trim($val);
		}
		if ($data['password'] && (strlen($data['password']) < 4)) {
			$ret['error_string'] = _('Password should be at least 4 characters');
			$ret['status'] = false;
		}
		if ($ret['status'] && $data['username'] && (strlen($data['username']) < 3)) {
			$ret['error_string'] = _('Username should be at least 3 characters')." (".$data['username'].")";
			$ret['status'] = false;
		}
		if ($ret['status'] && $data['username'] && (! preg_match('/([A-Za-z0-9\.\-])/', $data['username']))) {
			$ret['error_string'] = _('Valid characters for username are alphabets, numbers, dot or dash')." (".$data['username'].")";
			$ret['status'] = false;
		}
		if ($ret['status'] && (! preg_match('/^(.+)@(.+)\.(.+)$/', $data['email']))) {
			if ($data['email']) {
				$ret['error_string'] = _('Your email format is invalid')." (".$data['email'].")";
			} else {
				$ret['error_string'] = _('Your email format is invalid');
			}
			$ret['status'] = false;
		}

		// current data user, to check an edit action
		$current = user_getdatabyusername($data['username']);

		// check if username is exists
		if ($data['username'] && ($data['username'] != $current['username'])) {
			if ($ret['status'] && dba_isexists(_DB_PREF_.'_tblUser', array('username' => $data['username']))) {
				$ret['error_string'] = _('User is already exists')." ("._('username').": ".$data['username'].")";
				$ret['status'] = false;
			}
		}

		// check if email is exists
		if ($data['email'] && ($data['email'] != $current['email'])) {
			if ($ret['status'] && dba_isexists(_DB_PREF_.'_tblUser', array('email' => $data['email']))) {
				$ret['error_string'] = _('User with this email is already exists')." ("._('email').": ".$data['email'].")";
				$ret['status'] = false;
			}
		}

		// check mobile, must check for duplication only when filled
		if ($data['mobile'] && ($data['mobile'] != $current['mobile'])) {
			if ($ret['status'] && (! preg_match('/([0-9\+\- ])/', $data['mobile']))) {
				$ret['error_string'] = _('Your mobile format is invalid')." (".$data['mobile'].")";
				$ret['status'] = false;
			}
			if ($ret['status'] && dba_isexists(_DB_PREF_.'_tblUser', array('mobile' => $data['mobile']))) {
				$ret['error_string'] = _('User with this mobile is already exists')." ("._('mobile').": ".$data['mobile'].")";
				$ret['status'] = false;
			}
		}
	}
	return $ret;
}

/**
 * Add new user
 * @param array $data User data
 * @return array $ret('error_string', 'status', 'uid')
 */
function user_add($data=array()) {
	global $core_config;
	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = FALSE;
	$ret['uid'] = 0;
	$data = ( trim($data['username']) ? $data : $_REQUEST );
	if (auth_isadmin() || $core_config['main']['enable_register']) {
		foreach ($data as $key => $val) {
			$data[$key] = trim($val);
		}
		$data['status'] = ( $data['status'] ? $data['status'] : 3 );
		$data['status'] = ( auth_isadmin() ? $data['status'] : 3 );
		$data['username'] = core_sanitize_username($data['username']);
		$data['password'] = ( $data['password'] ? $data['password'] : core_get_random_string(10) );
		$new_password = $data['password'];
		$data['password'] = md5($new_password);
		$data['token'] = md5(uniqid($data['username'].$data['password'], true));
		$data['credit'] = ( $data['credit'] ? $data['credit'] : $core_config['main']['default_credit'] );
		$data['sender'] = ( $data['sender'] ? core_sanitize_sender($data['sender']) : '' );
		$data['footer'] = '@'.$data['username'];
		$dt = core_get_datetime();
		$data['register_datetime'] = $dt;
		$data['lastupdate_datetime'] = $dt;
		$data['webservices_ip'] = ( trim($data['webservices_ip']) ? trim($data['webservices_ip']) : '127.0.0.1, 192.168.*.*' );
		$v = user_add_validate($data);
		if ($v['status']) {
			_log('attempt to register status:'.$data['status'].' u:'.$data['username'].' email:'.$data['email'], 3, 'user_add');
			if ($data['username'] && $data['email'] && $data['name']) {
				if ($new_uid = dba_add(_DB_PREF_.'_tblUser', $data)) {
					$ret['status'] = TRUE;
					$ret['uid'] = $new_uid;
				} else {
					$ret['error_string'] = _('Fail to register an account');
				}
				if ($ret['status']) {
					_log('registered status:'.$data['status'].' u:'.$data['username'].' uid:'.$ret['uid'].' email:'.$data['email'].' ip:'.$_SERVER['REMOTE_ADDR'].' mobile:'.$data['mobile'].' credit:'.$data['credit'], 2, 'user_add');
					$subject = _('New account registration');
					$body = $core_config['main']['web_title']."\n";
					$body .= $core_config['http_path']['base']."\n\n";
					$body .= _('Username').": ".$data['username']."\n";
					$body .= _('Password').": ".$new_password."\n";
					$body .= _('Mobile').": ".$data['mobile']."\n";
					$body .= _('Credit').": ".$data['credit']."\n\n";
					$body .= $core_config['main']['email_footer']."\n\n";
					$ret['error_string'] = _('User has been added and password has been emailed')." ("._('username').": ".$data['username'].")";
					$mail_data = array(
						'mail_from_name' => $core_config['main']['web_title'],
						'mail_from' => $core_config['main']['email_service'],
						'mail_to' => $data['email'],
						'mail_subject' => $subject,
						'mail_body' => $body);
					if (! sendmail($mail_data)) {
						$ret['error_string'] = _('User has been added but failed to send email')." ("._('username').": ".$data['username'].")";
					}
				}
			} else {
				$ret['error_string'] = _('You must fill all required fields');
			}
		} else {
			$ret['error_string'] = $v['error_string'];
		}
	} else {
		$ret['error_string'] = _('Public registration is disabled');
	}
	return $ret;
}

/**
 * Delete existing user
 * @param integer $uid User ID
 * @return array $ret('error_string', 'status')
 */
function user_remove($uid) {
	global $user_config;
	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = FALSE;
	if ($username = user_uid2username($uid)) {
		if (! ($uid == 1)) {
			if ($uid == $user_config['uid']) {
				$ret['error_string'] = _('Currently logged in user is immune to deletion');
			} else {
				if (dba_remove(_DB_PREF_.'_tblUser', array('uid' => $uid))) {
					user_banned_remove($uid);
					_log('user removed u:'.$username.' uid:'.$uid, 2, 'user_remove');
					$ret['error_string'] = _('User has been removed') . " (" . _('username') . ": ".$username.")";
					$ret['status'] = TRUE;
				}
			}
		} else {
			$ret['error_string'] = _('User is immune to deletion') . " (" . _('username') . ": ".$username.")";
		}
	} else {
		$ret['error_string'] = _('User does not exists');
	}
	return $ret;
}

/**
 * Save user's login session information
 * @param integer $uid User ID
 */
function user_session_set($uid='') {
	global $core_config, $user_config;
	if (! $core_config['daemon_process']) {
		$uid = ( $uid ? $uid : $user_config['uid'] );
		$hash = md5($uid.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		$json = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'last_update' => core_get_datetime(),
			'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'sid' => $_SESSION['sid'],
			'uid' => $uid,
		);
		$item[$hash] = json_encode($json);
		registry_update(1, 'auth', 'login_session', $item);
	}
}

/**
 * Get user's login session information
 * @param integer $uid User ID
 * @param string $sid Session ID
 * @return array login sessions
 */
function user_session_get($uid='', $sid='') {
	global $user_config;
	$ret = array();
	$h = registry_search(1, 'auth', 'login_session');
	$hashes = $h['auth']['login_session'];
	foreach ($hashes as $key => $val) {
		$d = core_object_to_array(json_decode($val));
		if ($d['ip'] && $d['last_update'] && $d['http_user_agent'] && $d['sid'] && $d['uid']) {
			if ($uid && $uid == $d['uid']) {
				$ret[$key] = $d;
				return $ret;
			}
			if ($sid && $sid == $d['sid']) {
				$ret[$key] = $d;
				return $ret;
			}
			$c_ret[$key] = $d;
		}
	}
	$ret = $c_ret;
	return $ret;
}

/**
 * Remove user's login session information
 * @param integer $uid User ID
 * @param string $sid Session ID
 * @return boolean
 */
function user_session_remove($uid='', $sid='', $hash='') {
	$ret = FALSE;
	if ($hash) {
		if (registry_remove(1, 'auth', 'login_session', $hash)) {
			return TRUE;
		}
	} else if ($sid) {
		$hash = user_session_get('', $sid);
		if (registry_remove(1, 'auth', 'login_session', key($hash))) {
			return TRUE;
		}
	} else if ($uid) {
		$hashes = user_session_get($uid);
		foreach ($hashes as $hash) {
			if (registry_remove(1, 'auth', 'login_session', key($hash))) {
				$ret = TRUE;
			}
		}
	}
	return $ret;
}

/**
 * Add user to banned user list
 * @param  integer $uid User ID
 * @return boolean TRUE if user successfully added to banned user list
 */
function user_banned_add($uid) {
	$bantime = core_get_datetime();
	if (! user_session_remove($uid)) {
		return FALSE;
	}
	$item = array($uid => $bantime);
	if (registry_update(1, 'auth', 'banned_users', $item)) {
		_log('banned uid:'.$uid.' bantime:'.$bantime, 2, 'user_banned_add');
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Remove user from banned user list
 * @param  integer $uid User ID
 * @return boolean TRUE if user successfully removed from banned user list
 */
function user_banned_remove($uid) {
	if (registry_remove(1, 'auth', 'banned_users', $uid)) {
		_log('unbanned uid:'.$uid, 2, 'user_banned_remove');
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Get user ban status
 * @param  integer $uid User ID
 * @return string Ban date/time
 */
function user_banned_get($uid) {
	$list = registry_search(1, 'auth', 'banned_users', $uid);
	if ($list['auth']['banned_users'][$uid]) {
		return $list['auth']['banned_users'][$uid];
	} else {
		return FALSE;
	}
}

/**
 * List all banned users
 * @return array banned users
 */
function user_banned_list() {
	$ret = array();
	$list = registry_search(1, 'auth', 'banned_users');
	foreach ($list['auth']['banned_users'] as $key => $val) {
		$uid = (int) $key;
		$username = user_uid2username($uid);
		$bantime = $val;
		if ($uid && $username && $bantime) {
			$ret[] = array(
				'uid' => $uid,
				'username' => $username,
				'bantime' => $bantime,
			);
		}
	}
	return $ret;
}
