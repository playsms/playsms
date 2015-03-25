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

function user_getallwithstatus($status) {
	$ret = array();
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND status='$status'";
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
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND uid='$uid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row;
			$ret['opt']['sms_footer_length'] = (strlen($ret['footer']) > 0 ? strlen($ret['footer']) + 1 : 0);
			$ret['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $ret['opt']['sms_footer_length'];

			// special setting to credit unicode SMS the same as normal SMS length
			$result = registry_search($uid, 'core', 'user_config', 'enable_credit_unicode');
			$ret['opt']['enable_credit_unicode'] = (int) $result['core']['user_config']['enable_credit_unicode'];
			if (!$ret['opt']['enable_credit_unicode']) {
				// global config overriden by user config
				$ret['opt']['enable_credit_unicode'] = (int) $core_config['main']['enable_credit_unicode'];
			}
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
		$db_query = "SELECT $field FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND uid='$uid'";
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
		$db_query = "SELECT username FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND uid='$uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$username = $db_row['username'];
	}
	return $username;
}

function user_username2uid($username) {
	if ($username) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username='$username'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

function user_mobile2uid($mobile) {
	if ($mobile) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND mobile LIKE '%" . core_mobile_matcher_format($mobile) . "'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

function user_mobile2username($mobile) {
	if ($uid = user_mobile2uid($mobile)) {
		$username = user_uid2username($uid);
	}

	return $username;
}

/**
 * Get uid from email
 *
 * @param string $email
 *        Email
 * @return integer User ID
 */
function user_email2uid($email) {
	$list = dba_search(_DB_PREF_ . '_tblUser', 'uid', array(
		'flag_deleted' => 0,
		'email' => $email
	));
	return $list[0]['uid'];
}

/**
 * Get username from email
 *
 * @param string $email
 *        Email
 * @return string username
 */
function user_email2username($email) {
	$list = dba_search(_DB_PREF_ . '_tblUser', 'username', array(
		'flag_deleted' => 0,
		'email' => $email
	));
	return $list[0]['username'];
}

/**
 * Validate data for user registration
 *
 * @param array $data
 *        User data
 * @param boolean $flag_edit
 *        TRUE when edit action (currently not inuse)
 * @return array $ret('error_string', 'status')
 */
function user_add_validate($data = array(), $flag_edit = FALSE) {
	global $core_config;
	$ret['status'] = true;

	if (is_array($data)) {
		foreach ($data as $key => $val) {
			$data[$key] = trim($val);
		}

		// password should be at least 4 characters
		if ($data['password'] && (strlen($data['password']) < 4)) {
			$ret['error_string'] = _('Password should be at least 4 characters');
			$ret['status'] = false;
		}

		// username should be at least 3 characters and maximum $username_length
		$username_length = ($core_config['main']['username_length'] ? $core_config['main']['username_length'] : 30);
		if ($ret['status'] && $data['username'] && ((strlen($data['username']) < 3) || (strlen($data['username']) > $username_length))) {
			$ret['error_string'] = sprintf(_('Username must be at least 3 characters and maximum %d characters'), $username_length) . " (" . $data['username'] . ")";
			$ret['status'] = false;
		}

		// username only can contain alphanumeric, dot and dash
		if ($ret['status'] && $data['username'] && (!preg_match('/([A-Za-z0-9\.\-])/', $data['username']))) {
			$ret['error_string'] = _('Valid characters for username are alphabets, numbers, dot or dash') . " (" . $data['username'] . ")";
			$ret['status'] = false;
		}

		// name must be exists
		if ($ret['status'] && !$data['name']) {
			$ret['error_string'] = _('Account name is mandatory');
			$ret['status'] = false;
		}

		// email must be in valid format
		if ($ret['status'] && (!preg_match('/^(.+)@(.+)\.(.+)$/', $data['email'])) && !$core_config['main']['enhance_privacy_subuser']) {
			if ($data['email']) {
				$ret['error_string'] = _('Your email format is invalid') . " (" . $data['email'] . ")";
			} else {
				$ret['error_string'] = _('Email address is mandatory');
			}
			$ret['status'] = false;
		}

		// mobile must be in valid format, but check this only when filled
		if ($ret['status'] && $data['mobile'] && (!preg_match('/([0-9\+\- ])/', $data['mobile']))) {
			$ret['error_string'] = _('Your mobile format is invalid') . " (" . $data['mobile'] . ")";
			$ret['status'] = false;
		}

		// check if username is exists
		if ($ret['status'] && $data['username'] && dba_isexists(_DB_PREF_ . '_tblUser', array(
			'flag_deleted' => 0,
			'username' => $data['username']
		), 'AND')) {
			if (!$flag_edit) {
				$ret['error_string'] = _('Account already exists') . " (" . _('username') . ": " . $data['username'] . ")";
				$ret['status'] = false;
			}
		}

		$existing = user_getdatabyusername($data['username']);

		// check if email is exists
		if ($ret['status'] && $data['email'] && dba_isexists(_DB_PREF_ . '_tblUser', array(
			'flag_deleted' => 0,
			'email' => $data['email']
		), 'AND')) {
			if ($data['email'] != $existing['email']) {
				$ret['error_string'] = _('Account with this email already exists') . " (" . _('email') . ": " . $data['email'] . ")";
				$ret['status'] = false;
			}
		}

		// check mobile, must check for duplication only when filled
		if ($ret['status'] && $data['mobile']) {
			if (dba_isexists(_DB_PREF_ . '_tblUser', array(
				'flag_deleted' => 0,
				'mobile' => $data['mobile']
			), 'AND')) {
				if ($data['mobile'] != $existing['mobile']) {
					$ret['error_string'] = _('Account with this mobile already exists') . " (" . _('mobile') . ": " . $data['mobile'] . ")";
					$ret['status'] = false;
				}
			}
		}
	}

	return $ret;
}

/**
 * Validate data for user preferences or configuration edit
 *
 * @param array $data
 *        User data
 * @return array $ret('error_string', 'status')
 */
function user_edit_validate($data = array()) {
	return user_add_validate($data, TRUE);
}

/**
 * Add new user
 *
 * @param array $data
 *        User data
 * @param boolean $forced
 *        Forced addition
 * @return array $ret('error_string', 'status', 'uid')
 */
function user_add($data = array(), $forced = FALSE) {
	global $core_config, $user_config;
	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = FALSE;
	$ret['uid'] = 0;
	$data = (trim($data['username']) ? $data : $_REQUEST);
	if ($forced || auth_isadmin() || ($user_config['status'] == 3) || (!auth_isvalid() && $core_config['main']['enable_register'])) {
		foreach ($data as $key => $val) {
			$data[$key] = trim($val);
		}

		// set valid status
		$data['status'] = (int) $data['status'];
		if (!(($data['status'] == 2) || ($data['status'] == 3))) {
			$data['status'] = 4;
		}

		// ACL exception for admins
		$data['acl_id'] = ((int) $data['acl_id'] ? (int) $data['acl_id'] : $core_config['main']['default_acl']);
		if ($data['status'] == 2) {
			$data['acl_id'] = 0;
		}

		// default parent_id
		$data['parent_uid'] = ((int) $data['parent_uid'] ? (int) $data['parent_uid'] : $core_config['main']['default_parent']);
		if ($parent_status = user_getfieldbyuid($data['parent_uid'], 'status')) {
			// logic for parent_uid, parent uid by default is 0
			if ($data['status'] == 4) {
				if (!(($parent_status == 2) || ($parent_status == 3))) {
					$data['parent_uid'] = $core_config['main']['default_parent'];
				}
			} else {
				$data['parent_uid'] = $core_config['main']['default_parent'];
			}
		} else {
			$data['parent_uid'] = $core_config['main']['default_parent'];
		}

		$data['username'] = core_sanitize_username($data['username']);
		$data['password'] = (trim($data['password']) ? trim($data['password']) : core_get_random_string(10));
		$new_password = $data['password'];
		$data['password'] = md5($new_password);
		$data['token'] = md5(uniqid($data['username'] . $data['password'], true));

		// default credit
		$supplied_credit = (float) $data['credit'];
		$data['credit'] = 0;

		// sender set to empty by default
		// $data['sender'] = ($data['sender'] ? core_sanitize_sender($data['sender']) : '');
		$data['sender'] = '';

		$dt = core_get_datetime();
		$data['register_datetime'] = $dt;
		$data['lastupdate_datetime'] = $dt;

		// fixme anton - these should be configurable on main config
		$data['footer'] = '@' . $data['username'];
		$data['enable_webservices'] = 1;
		// $data['webservices_ip'] = (trim($data['webservices_ip']) ? trim($data['webservices_ip']) : '127.0.0.1, 192.168.*.*');
		$data['webservices_ip'] = '*.*.*.*';

		$v = user_add_validate($data);
		if ($v['status']) {
			_log('attempt to register status:' . $data['status'] . ' u:' . $data['username'] . ' email:' . $data['email'], 3, 'user_add');
			if ($data['username'] && $data['email'] && $data['name']) {
				if ($new_uid = dba_add(_DB_PREF_ . '_tblUser', $data)) {
					$ret['status'] = TRUE;
					$ret['uid'] = $new_uid;

					// set credit upon registration
					$default_credit = ($supplied_credit ? $supplied_credit : (float) $core_config['main']['default_credit']);
					rate_addusercredit($ret['uid'], $default_credit);
				} else {
					$ret['error_string'] = _('Fail to register an account');
				}
				if ($ret['status']) {
					_log('registered status:' . $data['status'] . ' u:' . $data['username'] . ' uid:' . $ret['uid'] . ' email:' . $data['email'] . ' ip:' . $_SERVER['REMOTE_ADDR'] . ' mobile:' . $data['mobile'] . ' credit:' . $data['credit'], 2, 'user_add');
					$subject = _('New account registration');
					$body = $core_config['main']['web_title'] . "\n\n";
					$body .= _('Username') . ": " . $data['username'] . "\n";
					$body .= _('Password') . ": " . $new_password . "\n";
					$body .= _('Mobile') . ": " . $data['mobile'] . "\n";
					$body .= _('Credit') . ": " . user_getfieldbyuid($new_uid, 'credit') . "\n\n--\n";
					$body .= $core_config['main']['email_footer'] . "\n\n";
					$ret['error_string'] = _('Account has been added and password has been emailed') . " (" . _('username') . ": " . $data['username'] . ")";
					$mail_data = array(
						'mail_from_name' => $core_config['main']['web_title'],
						'mail_from' => $core_config['main']['email_service'],
						'mail_to' => $data['email'],
						'mail_subject' => $subject,
						'mail_body' => $body
					);
					if (!sendmail($mail_data)) {
						$ret['error_string'] = _('Account has been added but failed to send email') . " (" . _('username') . ": " . $data['username'] . ")";
					}
				}
			} else {
				$ret['error_string'] = _('You must fill all required fields');
			}
		} else {
			$ret['error_string'] = $v['error_string'];
		}
	} else {
		$ret['error_string'] = _('Account registration is not available');
	}
	return $ret;
}

function user_edit($uid, $data = array()) {
	$up = array();
	$ret = array();

	$ret['status'] = FALSE;

	$user_edited = user_getdatabyuid($uid);
	if ($user_edited['status'] != 4) {
		unset($data['parent_uid']);
	}
	$data['username'] = $user_edited['username'];

	$fields = array(
		'username',
		'parent_uid',
		'name',
		'email',
		'mobile',
		'address',
		'city',
		'state',
		'country',
		'password',
		'zipcode'
	);
	foreach ($fields as $field) {
		if ($c_data = trim($data[$field])) {
			$up[$field] = $c_data;
		}
	}
	$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());

	if ($up['name']) {
		$v = user_edit_validate($up);
		if ($v['status']) {
			$continue = true;

			if ($up['password']) {
				$up['password'] = md5($up['password']);
			} else {
				unset($up['password']);
			}

			if ($continue) {
				if (dba_update(_DB_PREF_ . '_tblUser', $up, array(
					'flag_deleted' => 0,
					'uid' => $uid
				))) {
					$ret['status'] = TRUE;
					if ($up['password']) {
						$ret['error_string'] = _('Preferences have been saved and password updated');
					} else if ($up['token']) {
						$ret['error_string'] = _('Preferences have been saved and webservices token updated');
					} else {
						$ret['error_string'] = _('Preferences have been saved');
					}
				} else {
					$ret['error_string'] = _('Fail to save preferences');
				}
			}
		} else {
			$ret['error_string'] = $v['error_string'];
		}
	} else {
		$ret['error_string'] = _('You must fill all mandatory fields');
	}

	return $ret;
}

/**
 * Delete existing user
 *
 * @param integer $uid
 *        User ID
 * @return array $ret('error_string', 'status')
 */
function user_remove($uid, $forced = FALSE) {
	global $user_config;
	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = FALSE;
	if ($forced || auth_isadmin() || ($user_config['status'] == 3)) {
		if ($username = user_uid2username($uid)) {
			if (!($uid == 1)) {
				if ($uid == $user_config['uid']) {
					$ret['error_string'] = _('Currently logged in user is immune to deletion');
				} else {

					$subusers = user_getsubuserbyuid($uid);
					if (count($subusers) > 0) {
						$ret['error_string'] = _('Unable to delete this user until all subusers under this user have been removed');
						return $ret;
					}

					if ($user_config['status'] == 3) {
						$parent_uid = user_getparentbyuid($uid);
						if ($parent_uid != $user_config['uid']) {
							$ret['error_string'] = _('Unable to delete other users');
							return $ret;
						}
					}

					if (dba_update(_DB_PREF_ . '_tblUser', array(
						'c_timestamp' => mktime(),
						'flag_deleted' => 1
					), array(
						'flag_deleted' => 0,
						'uid' => $uid
					))) {
						user_banned_remove($uid);
						_log('user removed u:' . $username . ' uid:' . $uid, 2, 'user_remove');
						$ret['error_string'] = _('Account has been removed') . " (" . _('username') . ": " . $username . ")";
						$ret['status'] = TRUE;
					}
				}
			} else {
				$ret['error_string'] = _('User is immune to deletion') . " (" . _('username') . ": " . $username . ")";
			}
		} else {
			$ret['error_string'] = _('User does not exist');
		}
	} else {
		$ret['error_string'] = _('User deletion unavailable');
	}
	return $ret;
}

function user_edit_conf($uid, $data = array()) {
	global $user_config;

	$ret['status'] = FALSE;
	$ret['error_string'] = _('No changes made');

	$fields = array(
		'footer',
		'datetime_timezone',
		'language_module',
		'fwd_to_inbox',
		'fwd_to_email',
		'fwd_to_mobile',
		'local_length',
		'replace_zero',
		'new_token',
		'enable_webservices',
		'webservices_ip',
		'sender',
		'acl_id'
	);

	$up = array();
	foreach ($fields as $field) {
		$up[$field] = trim($data[$field]);
	}

	$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
	if ($uid) {
		if ($up['new_token']) {
			$up['token'] = md5(mktime() . $uid . _PID_);
		}
		unset($up['new_token']);

		// if sender ID is sent then validate it
		if ($c_sender = core_sanitize_sender($up['sender'])) {
			$check_sender = (sender_id_check($uid, $c_sender) ? TRUE : FALSE);
		} else {
			$check_sender = TRUE;
		}

		if ($check_sender) {
			$up['sender'] = $c_sender;

			$c_footer = core_sanitize_footer($up['footer']);
			$up['footer'] = (strlen($c_footer) > 30 ? substr($c_footer, 0, 30) : $c_footer);

			// acl exception for admins
			$c_status = (int) user_getfieldbyuid($uid, 'status');
			if ($c_status == 2) {
				$up['acl_id'] = 0;
			}

			// self edit can't save acl
			if ($uid == $user_config['uid']) {
				unset($up['acl_id']);
			}

			if (dba_update(_DB_PREF_ . '_tblUser', $up, array(
				'flag_deleted' => 0,
				'uid' => $uid
			))) {
				if ($up['token']) {
					$ret['error_string'] = _('User configuration has been saved and webservices token updated');
				} else {
					$ret['error_string'] = _('User configuration has been saved');
				}
				$ret['status'] = TRUE;
			} else {
				$ret['error_string'] = _('Fail to save configuration');
			}
		} else {
			$ret['error_string'] = _('Invalid sender ID');
		}
	} else {
		$ret['error_string'] = _('Unknown error');
	}

	return $ret;
}

/**
 * Save user's login session information
 *
 * @param integer $uid
 *        User ID
 */
function user_session_set($uid = '') {
	global $core_config, $user_config;
	if (!$core_config['daemon_process']) {
		$uid = ($uid ? $uid : $user_config['uid']);

		// fixme anton - do not make this based on IP, not working properly when clients assigned ranged dynamic IPs
		// $hash = md5($uid.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		$hash = md5($uid . $_SERVER['HTTP_USER_AGENT']);

		$json = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'last_update' => core_get_datetime(),
			'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'sid' => $_SESSION['sid'],
			'uid' => $uid
		);
		$item[$hash] = json_encode($json);
		registry_update(1, 'auth', 'login_session', $item);
	}
}

/**
 * Get user's login session information
 *
 * @param integer $uid
 *        User ID
 * @param string $sid
 *        Session ID
 * @return array login sessions
 */
function user_session_get($uid = '', $sid = '') {
	global $user_config;
	$ret = array();
	$h = registry_search(1, 'auth', 'login_session');
	$hashes = $h['auth']['login_session'];
	foreach ($hashes as $key => $val) {
		$d = core_object_to_array(json_decode($val));
		if ($d['ip'] && $d['last_update'] && $d['http_user_agent'] && $d['sid'] && $d['uid']) {
			if ($uid || $sid) {
				if ($uid && ($uid == $d['uid'])) {
					$ret[$key] = $d;
					return $ret;
				}
				if ($sid && ($sid == $d['sid'])) {
					$ret[$key] = $d;
					return $ret;
				}
			} else {
				$c_ret[$key] = $d;
			}
		}
	}
	$ret = $c_ret;
	return $ret;
}

/**
 * Remove user's login session information
 *
 * @param integer $uid
 *        User ID
 * @param string $sid
 *        Session ID
 * @return boolean
 */
function user_session_remove($uid = '', $sid = '', $hash = '') {
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
		$hash = user_session_get($uid);
		if (registry_remove(1, 'auth', 'login_session', key($hash))) {
			$ret = TRUE;
		}
	}
	return $ret;
}

/**
 * Add account to banned account list
 *
 * @param integer $uid
 *        User ID
 * @return boolean TRUE if user successfully added to banned user list
 */
function user_banned_add($uid) {
	global $user_config;

	// account admin and currently logged in user/admin cannot be ban
	if ($uid && (($uid == 1) || ($uid == $user_config['uid']))) {
		_log('unable to ban uid:' . $uid, 2, 'user_banned_add');
		return FALSE;
	}

	$bantime = core_get_datetime();
	if (user_session_get($uid)) {
		if (!user_session_remove($uid)) {
			return FALSE;
		}
	}
	$item = array(
		$uid => $bantime
	);
	if (registry_update(1, 'auth', 'banned_users', $item)) {
		_log('banned uid:' . $uid . ' bantime:' . $bantime, 2, 'user_banned_add');
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Remove account from banned account list
 *
 * @param integer $uid
 *        User ID
 * @return boolean TRUE if user successfully removed from banned user list
 */
function user_banned_remove($uid) {
	if (registry_remove(1, 'auth', 'banned_users', $uid)) {
		_log('unbanned uid:' . $uid, 2, 'user_banned_remove');
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Get user ban status
 *
 * @param integer $uid
 *        User ID
 * @return mixed Ban date/time or FALSE for non-banned user
 */
function user_banned_get($uid) {
	$list = registry_search(1, 'auth', 'banned_users', $uid);
	if ($list['auth']['banned_users'][$uid]) {
		return $list['auth']['banned_users'][$uid];
	} else {

		// check if this user has parent then check the parent ban status
		if ($parent_uid = user_getparentbyuid($uid)) {
			if ($bantime = user_banned_get($parent_uid)) {
				return $bantime;
			} else {
				return FALSE;
			}
		}
	}
}

/**
 * List all banned users
 *
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
				'bantime' => $bantime
			);
		}
	}
	return $ret;
}

/**
 * Set user data by uid
 *
 * @param integer $uid
 *        User ID
 * @param array $data
 *        User data
 * @return boolean TRUE when user data updated
 */
function user_setdatabyuid($uid, $data) {
	if ((int) $uid && is_array($data)) {
		$conditions = array(
			'flag_deleted' => 0,
			'uid' => $uid
		);
		if (dba_update(_DB_PREF_ . '_tblUser', $data, $conditions)) {
			return TRUE;
		}
	}

	return FALSE;
}

/**
 * Set parent for a subuser by uid
 *
 * @param integer $uid
 *        User ID
 * @param integer $parent_uid
 *        Parent account ID
 * @return boolean TRUE when parent sets
 */
function user_setparentbyuid($uid, $parent_uid) {
	$uid = (int) $uid;
	$parent_uid = (int) $parent_uid;
	if ($uid && $parent_uid) {
		$parent_status = user_getfieldbyuid($parent_uid, 'status');
		if ($parent_status == 3) {
			if (user_setdatabyuid($uid, array(
				'parent_uid' => $parent_uid,
				'status' => 4
			))) {
				return TRUE;
			}
		}
	}

	return FALSE;
}

/**
 * Get parent of a subuser by uid
 *
 * @param integer $uid
 *        User ID
 * @return mixed Parent account ID or FALSE on error
 */
function user_getparentbyuid($uid) {
	$uid = (int) $uid;
	if ($uid) {
		$conditions = array(
			'flag_deleted' => 0,
			'uid' => $uid,
			'status' => 4
		);
		$list = dba_search(_DB_PREF_ . '_tblUser', 'parent_uid', $conditions);
		$parent_uid = (int) $list[0]['parent_uid'];
		$parent_status = user_getfieldbyuid($parent_uid, 'status');
		if (($parent_status == 2) || ($parent_status == 3)) {
			return $parent_uid;
		}
	}

	return FALSE;
}

/**
 * Get list of subusers under a user by uid
 *
 * @param integer $uid
 *        User ID
 * @return array Array of subusers
 */
function user_getsubuserbyuid($uid) {
	$uid = (int) $uid;
	if ($uid) {
		$parent_status = user_getfieldbyuid($uid, 'status');
		if (($parent_status == 2) || ($parent_status == 3)) {
			$conditions = array(
				'flag_deleted' => 0,
				'parent_uid' => $uid,
				'status' => 4
			);
			return dba_search(_DB_PREF_ . '_tblUser', '*', $conditions);
		}
	}

	return array();
}

/**
 * Search user records
 *
 * @param mixed $keywords
 *        Array or string of keywords
 * @param mixed $fields
 *        Array or string of record fields
 * @param mixed $extras
 *        Array or string of record fields
 * @return array Array of users
 */
function user_search($keywords = '', $fields = '', $extras = '', $exact = FALSE) {
	$ret = array();

	if (!is_array($keywords)) {
		$keywords = explode(',', $keywords);
	}

	if (!is_array($fields)) {
		$fields = explode(',', $fields);
	}

	$search = '';
	foreach ($fields as $field) {
		foreach ($keywords as $keyword) {

			if ($exact) {
				$search .= $field . '=\'' . $keyword . '\' OR ';
			} else {
				$search .= $field . ' LIKE \'%' . $keyword . '%\' OR ';
			}
		}
	}
	if ($search) {
		$search = substr($search, 0, -4);
	}

	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$extra_sql .= ' ' . $key . ' ' . $val;
		}
		$extra_sql = trim($extra_sql);
	} else {
		$extra_sql = trim($extras);
	}

	if ($search || $extra_sql) {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND (" . $search . " " . $extra_sql . ")";
	} else {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0'";
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}

	return $ret;
}
