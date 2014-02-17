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

function user_add_validate($item) {
	$ret['status'] = true;
	if (is_array($item)) {
		if ($item['password'] && (strlen($item['password']) < 4)) {
			$ret['error_string'] = _('Password should be at least 4 characters');
			$ret['status'] = false;
		}
		if ($item['username'] && (strlen($item['username']) < 3)) {
			$ret['error_string'] = _('Username should be at least 3 characters')." (".$item['username'].")";
			$ret['status'] = false;
		}
		if ($item['username'] && (! preg_match('/([A-Za-z0-9\.\-])/', $item['username']))) {
			$ret['error_string'] = _('Valid characters for username are alphabets, numbers, dot or dash')." (".$item['username'].")";
			$ret['status'] = false;
		}
		if ($item['email']) {
			if (! preg_match('/^(.+)@(.+)\.(.+)$/', $item['email'])) {
				$ret['error_string'] = _('Your email format is invalid')." (".$item['email'].")";
				$ret['status'] = false;
			}
			$c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('email' => $item['email']));
			if ($c_user[0]['username'] && ($c_user[0]['username'] != $item['username'])) {
				$ret['error_string'] = _('Email is already in use by other username') . " (" . _('email') . ": ".$item['email'].", " . _('username') . ": " . $c_user[0]['username'] . ") ";
				$ret['status'] = false;
			}
		}
		if ($item['mobile']) {
			if (! preg_match('/([0-9\+\- ])/', $item['mobile'])) {
				$ret['error_string'] = _('Your mobile format is invalid')." (".$item['mobile'].")";
				$ret['status'] = false;
			}
			$c_uid = user_mobile2uid($item['mobile']);
			$c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('uid' => $c_uid));
			if ($c_user[0]['username'] && ($c_user[0]['username'] != $item['username'])) {
				$ret['error_string'] = _('Mobile is already in use by other username') . " (" . _('mobile') . ": ".$item['mobile'].", " . _('username') . ": " . $c_user[0]['username'] . ") ";
				$ret['status'] = false;
			}
		}
	}
	return $ret;
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
 * Add new user
 * @param array $data User data
 * @return array $ret('error_string', 'status')
 */
function user_add($data=array()) {
	global $core_config;
	$ret['status'] = FALSE;
	$ret['error_string'] = _('Fail to register an account');
	$data = ( trim($data['username']) ? $data : $_REQUEST );
	if (!auth_isadmin() && $core_config['main']['cfg_enable_register']) {
		$data['status'] = ( trim($data['status']) ? trim($data['status']) : 3 );
		$data['status'] = ( auth_isadmin() ? $data['status'] : 3 );
		$username = core_sanitize_username(trim($data['username']));
		$email = trim($data['email']);
		$name = trim($data['name']);
		$mobile = trim($data['mobile']);
		$password = ( trim($data['password']) ? trim($data['password']) : core_get_random_string(10) );
		$credit = ( trim($data['credit']) ? trim($data['credit']) : $core_config['main']['cfg_default_credit'] );
		$footer = '@'.$username;
		$v = user_add_validate($data);
		if ($v['status']) {
			if ($username && $email && $name && $mobile) {
				$continue = TRUE;

				// check username
				$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE username='$username'";
				$db_result = dba_query($db_query);
				if ($db_row = dba_fetch_array($db_result)) {
					$ret['error_string'] = _('User is already exists')." ("._('username').": ".$username.")";
					$continue = FALSE;
				}

				// check email
				if ($continue) {
					$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE email='$email'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						$ret['error_string'] = _('User with this email is already exists')." ("._('email').": ".$email.")";
						$continue = FALSE;
					}
				}

				// check mobile
				if ($continue) {
					$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE mobile='$mobile'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						$ret['error_string'] = _('User with this mobile is already exists')." ("._('mobile').": ".$mobile.")";
						$continue = FALSE;
					}
				}

				if ($continue) {
					$password_coded = md5($password);
					$dt = core_get_datetime();
					$db_query = "
						INSERT INTO "._DB_PREF_."_tblUser
						(status, username, password, name, mobile, email, footer, credit, register_datetime, lastupdate_datetime)
						VALUES
						('$status','$username','$password_coded','$name','$mobile','$email','$footer','$credit','$dt','$dt')";
					if ($new_uid = @dba_insert_id($db_query)) {
						$ret['status'] = TRUE;
					}
				}
				if ($ret['status']) {
					logger_print("u:".$username." email:".$email." ip:".$_SERVER['REMOTE_ADDR']." mobile:".$mobile." credit:".$credit, 2, "register");
					$subject = _('New account registration');
					$body = $core_config['main']['cfg_web_title']."\n";
					$body .= $core_config['http_path']['base']."\n\n";
					$body .= _('Username')."\t: $username\n";
					$body .= _('Password')."\t: $password\n";
					$body .= _('Mobile')."\t: $mobile\n";
					if ($credit) {
						$body .= _('Credit')."\t: $credit\n";
					}
					$body .= "\n";
					$body .= $core_config['main']['cfg_email_footer']."\n\n";
					$ret['error_string'] = _('User has been added')." ("._('username').": ".$username.")";
					$data = array(
						'mail_from_name' => $core_config['main']['cfg_web_title'],
						'mail_from' => $core_config['main']['cfg_email_service'],
						'mail_to' => $email,
						'mail_subject' => $subject,
						'mail_body' => $body
					);
					if (sendmail($data)) {
						$ret['error_string'] .= _('Password has been sent to your email');
					} else {
						$ret['error_string'] .= _('User has been addedd but failed to send email')." ("._('username').": ".$username.")";
					}
				}
			}
		} else {
			$ret['error_string'] = $v['error_string'];
		}
	} else {
		$ret['error_string'] = _('Public registration disabled');
	}
	return $ret;
}
