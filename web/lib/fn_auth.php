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
 * Check if ticket is valid, that visitor has access or validated
 * @return boolean TRUE if valid
 */
function auth_isvalid() {
	if ($_SESSION['username'] && $_SESSION['valid']) {
		return true;
	}
	return false;
}

/**
 * Check if visitor has admin access level
 * @return boolean TRUE if valid and visitor has admin access level
 */
function auth_isadmin() {
	if (auth_isvalid()) {
		if ($_SESSION['status']==2) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Force forward to noaccess page
 */
function auth_block() {
	global $core_config;
	$_SESSION['error_string'] = _('You have no access to this page');
	logger_print("WARNING: no access. sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR']." uid:".$core_config['user']['uid']." app:"._APP_." inc:"._INC_." op:"._OP_." route:"._ROUTE_, 2, "auth_block");
	header("Location: index.php?app=page&inc=noaccess");
	exit();
}

/**
 * Process login
 *
 */
function auth_login() {
	global $core_config;
	$username = trim($_REQUEST['username']);
	$password = trim($_REQUEST['password']);
	if ($username && $password) {
		if (auth_validate_login($username,$password)) {
			$db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp='".mktime()."',ticket='1' WHERE username='$username'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['sid'] = session_id();
				$_SESSION['username'] = $username;
				$c_user = user_getdatabyusername($username);
				$_SESSION['uid'] = $c_user['uid'];
				$_SESSION['status'] = $c_user['status'];
				$_SESSION['valid'] = true;
				logger_print("u:".$username." status:".$_SESSION['status']." sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
			} else {
				$_SESSION['error_string'] = _('Unable to update login session');
			}
		} else {
			$_SESSION['error_string'] = _('Invalid username or password');
		}
	}
	header("Location: ".$core_config['http_path']['base']);
	exit();
}

/**
 * Process logout
 *
 */
function auth_logout() {
	global $core_config;
	registry_remove($core_config['user']['uid'], 'auth', 'login_session');
	$db_query = "UPDATE "._DB_PREF_."_tblUser SET ticket='0' WHERE username='".$_SESSION['username']."'";
	$db_result = dba_query($db_query);
	logger_print("u:".$_SESSION['username']." status:".$_SESSION['status']." sid:".$_SESSION['sid']." ip:".$_SERVER['REMOTE_ADDR'], 2, "logout");
	@session_destroy();
	$_SESSION['error_string'] = _('You have been logged out');
	header("Location: ".$core_config['http_path']['base']);
	exit();
}

/**
 * Process forgot password
 *
 */
function auth_forgot() {
	global $core_config;
	$ok = FALSE;
	if ($core_config['main']['cfg_enable_forgot']) {
		$username = trim($_REQUEST['username']);
		$email = trim($_REQUEST['email']);
		$_SESSION['error_string'] = _('Fail to recover password');
		if ($username && $email) {
			$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username' AND email='$email'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				if ($password = $db_row['password']) {
					$tmp_password = core_get_random_string();
					$tmp_password_coded = md5($tmp_password);
					if (registry_update(1, 'auth', 'tmp_password', array($username => $tmp_password_coded))) {
						$subject = _('Password recovery');
						$body = $core_config['main']['cfg_web_title']."\n";
						$body .= $core_config['http_path']['base']."\n\n";
						$body .= _('Username')."\t: ".$username."\n";
						$body .= _('Password')."\t: ".$tmp_password."\n\n";
						$body .= $core_config['main']['cfg_email_footer']."\n\n";
						$data = array(
							'mail_from_name' => $core_config['main']['cfg_web_title'],
							'mail_from' => $core_config['main']['cfg_email_service'],
							'mail_to' => $email,
							'mail_subject' => $subject,
							'mail_body' => $body
						);
						if (sendmail($data)) {
							$_SESSION['error_string'] = _('Password has been emailed')." ("._('Username').": ".$username.")";
							$ok = TRUE;
						} else {
							$_SESSION['error_string'] = _('Fail to send email');
						}
						
					} else {
						$error_string = _('Fail to send email');
					}
					
					logger_print("u:".$username." email:".$email." ip:".$_SERVER['REMOTE_ADDR'], 2, "forgot");
				}
			}
		}
	} else {
		$_SESSION['error_string'] = _('Recover password disabled');
	}
	if ($ok) {
		header("Location: ".$core_config['http_path']['base']);
	} else {
		header("Location: index.php?app=page&inc=forgot");
	}
	exit();
}

/**
 * Process register an account
 *
 */
function auth_register() {
	global $core_config;
	$data['name'] = $_REQUEST['name'];
	$data['username'] = $_REQUEST['username'];
	$data['mobile'] = $_REQUEST['mobile'];
	$data['email'] = $_REQUEST['email'];
	$data['status'] = 3; // force non-admin
	$data['password'] = ''; // force generate random password
	$ret = user_add($data);
	$_SESSION['error_string'] = $ret['error_string'];
	if ($ret['status']) {
		header("Location: ".$core_config['http_path']['base']);
	} else {
		header("Location: index.php?app=page&inc=register");
	}
	exit();
}
