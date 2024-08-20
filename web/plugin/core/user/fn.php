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
 * Get all user data by status
 * 
 * @param int $status user status. 2 = admin, 3 = user, 4 = subuser.
 * @return array
 */
function user_getallwithstatus($status)
{
	$ret = [];

	if ($status >= 2 && $status <= 4) {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND status=?";
		$db_result = dba_query($db_query, [$status]);
		while ($db_row = dba_fetch_array($db_result)) {
			$ret[] = $db_row;
		}
	}

	return $ret;
}

/**
 * Get user data by uid
 * 
 * @param int $uid user ID
 * @return array
 */
function user_getdatabyuid($uid)
{
	global $core_config;

	$ret = [];

	if ($uid) {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND uid=?";
		$db_result = dba_query($db_query, [$uid]);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row;
			$ret['opt']['sms_footer_length'] = strlen($ret['footer']) > 0 ? strlen($ret['footer']) + 1 : 0;
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

/**
 * Get user data by username
 * 
 * @param string $username username
 * @return array
 */
function user_getdatabyusername($username)
{
	$ret = [];

	if ($username = trim($username) && $uid = user_username2uid($username)) {
		$ret = user_getdatabyuid($uid);
	}

	return $ret;
}

/**
 * Get specific field of user data by uid
 * 
 * @param int $uid user ID
 * @param string $field specific field
 * @return string
 */
function user_getfieldbyuid($uid, $field)
{
	$ret = '';

	// sanitize non-alphanumerics or underscores
	$field = trim(preg_replace('/[^\p{L}\p{N}_]+/u', '', $field));

	if ($uid && $field) {
		$db_query = "SELECT $field FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND uid=?";
		$db_result = dba_query($db_query, [$uid]);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row[$field];
		}
	}

	return $ret;
}

/**
 * Get specific field of user data by username
 * 
 * @param string $username username
 * @param string $field specific field
 * @return string
 */
function user_getfieldbyusername($username, $field)
{
	$ret = '';

	if ($username = trim($username) && $uid = user_username2uid($username)) {
		$ret = user_getfieldbyuid($uid, $field);
	}

	return $ret;
}

/**
 * Get username by uid
 * 
 * @param int $uid user ID
 * @return string|null
 */
function user_uid2username($uid)
{
	$ret = null;

	if ($uid) {
		$db_query = "SELECT username FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND uid=?";
		$db_result = dba_query($db_query, [$uid]);
		if ($db_row = dba_fetch_array($db_result)) {
			if (isset($db_row['username']) && $username = trim($db_row['username'])) {
				$ret = $username;
			}
		}
	}

	return $ret;
}

/**
 * Get uid by username
 * 
 * @param string $username username
 * @return int|null
 */
function user_username2uid($username)
{
	$ret = null;

	if ($username) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND username=?";
		$db_result = dba_query($db_query, [$username]);
		if ($db_row = dba_fetch_array($db_result)) {
			if (isset($db_row['uid']) && $uid = (int) $db_row['uid']) {
				$ret = $uid;
			}
		}
	}

	return $ret;
}

/**
 * Get uid by mobile phone number
 * 
 * @param string $mobile
 * @return int|null
 */
function user_mobile2uid($mobile)
{
	$ret = null;

	if ($mobile = trim($mobile)) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND mobile LIKE ?";
		$db_result = dba_query($db_query, ['%' . core_mobile_matcher_format($mobile)]);
		if ($db_row = dba_fetch_array($db_result)) {
			if (isset($db_row['uid']) && $uid = (int) $db_row['uid']) {
				$ret = $uid;
			}
		}
	}

	return $ret;
}

/**
 * Get username by mobile phone number
 * 
 * @param string $mobile
 * @return string|null
 */
function user_mobile2username($mobile)
{
	$ret = null;

	if ($mobile = trim($mobile) && $uid = user_mobile2uid($mobile)) {
		$ret = user_uid2username($uid);
	}

	return $ret;
}

/**
 * Get uid by email
 *
 * @param string $email email
 * @return int|null
 */
function user_email2uid($email)
{
	$ret = null;

	if ($email = trim($email)) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND email=?";
		$db_result = dba_query($db_query, [$email]);
		if ($db_row = dba_fetch_array($db_result)) {
			if (isset($db_row['uid']) && $uid = (int) $db_row['uid']) {
				$ret = $uid;
			}
		}
	}

	return $ret;
}

/**
 * Get username by email
 *
 * @param string $email email
 * @return string|null
 */
function user_email2username($email)
{
	$ret = null;

	if ($email = trim($email) && $uid = user_email2uid($email)) {
		$ret = user_uid2username($uid);
	}

	return $ret;
}

/**
 * Validate user data before user add or edit
 *
 * @param array $data user data
 * @param bool $flag_edit set true for user edit
 * @return array
 *     [
 *         'error_string',	// error message
 *         'status',		// true when user data validated
 *     ]
 */
function user_add_validate($data = [], $flag_edit = false)
{
	global $core_config;

	$ret['error_string'] = null;
	$ret['status'] = null;

	if (is_array($data)) {
		foreach ( $data as $key => $val ) {
			$data[$key] = trim($val);
		}

		// check mandatory fields: name, username and email

		// check if supplied data contains name
		if (!(isset($data['name']) && trim($data['name']))) {
			$ret['error_string'] = _('Account name is mandatory');
			$ret['status'] = false;

			return $ret;
		}

		// check if supplied data contains username
		if (!(isset($data['username']) && trim($data['username']))) {
			$ret['error_string'] = _('Account username is mandatory');
			$ret['status'] = false;

			return $ret;
		}

		// check if supplied data contains email
		if (!(isset($data['email']) && trim($data['email']))) {
			$ret['error_string'] = _('Account email is mandatory');
			$ret['status'] = false;

			return $ret;
		}

		// name must be at least 1 character
		if (strlen($data['name']) < 1) {
			$ret['error_string'] = _('Account name must be at least 1 character');
			$ret['status'] = false;

			return $ret;
		}

		// username should be at least 3 characters and maximum $username_length
		$username_length = $core_config['main']['username_length'] ? $core_config['main']['username_length'] : 30;
		if (strlen($data['username']) < 3 || strlen($data['username']) > $username_length) {
			$ret['error_string'] = sprintf(_('Username must be at least 3 characters and maximum %d characters'), $username_length) . " (" . $data['username'] . ")";
			$ret['status'] = false;

			return $ret;
		}

		// username only can contain alphanumeric, dot and dash
		if (!preg_match('/([A-Za-z0-9\.\-])/', $data['username'])) {
			$ret['error_string'] = _('Valid characters for username are alphabets, numbers, dot or dash') . " (" . $data['username'] . ")";
			$ret['status'] = false;

			return $ret;
		}

		// password should be at least 4 characters if supplied
		if (isset($data['password']) && $data['password'] && strlen($data['password']) < 4) {
			$ret['error_string'] = _('Password should be at least 4 characters');
			$ret['status'] = false;

			return $ret;
		}

		// email must be in valid format
		if (!preg_match('/^(.+)@(.+)\.(.+)$/', $data['email']) && !$core_config['main']['enhance_privacy_subuser']) {
			$ret['error_string'] = _('Your email format is invalid') . " (" . $data['email'] . ")";
			$ret['status'] = false;

			return $ret;
		}

		// mobile must be in valid format if supplied
		if (isset($data['mobile']) && $data['mobile'] && !preg_match('/([0-9\+\-\s])/', $data['mobile'])) {
			$ret['error_string'] = _('Your mobile format is invalid') . " (" . $data['mobile'] . ")";
			$ret['status'] = false;

			return $ret;
		}

		// check if username is already exists
		if (user_username2uid($data['username'])) {

			// ignore rule during user edit
			if (!$flag_edit) {
				$ret['error_string'] = _('Account already exists') . " (" . _('username') . ": " . $data['username'] . ")";
				$ret['status'] = false;

				return $ret;
			}
		}

		// check if email is already exists
		if ($username_existing = user_email2username($data['email'])) {

			// ignore rule during user edit and currently editing existing user
			if (!$flag_edit || $username_existing != $data['username']) {
				$ret['error_string'] = _('Account with this email already exists') . " (" . _('email') . ": " . $data['email'] . ")";
				$ret['status'] = false;

				return $ret;
			}
		}

		// check if mobile is exists if supplied
		if (isset($data['mobile']) && $data['mobile'] && $username_existing = user_mobile2username($data['mobile'])) {

			// ignore rule during user edit and currently editing existing user
			if (!$flag_edit || $username_existing != $data['username']) {
				$ret['error_string'] = _('Account with this mobile already exists') . " (" . _('mobile') . ": " . $data['mobile'] . ")";
				$ret['status'] = false;

				return $ret;
			}
		}
	}

	// all good
	$ret['error_string'] = '';
	$ret['status'] = true;

	return $ret;
}

/**
 * Validate user data before user edit
 *
 * @param array $data user data
 * @return array
 *     [
 *         'error_string',	// error message
 *         'status',		// true when user data validated
 *     ]
 */
function user_edit_validate($data = [])
{
	return user_add_validate($data, true);
}

/**
 * Add new user
 *
 * @param array $data user data
 * @param bool $forced forced addition
 * @param bool $send_email send email after successful user addition
 * @return array 
 *     [
 *         'error_string',	// error message
 *         'status',		// process status
 *         'uid',			// user ID
 *         'data'			// user data
 *     ]
 */
function user_add($data = [], $forced = false, $send_email = true)
{
	global $core_config, $user_config;

	// default return values
	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = false;
	$ret['uid'] = 0;
	$ret['data'] = [];

	$data = trim($data['username']) ? $data : $_REQUEST;
	if ($forced || auth_isadmin() || $user_config['status'] == 3 || (!auth_isvalid() && $core_config['main']['enable_register'])) {
		foreach ( $data as $key => $val ) {
			$data[$key] = trim($val);
		}

		// set valid status
		$data['status'] = (int) $data['status'];
		if (!($data['status'] == 2 || $data['status'] == 3)) {
			$data['status'] = 4;
		}

		// ACL exception for admins
		$data['acl_id'] = (int) $data['acl_id'] ? (int) $data['acl_id'] : (int) $core_config['main']['default_acl'];
		if ($data['status'] == 2) {
			$data['acl_id'] = 0;
		}

		// default parent_id
		$data['parent_uid'] = (int) $data['parent_uid'] ? (int) $data['parent_uid'] : (int) $core_config['main']['default_parent'];
		if ($parent_status = user_getfieldbyuid($data['parent_uid'], 'status')) {
			// logic for parent_uid, parent uid by default is 0
			if ($data['status'] == 4) {
				if (!($parent_status == 2 || $parent_status == 3)) {
					$data['parent_uid'] = (int) $core_config['main']['default_parent'];
				}
			} else {
				$data['parent_uid'] = (int) $core_config['main']['default_parent'];
			}
		} else {
			$data['parent_uid'] = (int) $core_config['main']['default_parent'];
		}

		// salt is unused, empty it
		$data['salt'] = '';

		$data['username'] = core_sanitize_username($data['username']);

		// password is unencrypted for validation, it will be encrypted when saving to db
		$data['password'] = trim($data['password']) ? trim($data['password']) : core_get_random_string();

		// do not generate token for webservices now
		$data['token'] = '';

		// default credit
		$data['credit'] = 0;
		$data['credit'] = (float) $data['credit'];
		$supplied_credit = $data['credit'];

		// sender set to empty by default
		// $data['sender'] = ($data['sender'] ? core_sanitize_sender($data['sender']) : '');
		$data['sender'] = '';

		$dt = core_get_datetime();
		$data['register_datetime'] = $dt;
		$data['lastupdate_datetime'] = $dt;

		// fixme anton - these should be configurable on main config
		$data['footer'] = '@' . $data['username'];

		// default disable webservices
		$data['enable_webservices'] = 0;
		$data['webservices_ip'] = '127.0.0.1';

		$v = user_add_validate($data);
		if ($v['status']) {

			// unencrypted password, this variable will be used later for emailing new user
			$register_password = $data['password'];

			// encrypt password with salt before saving to db
			$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, [
				'cost' => 12,
			]);

			_log('attempt to register status:' . $data['status'] . ' u:' . $data['username'] . ' email:' . $data['email'], 3, 'user_add');
			if ($data['username'] && $data['email'] && $data['name']) {
				if ($new_uid = dba_add(_DB_PREF_ . '_tblUser', $data)) {
					$ret['status'] = true;
					$ret['uid'] = $new_uid;

					// set credit upon registration
					$default_credit = ($supplied_credit ? $supplied_credit : (float) $core_config['main']['default_credit']);
					rate_addusercredit($ret['uid'], $default_credit);
				} else {
					$ret['error_string'] = _('Fail to register an account');
				}

				if ($ret['status']) {
					$data['credit'] = rate_getusercredit($data['username']);
					$data['register_password'] = $register_password;

					_log('registered status:' . $data['status'] . ' u:' . $data['username'] . ' uid:' . $ret['uid'] . ' email:' . $data['email'] . ' ip:' . _REMOTE_ADDR_ . ' mobile:' . $data['mobile'] . ' credit:' . $data['credit'], 2, 'user_add');

					// save $data on returns
					$ret['data'] = $data;

					// default is true, always send email from this function
					if ($send_email) {

						// injected variables must be global, need to work on this later
						global $reg_data;
						$reg_data = $ret['data'];

						// send email
						$tpl = [
							'name' => 'user_add_email',
							'vars' => [
								'Name' => _('Name'),
								'Username' => _('Username'),
								'Password' => _('Password'),
								'Mobile' => _('Mobile'),
								'Credit' => _('Credit'),
								'Email' => _('Email')
							],
							'injects' => [
								'core_config',
								'reg_data'
							],
						];
						$email_body = tpl_apply($tpl);
						$email_subject = _('New account registration');

						$mail_data = [
							'mail_from_name' => $core_config['main']['web_title'],
							'mail_from' => $core_config['main']['email_service'],
							'mail_to' => $data['email'],
							'mail_subject' => $email_subject,
							'mail_body' => $email_body
						];
						if (sendmail($mail_data)) {
							$ret['error_string'] = _('Account has been added and password has been emailed') . " (" . _('username') . ": " . $data['username'] . ")";
						} else {
							$ret['error_string'] = _('Account has been added but failed to send email') . " (" . _('username') . ": " . $data['username'] . ")";
						}
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

/**
 * Edit user
 * 
 * @param int $uid user ID
 * @param array $data modified user data
 * @return array
 *     [
 *         'error_string',	// error message
 *         'status',		// true when user has been edited
 *     ]
 */
function user_edit($uid, $data = [])
{
	$up = [];
	$ret = [];

	$ret['status'] = false;

	$user_edited = user_getdatabyuid($uid);
	if ($user_edited['status'] != 4) {
		unset($data['parent_uid']);
	}
	$data['username'] = $user_edited['username'];

	$fields = [
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
	];
	foreach ( $fields as $field ) {
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
				// salt is unused, empty it
				$up['salt'] = '';
				$up['password'] = password_hash($up['password'], PASSWORD_BCRYPT, [
					'cost' => 12,
				]);
			} else {
				unset($up['password']);
			}

			if ($continue) {
				if (
					dba_update(
						_DB_PREF_ . '_tblUser',
						$up,
						[
							'flag_deleted' => 0,
							'uid' => $uid
						]
					)
				) {
					$ret['status'] = true;
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
 * Remove user
 * 
 * @param int $uid user ID
 * @param bool $forced force use deletion
 * @return array
 *     [
 *         'error_string',	// error message
 *         'status',		// true when user has been edited
 *     ]
 */
function user_remove($uid, $forced = false)
{
	global $user_config;

	$ret['error_string'] = _('Unknown error has occurred');
	$ret['status'] = false;

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

					if (
						dba_update(
							_DB_PREF_ . '_tblUser',
							[
								'c_timestamp' => time(),
								'password' => '',
								'salt' => '',
								'flag_deleted' => 1
							],
							[
								'flag_deleted' => 0,
								'uid' => $uid
							],
						)
					) {
						user_banned_remove($uid);
						_log('user removed u:' . $username . ' uid:' . $uid, 2, 'user_remove');
						$ret['error_string'] = _('Account has been removed') . " (" . _('username') . ": " . $username . ")";
						$ret['status'] = true;
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

/**
 * Edit user configuration
 * 
 * @param int $uid user ID
 * @param array $data user configuration data
 * @return array
 *     [
 *         'error_string',	// error message
 *         'status',		// true when user configuration has been edited
 *     ]
 */
function user_edit_conf($uid, $data = [])
{
	global $user_config;

	$ret['status'] = false;
	$ret['error_string'] = _('No changes made');

	$fields = [
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
	];

	$up = [];
	foreach ( $fields as $field ) {
		if (isset($field) && isset($data[$field])) {
			$up[$field] = trim($data[$field]);
		}
	}

	$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
	if ($uid) {
		if (isset($up['new_token']) && $up['new_token']) {
			//$up['token'] = md5(core_get_random_string());
			$up['token'] = core_random();
		}
		unset($up['new_token']);

		// if sender ID is sent then validate it
		if ($c_sender = core_sanitize_sender($up['sender'])) {
			$check_sender = sender_id_check($uid, $c_sender) ? true : false;
		} else {
			$check_sender = true;
		}

		if ($check_sender) {
			$up['sender'] = $c_sender;

			$c_footer = core_sanitize_footer($up['footer']);
			$up['footer'] = strlen($c_footer) > 30 ? substr($c_footer, 0, 30) : $c_footer;

			// acl exception for admins
			$c_status = (int) user_getfieldbyuid($uid, 'status');
			if ($c_status == 2) {
				$up['acl_id'] = 0;
			}

			// self edit can't save acl
			if ($uid == $user_config['uid']) {
				unset($up['acl_id']);
			}

			if (
				dba_update(
					_DB_PREF_ . '_tblUser',
					$up,
					[
						'flag_deleted' => 0,
						'uid' => $uid
					]
				)
			) {
				if ($up['token']) {
					$ret['error_string'] = _('User configuration has been saved and webservices token updated');
				} else {
					$ret['error_string'] = _('User configuration has been saved');
				}
				$ret['status'] = true;
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
 * @param int user ID
 */
function user_session_set($uid = 0)
{
	global $core_config, $user_config;

	if (!$core_config['daemon_process']) {
		$uid = $uid ? $uid : $_SESSION['uid'];

		if ($uid && $sid = $_SESSION['sid']) {
			$json = [
				'ip' => _REMOTE_ADDR_,
				'last_update' => $_SESSION['last_update'],
				'login_time' => $_SESSION['login_time'],

				// fixme anton - https://www.exploit-database.net/?id=92909
				'http_user_agent' => core_sanitize_string($_SERVER['HTTP_USER_AGENT']),

				'uid' => $uid,
				'username' => $_SESSION['username'],
			];
			registry_update(1, 'auth', 'login_session', [
				$sid => json_encode($json),
			]);

			//_log("login session saved in registry uid:" . $uid . " hash:" . $sid, 2, "user_session_set");
		}
	}
}

/**
 * Update user's login session information
 *
 * @param int user ID
 * @param array $data user data
 */
function user_session_update($uid = 0, $data = [])
{
	global $core_config, $user_config;

	if (!$core_config['daemon_process']) {
		$uid = $uid ? $uid : $user_config['uid'];

		if ($uid && $sid = $_SESSION['sid']) {
			$d = user_session_get($uid);
			if (isset($d[$sid]) && $d = $d[$sid]) {

				foreach ( $d as $key => $val ) {
					if (isset($d[$key]) && isset($data[$key])) {
						$d[$key] = core_sanitize_string($data[$key]);
					}
				}

				registry_update(1, 'auth', 'login_session', [
					$sid => json_encode($d),
				]);

				// debug
				//_log("login session updated in registry uid:" . $uid . " hash:" . $sid . " data:" . json_encode($data), 2, "user_session_update");
			} else {
				// debug
				//_log("fail to update login session in registry uid:" . $uid . " hash:" . $sid . " data:" . json_encode($data), 2, "user_session_update");
			}
		}
	}
}

/**
 * Get user's login session information
 *
 * @param int $uid user ID
 * @return array login sessions
 */
function user_session_get($uid = 0)
{
	global $user_config;

	$ret = [];

	if ($uid && ($sid = $_SESSION['sid'])) {
		$h = registry_search(1, 'auth', 'login_session', $sid);
		$d = core_object_to_array(json_decode($h['auth']['login_session'][$sid]));
		if ($d['ip'] && $d['last_update'] && $d['http_user_agent'] && $d['uid']) {

			// fixme anton - https://www.exploit-database.net/?id=92909
			$d['http_user_agent'] = core_sanitize_string($d['http_user_agent']);

			return [$sid => $d];
		}
	}
	unset($sid);

	$h = registry_search(1, 'auth', 'login_session');
	$hashes = $h['auth']['login_session'];
	foreach ( $hashes as $sid => $data ) {
		$d = core_object_to_array(json_decode($data));
		if ($d['ip'] && $d['last_update'] && $d['http_user_agent'] && $d['uid']) {

			// fixme anton - https://www.exploit-database.net/?id=92909
			$d['http_user_agent'] = core_sanitize_string($d['http_user_agent']);

			$ret[$sid] = $d;
		}
	}

	return $ret;
}

/**
 * Remove user's login session information
 *
 * @param int $uid user ID
 * @param string $hash registry hash
 * @return bool
 */
function user_session_remove($uid = 0, $hash = '')
{
	global $user_config;

	$ret = false;

	if ($hash) {
		if (registry_remove(1, 'auth', 'login_session', $hash)) {
			$ret = true;
		}
	} else if ($uid = (int) $uid) {
		$d = user_session_get($uid);
		if (registry_remove(1, 'auth', 'login_session', key($d))) {
			$ret = true;
		}
	}

	if ($ret) {
		_log("login session removed from registry uid:" . $uid . " hash:" . $hash, 2, "user_session_remove");
	}

	return $ret;
}

/**
 * Add account to banned account list
 *
 * @param int user ID
 * @return bool true if user successfully added to banned user list
 */
function user_banned_add($uid)
{
	global $user_config;

	// account admin and currently logged in user/admin cannot be ban
	if ($uid == 1 || $uid == $user_config['uid']) {
		_log('unable to ban uid:' . $uid, 2, 'user_banned_add');

		return false;
	}

	$bantime = core_get_datetime();
	if (user_session_get($uid)) {
		if (!user_session_remove($uid)) {

			return false;
		}
	}
	$item = [
		$uid => $bantime
	];
	if (registry_update(1, 'auth', 'banned_users', $item)) {
		_log('banned uid:' . $uid . ' bantime:' . $bantime, 2, 'user_banned_add');

		return true;
	} else {

		return false;
	}
}

/**
 * Remove account from banned account list
 *
 * @param int user ID
 * @return bool true if user successfully removed from banned user list
 */
function user_banned_remove($uid)
{
	if (registry_remove(1, 'auth', 'banned_users', $uid)) {
		_log('unbanned uid:' . $uid, 2, 'user_banned_remove');

		return true;
	} else {

		return false;
	}
}

/**
 * Get user ban status
 *
 * @param int user ID
 * @return mixed ban date/time or false for non-banned user
 */
function user_banned_get($uid)
{
	$list = registry_search(1, 'auth', 'banned_users', $uid);
	if ($list['auth']['banned_users'][$uid]) {

		return $list['auth']['banned_users'][$uid];
	} else {

		// check if this user has parent then check the parent ban status
		if ($parent_uid = user_getparentbyuid($uid)) {
			if ($bantime = user_banned_get($parent_uid)) {

				return $bantime;
			} else {

				return false;
			}
		}
	}
}

/**
 * List all banned users
 *
 * @return array banned users
 */
function user_banned_list()
{
	$ret = [];

	$list = registry_search(1, 'auth', 'banned_users');
	foreach ( $list['auth']['banned_users'] as $key => $val ) {
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
 * @param int user ID
 * @param array $data user data
 * @return bool true when user data updated
 */
function user_setdatabyuid($uid, $data)
{
	if (is_array($data)) {
		$conditions = [
			'flag_deleted' => 0,
			'uid' => $uid
		];
		if (dba_update(_DB_PREF_ . '_tblUser', $data, $conditions)) {

			return true;
		}
	}

	return false;
}

/**
 * Set parent for a subuser by uid
 *
 * @param int user ID
 * @param int parent account ID
 * @return bool true when parent sets
 */
function user_setparentbyuid($uid, $parent_uid)
{
	if ($uid && $parent_uid) {
		$parent_status = user_getfieldbyuid($parent_uid, 'status');
		if ($parent_status == 3) {
			if (
				user_setdatabyuid(
					$uid,
					[
						'parent_uid' => $parent_uid,
						'status' => 4
					]
				)
			) {

				return true;
			}
		}
	}

	return false;
}

/**
 * Get parent of a subuser by uid
 *
 * @param int user ID
 * @return int|null parent account ID or null on error
 */
function user_getparentbyuid($uid)
{
	if ($uid) {
		$conditions = [
			'flag_deleted' => 0,
			'uid' => $uid,
			'status' => 4
		];
		$list = dba_search(_DB_PREF_ . '_tblUser', 'parent_uid', $conditions);
		$parent_uid = (int) $list[0]['parent_uid'];
		$parent_status = user_getfieldbyuid($parent_uid, 'status');
		if (($parent_status == 2) || ($parent_status == 3)) {

			return $parent_uid;
		}
	}

	return null;
}

/**
 * Get list of subusers under a user by uid
 *
 * @param int user ID
 * @return array array of subusers
 */
function user_getsubuserbyuid($uid)
{
	if ($uid) {
		$parent_status = user_getfieldbyuid($uid, 'status');
		if (($parent_status == 2) || ($parent_status == 3)) {
			$conditions = [
				'flag_deleted' => 0,
				'parent_uid' => $uid,
				'status' => 4
			];

			return dba_search(_DB_PREF_ . '_tblUser', '*', $conditions);
		}
	}

	return [];
}

/**
 * Search user records
 *
 * @param string|array $keywords array or string of keywords
 * @param string|array $fields array or string of record fields
 * @param string|array $extras array or string of record fields
 * @param bool $exact search as is
 * @return array array of users
 */
function user_search($keywords = '', $fields = '', $extras = '', $exact = false)
{
	$ret = [];
	$db_argv = [];

	if (!is_array($keywords)) {
		$keywords = explode(',', $keywords);
		$keywords = core_trim($keywords);
	}

	if (!is_array($fields)) {
		$fields = explode(',', $fields);
		$fields = core_trim($fields);
	}

	$search = '';
	foreach ( $fields as $field ) {
		foreach ( $keywords as $keyword ) {

			if ($exact) {
				$search .= $field . '=? OR ';
				$db_argv[] = $keyword;
			} else {
				$search .= $field . ' LIKE ? OR ';
				$db_argv[] = '%' . $keyword . '%';
			}
		}
	}
	if ($search) {
		$search = preg_replace('/\sOR\s$/i', '', $search);
		$search = trim($search);
	}

	if (is_array($extras)) {
		foreach ( $extras as $key => $val ) {
			$extra_sql .= ' ' . $key . ' ' . $val;
		}
		$extra_sql = trim($extra_sql);
	} else {
		$extra_sql = trim($extras);
	}

	if ($search || $extra_sql) {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND (" . $search . " " . $extra_sql . ")";
		$db_result = dba_query($db_query, $db_argv);
	} else {
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0";
		$db_result = dba_query($db_query);
	}
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}

	return $ret;
}