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
 * Validate username and password
 *
 * @param string $username username
 * @param string $password password
 * @return bool true when validated
 */
function auth_validate_login($username, $password)
{
	// username not found
	if (!trim($username)) {
		_log('invalid username not found u:' . $username . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}

	// sanitized username
	$sanitized_username = core_sanitize_username($username);
	if ($sanitized_username != $username) {
		_log('username contains invalid characters u:' . $username . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}
	$username = $sanitized_username;

	// password is too short
	if (strlen(trim($password)) <= 4) {
		_log('invalid password is too short u:' . $username . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}

	// get uid
	if (!($uid = user_username2uid($username))) {
		_log('cannot find uid from username u:' . $username . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}

	// log attempts	
	_log('login attempt u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 3, 'auth_validate_login');

	// check if user's IP blacklist
	if (blacklist_ifipexists(_REMOTE_ADDR_)) {
		_log('IP blacklisted u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}

	// check if user banned
	if (user_banned_get($uid)) {
		_log('user banned u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		return false;
	}

	// get user's password and salt, but after using password_hash() salt is not inuse actually
	$db_query = "SELECT password,salt FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND username=?";
	$db_result = dba_query($db_query, [$username]);
	$db_row = dba_fetch_array($db_result);
	$res_password = trim($db_row['password']);
	$res_salt = trim($db_row['salt']);

	// verify user's password againts visitor input
	if ($password && $res_password && password_verify($password, $res_password)) {
		_log('valid login u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

		// login successful, visitor is user

		// remove IP on successful login
		blacklist_clearip(_REMOTE_ADDR_);

		return true;
	} else {

		// hmm not verified, check temporary password, user's might issue forgot password
		$ret = registry_search(1, 'auth', 'tmp_password', $username);
		$tmp_password = $ret['auth']['tmp_password'][$username];

		// check temporary password issued by forgot password with visitor input
		if ($password && $tmp_password && password_verify($password, $tmp_password)) {

			// login successful, visitor is user and using forgot password to reset their access
			_log('valid login u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_ . ' using temporary password', 2, 'auth_validate_login');

			// remove temporary password from registry upon successful login
			if (!registry_remove(1, 'auth', 'tmp_password', $username)) {
				_log('WARNING: unable to remove temporary password after successful login', 2, 'auth_validate_login');

				// it used to be ok, but now we mark this as login failure
				return false;
			}

			// remove IP on successful login
			blacklist_clearip(_REMOTE_ADDR_);

			return true;
		} else {

			// fixme anton
			// this part is temporary until all users use the new password hash
			// in this part playSMS will convert md5 password to bcrypt hash if password matched

			// not verified, visitor's is not user forgotten password, maybe an old account using old formattted password
			if ($password && $res_password && ($res_password === md5($password) || $res_password === md5($password . $res_salt))) {

				// password matched with old md5 password, convert it to bcrypt hash
				if ($new_password = password_hash($password, PASSWORD_BCRYPT)) {

					// save password in new format
					$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET password=?,salt='' WHERE flag_deleted='0' AND username=?";
					if (dba_affected_rows($db_query, [$new_password, $username])) {
						_log('WARNING: md5 password converted u:' . $username, 2, 'auth_validate_login');

						// remove IP on successful login
						blacklist_clearip(_REMOTE_ADDR_);

						return true;
					} else {
						_log('WARNING: fail to convert md5 password u:' . $username, 2, 'auth_validate_login');

						// conversion is a must, fix it, so we mark this as login failure
						return false;
					}
				} else {
					_log('WARNING: unable to convert password format u:' . $username, 2, 'auth_validate_login');

					// conversion is a must, fix it, so we mark this as login failure
					return false;
				}
			}
		}
	}

	// check blacklist
	blacklist_checkip(_REMOTE_ADDR_);

	// we are here, visitor is unverified, visitor is not user, login failed, return false
	_log('invalid login u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_login');

	return false;
}

/**
 * Validate email and password
 *
 * @param string $email email
 * @param string $password password
 * @return bool true when validated
 */
function auth_validate_email($email, $password)
{
	if (!($username = user_email2username($email))) {
		_log('user not found email:' . $email . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_email');

		return false;
	}

	_log('login attempt email:' . $email . ' u:' . $username . ' ip:' . _REMOTE_ADDR_, 3, 'auth_validate_email');

	return auth_validate_login($username, $password);
}

/**
 * Validate token
 *
 * @param string $token token
 * @return bool true when validated
 */
function auth_validate_token($token)
{

	// check token format, it must be alphanumeric
	if (!($token && $token == core_sanitize_alphanumeric($token))) {
		_log('invalid format token:' . $token . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_token');

		return false;
	}

	if (_APP_ == 'main' || _APP_ == 'menu') {

		// log attempts if using token from web, not webservices
		_log('invalid usage token:' . $token . ' ip:' . _REMOTE_ADDR_, 3, 'auth_validate_token');

		// it used to be ok, but now token can only be used in webservices
		return false;
	}

	$db_query = "SELECT uid,username,enable_webservices,webservices_ip FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND token=?";
	$db_result = dba_query($db_query, [$token]);
	$db_row = dba_fetch_array($db_result);
	$uid = (int) $db_row['uid'];
	$username = trim($db_row['username']);
	$enable_webservices = (bool) $db_row['enable_webservices'];
	$webservices_ip = trim($db_row['webservices_ip']);

	// log attempts	- commented, too noisy for webservices
	//_log('login attempt token:' . $token . ' username:' . $username . ' ip:' . _REMOTE_ADDR_, 3, 'auth_validate_login');

	// check blacklist
	if (blacklist_ifipexists(_REMOTE_ADDR_)) {
		_log('IP blacklisted u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_token');

		return false;
	}

	if ($uid && $username && $enable_webservices && $webservices_ip) {

		// check if auth token coming from allowed IP or network
		$nets = explode(',', $webservices_ip);
		if (is_array($nets) && $nets) {
			foreach ( $nets as $net ) {
				$net = trim($net);
				if (!$net) {
					_log('invalid network u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_ . ' net:' . $net, 2, 'auth_validate_token');

					return false;
				}

				if (core_net_match($net, _REMOTE_ADDR_)) {

					// IP allowed, but user banned
					if (user_banned_get($uid)) {
						_log('user banned u:' . $username . ' uid:' . $uid . ' ip:' . _REMOTE_ADDR_ . ' net:' . $net, 2, 'auth_validate_token');

						return false;
					}

					// remove IP on successful login
					blacklist_clearip(_REMOTE_ADDR_);

					return true;
				}
			}
		}
	}

	// check blacklist
	blacklist_checkip(_REMOTE_ADDR_);

	_log('invalid login t:' . $token . ' ip:' . _REMOTE_ADDR_, 2, 'auth_validate_token');

	return false;
}

/**
 * Check if visitor has been validated
 *
 * @return bool true if valid
 */
function auth_isvalid()
{
	if (session_id() && $_SESSION['uid'] && ($sid = $_SESSION['sid'])) {
		$uid = $_SESSION['uid'];

		// check if user still using the same browser
		if (!($_SESSION['http_user_agent'] && ($_SESSION['http_user_agent'] == core_sanitize_string($_SERVER['HTTP_USER_AGENT'])))) {
			_log("invalid auth HTTP_USER_AGENT changed session:[" . $_SESSION['http_user_agent'] . "] server:[" . core_sanitize_string($_SERVER['HTTP_USER_AGENT']) . "]", 3, "auth_isvalid");

			//auth_session_destroy();

			return false;
		}

		// check if user still browsing from the same IP address
		if (!($_SESSION['ip'] && ($_SESSION['ip'] == _REMOTE_ADDR_))) {
			_log("invalid auth REMOTE_ADDR changed session:" . $_SESSION['ip'] . " server:" . _REMOTE_ADDR_, 3, "auth_isvalid");

			//auth_session_destroy();

			return false;
		}

		// get registry
		$d = user_session_get($uid);

		// check if user session's HTTP_USER_AGENT the same as recorded HTTP_USER_AGENT in registry
		$c_http_user_agent = isset($d[$sid]['http_user_agent']) ? stripslashes($d[$sid]['http_user_agent']) : '';
		if (!($_SESSION['http_user_agent'] && $c_http_user_agent && ($_SESSION['http_user_agent'] == $c_http_user_agent))) {

			// log it only when HTTP_USER_AGENT still recorded in session
			if ($c_http_user_agent) {
				_log("invalid auth HTTP_USER_AGENT different session:[" . $_SESSION['http_user_agent'] . "] registry:[" . $c_http_user_agent . "]", 3, "auth_isvalid");
			}

			//auth_session_destroy();

			return false;
		}

		// check if user session's IP the same as recorded IP in registry
		if (!($_SESSION['ip'] && ($_SESSION['ip'] == $d[$sid]['ip']))) {
			_log("invalid auth REMOTE_ADDR different session:" . $_SESSION['ip'] . " registry:" . $d[$sid]['ip'], 3, "auth_isvalid");

			//auth_session_destroy();

			return false;
		}

		return true;
	}

	return false;
}

/**
 * Check if visitor has admin access level
 *
 * @return bool true if valid and visitor has admin access level
 */
function auth_isadmin()
{
	if ($_SESSION['status'] == 2) {
		if (auth_isvalid()) {
			return true;
		}
	}

	return false;
}

/**
 * Check if visitor has user access level
 *
 * @return bool true if valid and visitor has user access level
 */
function auth_isuser()
{
	if ($_SESSION['status'] == 3) {
		if (auth_isvalid()) {
			return true;
		}
	}

	return false;
}

/**
 * Check if visitor has subuser access level
 *
 * @return bool true if valid and visitor has subuser access level
 */
function auth_issubuser()
{
	if ($_SESSION['status'] == 4) {
		if (auth_isvalid()) {
			return true;
		}
	}

	return false;
}

/**
 * Check if visitor has certain user status
 *
 * @param int|string $status Account status
 * @return bool true if valid and visitor has certain user status
 */
function auth_isstatus($status)
{
	$status = (int) $status;
	if ($status && $_SESSION['status'] == $status) {
		if (auth_isvalid()) {
			return true;
		}
	}

	return false;
}

/**
 * Check if visitor has certain ACL
 *
 * @param string $acl Access Control List
 * @return bool true if valid and visitor has certain ACL
 */
function auth_isacl($acl)
{
	if (auth_isadmin()) {

		return true;
	} else {
		$user_acl_id = user_getfieldbyuid($_SESSION['uid'], 'acl_id');
		$user_acl_name = acl_getname($user_acl_id);
		if ($acl && $user_acl_name && strtoupper($acl) == strtoupper($user_acl_name)) {

			return true;
		}
	}

	return false;
}

/**
 * Display page for blocked access
 */
function auth_block()
{
	header("Location: " . _u('index.php?app=main&inc=core_auth&route=block&op=block'));
	exit();
}

/**
 * Setup and renew user session
 *
 * @param int|string $uid User ID
 */
function auth_session_setup($uid)
{
	global $core_config;

	// regenerate new session ID
	session_regenerate_id(true);

	if (!($uid = (int) $uid)) {

		return;
	}

	$c_user = user_getdatabyuid($uid);
	if ($c_user['uid'] && $c_user['username'] && $c_user['status']) {

		// set session
		// these variables and values sets only in here, no where else
		// except probably later 'status' can be changed somewhere else, eg. after admin changed user's status
		$_SESSION['sid'] = session_id();
		$_SESSION['uid'] = (int) $c_user['uid'];
		$_SESSION['username'] = $c_user['username'];
		$_SESSION['status'] = (int) $c_user['status'];
		$_SESSION['ip'] = _REMOTE_ADDR_;
		$_SESSION['http_user_agent'] = core_sanitize_string($_SERVER['HTTP_USER_AGENT']);
		$_SESSION['last_update'] = time();
		$_SESSION['login_time'] = time();

		// make sure this is empty if currently not inuse
		if (!is_array($_SESSION['tmp']['login_as'])) {
			$_SESSION['tmp']['login_as'] = [];
		}

		// save session in registry - only if not running from daemon
		if (!$core_config['daemon_process']) {
			user_session_set($c_user['uid']);

			_log("session setup uid:" . $_SESSION['uid'] . " hash:" . $_SESSION['sid'], 2, "auth_session_setup");
		}
	}
}

/**
 * Destroy user session
 */
function auth_session_destroy()
{
	$sid = $_SESSION['sid'];
	$uid = $_SESSION['uid'];

	user_session_remove($uid);

	$_SESSION = [];

	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(
			session_name(),
			'',
			time() - 42000,
			$params['path'],
			$params['domain'],
			$params['secure'],
			$params['httponly']
		);
	}

	session_destroy();

	_log("session destroyed uid:" . $uid . " hash:" . $sid, 2, "auth_session_destroy");
}

function auth_login_as($uid)
{
	// save current login
	array_unshift($_SESSION['tmp']['login_as'], $_SESSION['uid']);

	// setup new session
	auth_session_setup($uid);
}

function auth_login_return()
{
	// get previous login
	$previous_login = $_SESSION['tmp']['login_as'][0];
	array_shift($_SESSION['tmp']['login_as']);

	// return to previous session
	auth_session_setup($previous_login);
}

function auth_login_as_check()
{
	if (isset($_SESSION['tmp']['login_as']) && is_array($_SESSION['tmp']['login_as']) && count($_SESSION['tmp']['login_as']) > 0) {

		return true;
	} else {

		return false;
	}
}