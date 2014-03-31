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

$username = trim($_REQUEST['username']);
$email = trim($_REQUEST['email']);

$ok = FALSE;

if (! auth_isvalid()) {
	if ($core_config['main']['enable_forgot']) {
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
						$body = $core_config['main']['web_title']."\n";
						$body .= $core_config['http_path']['base']."\n\n";
						$body .= _('You or someone else have requested a password recovery')."\n\n";
						$body .= _('This temporary password will be removed once you have logged in successfully')."\n\n";
						$body .= _('Username')."\t: ".$username."\n";
						$body .= _('Password')."\t: ".$tmp_password."\n\n";
						$body .= $core_config['main']['email_footer']."\n\n";
						$data = array(
							'mail_from_name' => $core_config['main']['web_title'],
							'mail_from' => $core_config['main']['email_service'],
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
}

if ($ok) {
	header("Location: "._u($core_config['http_path']['base']));
} else {
	header("Location: "._u('index.php?app=page&inc=forgot'));
}

exit();
