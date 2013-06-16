<?php
defined('_SECURE_') or die('Forbidden');

/**
 * Validate username and password
 * @param string $username Username
 * @param string $password Password
 * @return boolean TRUE when validated or boolean FALSE when validation failed
 */
function validatelogin($username,$password) {
	logger_print("login attempt u:".$username." p:".$password." ip:".$_SERVER['REMOTE_ADDR'], 3, "login");
	$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$res_password = trim($db_row['password']);
	$password = md5($password);
	if ($password && $res_password && ($password==$res_password)) {
		logger_print("valid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
		return true;
	}
	logger_print("invalid login u:".$username." ip:".$_SERVER['REMOTE_ADDR'], 2, "login");
	return false;
}

/**
 * Validate token
 * @param string $token Token
 * @return string User ID when validated or boolean FALSE when validation failed
 */
function validatetoken($token) {
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
function valid() {
	if ($_SESSION['username'] && $_SESSION['valid']) {
		return true;
	}
	return false;
}

/**
 * Check if visitor has admin access level
 * @return boolean TRUE if valid and visitor has admin access level
 */
function isadmin() {
	if (valid()) {
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
function forcenoaccess() {
	$_SESSION['error_string'] = _('You have no access to this page');
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
		if (validatelogin($username,$password)) {
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
	if ($core_config['main']['cfg_enable_forgot']) {
		$username = trim($_REQUEST['username']);
		$email = trim($_REQUEST['email']);
		$_SESSION['error_string'] = _('Fail to recover password');
		if ($username && $email) {
			$db_query = "SELECT password FROM "._DB_PREF_."_tblUser WHERE username='$username' AND email='$email'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				if ($password = $db_row['password']) {
					$new_password = core_get_random_string();
					$new_password_coded = md5($new_password);
					$db_query = "UPDATE "._DB_PREF_."_tblUser SET password='$new_password_coded' WHERE username='$username' AND email='$email'";
					if (@dba_affected_rows($db_query)) {
						$subject = "[SMSGW] "._('Password recovery');
						$body = $core_config['main']['cfg_web_title']."\n";
						$body .= $core_config['http_path']['base']."\n\n";
						$body .= _('Username')."\t: ".$username."\n";
						$body .= _('Password')."\t: ".$new_password."\n\n";
						$body .= $core_config['main']['cfg_email_footer']."\n\n";
						if (sendmail($core_config['main']['cfg_email_service'],$email,$subject,$body)) {
							$_SESSION['error_string'] = _('Password has been sent to your email');
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
	header("Location: ".$core_config['http_path']['base']."?errid=".$errid);
	exit();
}

/**
 * Process register an account
 *
 */
function auth_register() {
	global $core_config;
	$ok = false;
	if ($core_config['main']['cfg_enable_register']) {
		$username = trim($_REQUEST['username']);
		$username = core_sanitize_username($username);
		$email = trim($_REQUEST['email']);
		$name = trim($_REQUEST['name']);
		$mobile = trim($_REQUEST['mobile']);
		$_SESSION['error_string'] = _('Fail to register an account');
		if ($username && $email && $name && $mobile) {
			$continue = true;
			
			// check username
			$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE username='$username'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$_SESSION['error_string'] = _('User is already exists')." ("._('username').": ".$username.")";
				$continue = false;
			} 
			
			// check email
			if ($continue) {
				$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE email='$email'";
				$db_result = dba_query($db_query);
				if ($db_row = dba_fetch_array($db_result)) {
					$_SESSION['error_string'] = _('User is already exists')." ("._('email').": ".$email.")";
					$continue = false;
				}
			}
			
			// check mobile
			if ($continue) {
				$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE mobile='$mobile'";
				$db_result = dba_query($db_query);
				if ($db_row = dba_fetch_array($db_result)) {
					$_SESSION['error_string'] = _('User is already exists')." ("._('mobile').": ".$mobile.")";
					$continue = false;
				}
			}
			
			if ($continue) {
				$password = core_get_random_string();
				$password_coded = md5($password);
				$footer = '@'.$username;
				if (preg_match("/^(.+)(.+)\\.(.+)$/",$email,$arr)) {
					// by default the status is 3 (normal user)
					$dt = core_get_datetime();
					$db_query = "
						INSERT INTO "._DB_PREF_."_tblUser (status,username,password,name,mobile,email,footer,credit,register_datetime,lastupdate_datetime)
						VALUES ('3','$username','$password_coded','$name','$mobile','$email','$footer','".$core_config['main']['cfg_default_credit']."','$dt','$dt')
					";
					if ($new_uid = @dba_insert_id($db_query)) {
						$ok = true;
					}
				}
			}
			if ($ok) {
				logger_print("u:".$username." email:".$email." ip:".$_SERVER['REMOTE_ADDR'], 2, "register");
				$subject = "[SMSGW] "._('New account registration');
				$body = $core_config['main']['cfg_web_title']."\n";
				$body .= $core_config['http_path']['base']."\n\n";
				$body .= _('Username')."\t: $username\n";
				$body .= _('Password')."\t: $password\n\n";
				$body .= $core_config['main']['cfg_email_footer']."\n\n";
				$_SESSION['error_string'] = _('User has been added')." ("._('username').": ".$username.")";
				$_SESSION['error_string'] .= "<br />";
				if (sendmail($core_config['main']['cfg_email_service'],$email,$subject,$body)) {
					$_SESSION['error_string'] .= _('Password has been sent to your email');
				} else {
					$_SESSION['error_string'] .= _('Fail to send email');
				}
			}
		}
	} else {
		$_SESSION['error_string'] = _('Public registration disabled');
	}
	header("Location: ".$core_config['http_path']['base']);
	exit();
}

?>
