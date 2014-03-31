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

/**
 * Validate username and password
 * @param string $username Username
 * @param string $password Password
 * @return boolean TRUE when validated or boolean FALSE when validation failed
 */
function auth_validate_login($username,$password) {
	logger_print("login attempt u:".$username." p:".md5($password)." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$res_password = trim($db_row['password']);
	$password = md5($password);
	if ($password && $res_password && ($password==$res_password)) {
		logger_print("valid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
		return true;
	} else {
		$ret = registry_search(1, 'auth', 'tmp_password', $username);
		$tmp_password = $ret['auth']['tmp_password'][$username];
		if ($password && $tmp_password && ($password==$tmp_password)) {
			logger_print("valid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'].' using temporary password', 2, "login");
			if (! registry_remove(1, 'auth', 'tmp_password', $username)) {
				logger_print("WARNING: unable to remove temporary password after successful login", 3, "login");
			}
			return true;
		}
	}
	logger_print("invalid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
	return false;
}

/**
 * Validate email and password
 * @param string $email Username
 * @param string $password Password
 * @return boolean TRUE when validated or boolean FALSE when validation failed
 */
function auth_validate_email($email,$password) {
	logger_print("login attempt email:".$email." p:".md5($password)." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE email='$email'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$res_password = trim($db_row['password']);
	$password = md5($password);
	if ($password && $res_password && ($password==$res_password)) {
		logger_print("valid login email:".$email." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
		return true;
	} else {
		$ret = registry_search(1, 'auth', 'tmp_password', $email);
		$tmp_password = $ret['auth']['tmp_password'][$email];
		if ($password && $tmp_password && ($password==$tmp_password)) {
			logger_print("valid login email:".$email." ip:".$_SERVER['REMOTE_ADDR'].' using temporary password', 2, "login");
			if (! registry_remove(1, 'auth', 'tmp_password', $email)) {
				logger_print("WARNING: unable to remove temporary password after successful login", 3, "login");
			}
			return true;
		}
	}
	logger_print("invalid login email:".$email." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
	return false;
}

/**
 * Validate token
 * @param string $token Token
 * @return string User ID when validated or boolean FALSE when validation failed
 */
function auth_validate_token($token) {
	$token = trim($token);
	logger_print("login attempt t:".$token." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	if ($token) {
		$db_query = "SELECT uid,username,enable_webservices,webservices_ip FROM "._DB_PREF_."_tblUser WHERE token='$token'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if (($uid = trim($db_row['uid'])) && ($username = trim($db_row['username'])) && ($db_row['enable_webservices'])) {
			$ip = explode(',', $db_row['webservices_ip']);
			if (is_array($ip)) {
				foreach ($ip as $key => $net) {
					if (core_net_match($net, $_SERVER['REMOTE_ADDR'])) {
						logger_print("valid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
						return $uid;
					}
				}
			}
		}
	}
	logger_print("invalid login t:".$token." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
	return false;
}

/**
 * Check if visitor has been validated
 * @return boolean TRUE if valid
 */
function auth_isvalid() {
	if ($_SESSION['sid'] && $_SESSION['uid'] && $_SESSION['valid']) {
		$s = user_session_get($_SESSION['uid']);
		$items = $s[$_SESSION['sid']];
		if ($items['ip'] && $items['last_update']) {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Check if visitor has admin access level
 * @return boolean TRUE if valid and visitor has admin access level
 */
function auth_isadmin() {
	if ($_SESSION['status'] ==  2) {
		if (auth_isvalid()) {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Display page for blocked access
 */
function auth_block() {
	header("Location: "._u('index.php?app=auth&op=block'));
	exit();
}
